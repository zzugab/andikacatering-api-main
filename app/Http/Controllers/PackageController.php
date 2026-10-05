<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityHelper;
use App\Models\MenuModel;
use App\Models\PackageFoodStallModel;
use App\Models\PackagesDataModel;
use App\Models\PackagesModel;
use App\Models\TestimoniModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    private $packageModel;
    private $packageDataModel;
    private $packageFoodStallModel;
    private $menuModel;
    private $testimonialModel;

    public function __construct()
    {
        $this->packageModel = new PackagesModel();
        $this->packageDataModel = new PackagesDataModel();
        $this->packageFoodStallModel = new PackageFoodStallModel();
        $this->menuModel = new MenuModel();
        $this->testimonialModel = new TestimoniModel();
    }

    public function landingPage(Request $request)
    {
        $filter = [
            'package_limit' => $request->package_limit ?? 3,
            'menu_limit' => $request->menu_limit ?? 3,
            'foodstall_limit' => $request->foodstall_limit ?? 3,
            'testimoni_limit' => $request->testimoni_limit ?? 6,
        ];

        // ponytail: cache landing page selama 5 menit untuk memangkas latency network database eksternal.
        $cacheKey = 'landing_page_data_' . implode('_', $filter);
        $cachedData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($filter) {
            $packageData = $this->packageModel->getAll($filter['package_limit']);
            if ($packageData['status'] && !$packageData['data']->isEmpty()) {
                $packageData['data']->load('packageData.category', 'packageFoodStall.menu');

                foreach ($packageData['data'] as $package) {
                    $categoryList = [];
                    foreach ($package->packageData as $category) {
                        if ($category->category) {
                            $categoryList[] = $category->category->name;
                        }
                    }
                    $package['category'] = $categoryList;

                    $stallList = [];
                    foreach ($package->packageFoodStall as $stall) {
                        if ($stall->menu) {
                            $stallList[] = [
                                'name' => $stall->menu->name,
                                'quantity_percent' => $stall->quantity_percent
                            ];
                        }
                    }
                    $package['stall'] = $stallList;
                }
            }

            $menuData = $this->menuModel->getAll($filter['menu_limit'], ['type' => 'Buffet']);
            $foodStallData = $this->menuModel->getAll($filter['foodstall_limit'], ['type' => 'Foodstall']);
            $testimonialData = $this->testimonialModel->getAll($filter['testimoni_limit']);

            if ($packageData['status'] && $menuData['status'] && $foodStallData['status'] && $testimonialData['status']) {
                return [
                    'package' => $packageData,
                    'menu' => $menuData['data'],
                    'foodstall' => $foodStallData['data'],
                    'testimoni' => $testimonialData['data']
                ];
            }
            return null;
        });

        if ($cachedData) {
            $data = [
                'status' => true,
                'data' => $cachedData
            ];
            return response()->success($data, 200);
        } else {
            return response()->failed('Something error', 400);
        }
    }

    public function index(Request $request)
    {
        $filter = [
            'name' => $request->name ?? null,
            'price_start' => $request->price_start ?? null,
            'price_end' => $request->price_end ?? null,
            'type' => $request->type ?? null,
        ];
        $data = $this->packageModel->getAll(null, $filter);

        if (!$data) return response()->failed($data, 404);
        return response()->success($data, 200);
    }

    public function show($uuid)
    {
        $data = $this->packageModel->getById($uuid);
        if (!$data['status']) {
            return response()->failed('Package not found', 404);
        }
        return response()->success($data, 200);
    }

    public function update($uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $checkExist = $this->packageModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Package not found', 404);
        }

        $update = $this->packageModel->edit($validator->validated(), $uuid);
        ActivityHelper::log('Mengubah Paket');
        if ($update) {
            $data = $this->packageModel->getById($uuid);
            return response()->success($data, 200);
        }
        return response()->failed('Something error', 422);
    }

    public function destroy($uuid)
    {
        $checkExist = $this->packageModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Package not found', 404);
        }

        $data = $this->packageModel->drop($uuid);
        ActivityHelper::log('Menghapus Paket');

        if ($data['status']) {
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    public function indexPackageData($uuidPackage)
    {
        $data = $this->packageDataModel->getAll([$uuidPackage]);
        if (!$data['data']) return response()->failed($data, 404);
        return response()->success($data, 200);
    }

    public function indexPackageDataV2($uuidPackage)
    {
        $data = $this->packageDataModel->getAll([$uuidPackage])['data']->toArray();
        if (!$data) return response()->failed($data, 404);

        foreach ($data as &$item) {
            $category = $item['category'];
            $list_menu = $item['category']['menu'];
            unset($item['category'], $category['menu']);

            $item = array_merge(
                $item,
                [
                    'category' => $category,
                    'list_menu' => $list_menu
                ]
            );
        }
        return response()->success($data, 200);
    }

    public function storePackageData($uuidPackage, Request $request)
    {
        try {
            foreach ($request["category"] as $item) {
                $validator = Validator::make($item, [
                    "category_id" => 'required',
                    "quantity_percent" => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->failed($validator->errors(), 400);
                }

                $data = $this->packageDataModel->store(array_merge($validator->validated(), [
                    'packages_id' => $uuidPackage
                ]));
            }
            ActivityHelper::log('Membuat Paket Data Baru');

            $data = $this->packageDataModel->getAll([$uuidPackage]);
            if (!$data) return response()->failed($data, 404);
            return response()->success($data, 200);
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 400);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $data = $this->packageModel->store($validator->validated());
        ActivityHelper::log('Membuat Paket Baru');

        return response()->success($data, 200);
    }

    public function showPackageData($uuid)
    {
        $data = $this->packageDataModel->getById($uuid);
        if (!$data['data']) {
            return response()->failed('Package data not found', 404);
        }
        return response()->success($data, 200);
    }

    public function updatePackageData($uuidPackage, Request $request)
    {
        try {
            foreach ($request["category"] as $item) {
                $validator = Validator::make($item, [
                    'uuid' => 'nullable',
                    "category_id" => 'required',
                    "quantity_percent" => 'required',
                ]);
                if ($validator->fails()) {
                    return response()->failed($validator->errors(), 400);
                }

                if (isset($item['uuid'])) {
                    $data = $this->packageDataModel->edit(array_merge($validator->validated(), [
                        'packages_id' => $uuidPackage
                    ]));
                } else {
                    $data = $this->packageDataModel->store(array_merge($validator->validated(), [
                        'packages_id' => $uuidPackage
                    ]));
                }
            }
            ActivityHelper::log('Mengubah Paket Data');
            $data = $this->packageDataModel->getAll([$uuidPackage]);
            if (!$data) return response()->failed($data, 404);
            return response()->success($data, 200);
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 400);
        }
    }

    public function destroyPackageData($uuid)
    {
        $checkExist = $this->packageDataModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Package data not found', 404);
        }

        $data = $this->packageDataModel->drop($uuid);
        ActivityHelper::log('Menghapus Paket Data');

        if ($data['status']) {
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    public function indexPackageStall($uuidPackage)
    {
        $data = $this->packageFoodStallModel->getAll($uuidPackage);
        if (!$data['data']) return response()->failed($data, 404);
        return response()->success($data, 200);
    }

    public function storePackageStall($uuidPackage, Request $request)
    {
        try {
            foreach ($request["category"] as $item) {
                $validator = Validator::make($item, [
                    "menu_id" => 'required',
                    "quantity_percent" => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->failed($validator->errors(), 400);
                }

                $data = $this->packageFoodStallModel->store(array_merge($validator->validated(), [
                    'packages_id' => $uuidPackage
                ]));
            }
            ActivityHelper::log('Membuat Paket Stall Baru');

            $data = $this->packageFoodStallModel->getAll($uuidPackage);
            if (!$data) return response()->failed($data, 404);
            return response()->success($data, 200);
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 400);
        }
    }

    public function showPackageStall($uuid)
    {
        $data = $this->packageFoodStallModel->getById($uuid);
        if (!$data['data']) {
            return response()->failed('Package data not found', 404);
        }
        return response()->success($data, 200);
    }

    public function updatePackageStall($uuidPackage, Request $request)
    {
        try {
            foreach ($request["category"] as $item) {
                $validator = Validator::make($item, [
                    'uuid' => 'nullable',
                    "menu_id" => 'required',
                    "quantity_percent" => 'required',
                ]);
                if ($validator->fails()) {
                    return response()->failed($validator->errors(), 400);
                }

                if (isset($item['uuid'])) {
                    $data = $this->packageFoodStallModel->edit(array_merge($validator->validated(), [
                        'packages_id' => $uuidPackage
                    ]));
                } else {
                    $data = $this->packageFoodStallModel->store(array_merge($validator->validated(), [
                        'packages_id' => $uuidPackage
                    ]));
                }
            }
            ActivityHelper::log('Mengubah Paket Stall');
            $data = $this->packageFoodStallModel->getAll($uuidPackage);
            if (!$data) return response()->failed($data, 404);
            return response()->success($data, 200);
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 400);
        }
    }

    public function destroyPackageStall($uuid)
    {
        $checkExist = $this->packageFoodStallModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Package data not found', 404);
        }

        $data = $this->packageFoodStallModel->drop($uuid);
        ActivityHelper::log('Menghapus Paket Stall');

        if ($data['status']) {
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }
}

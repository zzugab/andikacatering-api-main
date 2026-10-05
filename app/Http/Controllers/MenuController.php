<?php

namespace App\Http\Controllers;

use App\Models\MenuModel;
use App\Models\MenuCategoryModel;
use App\Models\RecipesModel;
use App\Models\Images;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ActivityHelper;

class MenuController extends Controller
{
    private $menuModel;
    private $menuCategoryModel;
    private $recipeModel;
    private $image;

    public function __construct()
    {
        $this->menuModel = new MenuModel();
        $this->menuCategoryModel = new MenuCategoryModel();
        $this->recipeModel = new RecipesModel();
        $this->image = new Images();
    }

    public function index(Request $request)
    {
        $filter = [
            'type' => $request->type ?? null,
        ];

        $data = $this->menuModel->getAll(null, $filter);
        if (!$data) return response()->failed($data, 404);

        // nanti dihapus seteelah menambahakan data image di menu
        $result = ['status' => true, 'data' => []];

        foreach ($data['data'] as $item) {
            $item = $item->toArray();
            if (!$item['image_id']) {
                $item['image_id'] = 'd30e0078-01f1-4d66-b32f-a7a6105deeec';
                $imagePath = 'default.jpg';
                $imageUrl = url('/') . Storage::url('uploads/menu/' . $imagePath);
                $item['image'] = [
                    'uuid' => 'd30e0078-01f1-4d66-b32f-a7a6105deeec',
                    'type' => 'menu',
                    'name' => 'default.jpg',
                    'link' => $imageUrl,
                ];
            } else {
                $imagePath = $item['image']['name'];
                $imageUrl = url('/') . Storage::url('uploads/menu/' . $imagePath);
                $item['image']['link'] = $imageUrl;
            }
            $result['data'][] = $item;
        }
        return response()->success($result, 200);
    }

    public function show($uuid)
    {
        $data = $this->menuModel->getById($uuid);
        if (!$data['data']) {
            return response()->failed('Menu not found', 404);
        }

        return response()->success($data, 200);
    }

    public function showOrder($uuidOrder)
    {
        $data = $this->menuModel->getByOrderId($uuidOrder);
        $dataStall = $this->menuModel->getFoodStallByOrderId($uuidOrder);

        if (!$data['status'] || !$dataStall['status']) {
            return response()->failed('Order not found', 404);
        }
        $menus = $data['data']->toArray();
        foreach ($menus as &$menu) {
            $percent = null;
            $menu['package'] = [];
            foreach ($menu['category']['package_data'] as $packageData) {
                if (isset($packageData['package']['uuid']) && $packageData['package']['uuid'] == $uuidOrder) {
                    $menu['package'] = $packageData['package'];
                    if ($percent === null) {
                        $percent = $packageData['quantity_percent'];
                    }
                }
            }
            $menu['category']['quantity_percent'] = $percent;
            unset($menu['category']['package_data']);
        }

        $stalls = $dataStall['data']->toArray();
        foreach ($stalls as &$stall) {
            $percent = null;
            $stall['package'] = [];
            foreach ($stall['food_stall'] as $package) {
                if ($package['package']['uuid'] == $uuidOrder) {
                    $stall['package'] = $package['package'];
                    if ($percent === null) {
                        $percent = $package['quantity_percent'];
                    }
                }
            }
            $stall['category']['name'] = $stall['type'];
            $stall['category']['quantity_percent'] = $percent;
            unset($stall['food_stall']);
        }

        $result = [
            'status' => true,
            'data' => [
                'package' => $menus,
                'food_stall' => $stalls
            ]
        ];

        return response()->success($result, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required',
            'category_id' => 'required',
            'type' => 'required',
            'description' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:30720',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $item = $validator->validated();
        if (isset($item['image'])) {
            $imageData = $this->image->store($item['image'], 'menu')['data'];
            unset($item['image']);
            $item['image_id'] = $imageData['uuid'];
        } else {
            $item['image_id'] = 'd30e0078-01f1-4d66-b32f-a7a6105deeec';
        }
        $result = $this->menuModel->store($item);

        if ($result['status']) {
            $result = $result['data']->toArray();
            $imagePath = $result['image']['name'];
            $imageUrl = url('/') . Storage::url('uploads/menu/' . $imagePath);
            $result['image']['link'] = $imageUrl;
        } else {
            return response()->failed('input process error', 400);
        }
        ActivityHelper::log('Membuat Menu Baru');
        return response()->success($result, 200);
    }

    public function update($uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required',
            'category_id' => 'required',
            'type' => 'required',
            'description' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:30720',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $checkExist = $this->menuModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Menu not found', 404);
        }

        $validatedData = $validator->validated();
        if (isset($validatedData['image'])) {
            $imageData = $this->image->store($validatedData['image'], 'menu')['data'];
            unset($validatedData['image']);
            $validatedData['image_id'] = $imageData['uuid'];
        }

        $update = $this->menuModel->edit($validatedData, $uuid);

        ActivityHelper::log('Mengubah Menu');
        if ($update) {
            $data = $this->menuModel->getById($uuid);
            $imagePath = $data['data']['image']['name'];
            $imageUrl = url('/') . Storage::url('uploads/menu/' . $imagePath);
            $data['data']['image']['link'] = $imageUrl;
            return response()->success($data, 200);
        }
        return response()->failed('Something error', 422);
    }

    public function destroy($uuid)
    {
        $checkExist = $this->menuModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Menu category not found', 404);
        }

        $data = $this->menuModel->drop($uuid);
        ActivityHelper::log('Menghapus Menu');
        if ($data['status']) {
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    public function indexCategory()
    {
        $data = $this->menuCategoryModel->getAll();

        if (!$data) return response()->failed($data, 404);
        return response()->success($data, 200);
    }

    public function storeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }
        $data = $this->menuCategoryModel->store($validator->validated());
        ActivityHelper::log('Membuat Kategori Baru');
        return response()->success($data, 200);
    }

    public function showCategory($uuid)
    {
        $data = $this->menuCategoryModel->getById($uuid);
        if (!$data['data']) {
            return response()->failed('Category not found', 404);
        }

        return response()->success($data, 200);
    }

    public function updateCategory($uuid, Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $checkExist = $this->menuCategoryModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Menu category not found', 404);
        }

        $update = $this->menuCategoryModel->edit($validator->validated(), $uuid);
        ActivityHelper::log('Mengubah Kategori');
        if ($update) {
            $data = $this->menuCategoryModel->getById($uuid);
            return response()->success($data, 200);
        }
        return response()->failed('Something error', 422);
    }

    public function destroyCategory($uuid)
    {
        $checkExist = $this->menuCategoryModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Menu category not found', 404);
        }

        $data = $this->menuCategoryModel->drop($uuid);
        ActivityHelper::log('Menghapus Kategori');
        if ($data['status']) {
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    public function indexRecipes($uuidMenu)
    {
        $data = $this->recipeModel->getAll($uuidMenu);

        if (!$data) return response()->failed($data, 404);
        return response()->success($data, 200);
    }

    public function storeRecipes($uuidMenu, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'recipes' => 'required',
                'recipes.*.name' => 'required',
                'recipes.*.quantity' => 'required',
                'recipes.*.unit' => 'required',
                'recipes.*.portion' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->failed($validator->errors(), 400);
            }

            foreach ($validator->validated()["recipes"] as $item) {
                $data = $this->recipeModel->store(array_merge($item, [
                    'menu_id' => $uuidMenu
                ]));
            }
            ActivityHelper::log('Membuat Resep Baru');

            $data = $this->recipeModel->getAll($uuidMenu);
            if (!$data) return response()->failed($data, 404);
            return response()->success($data, 200);
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 400);
        }
    }

    public function updateRecipes($uuidMenu, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'recipes' => 'required',
                'recipes.*.uuid' => 'nullable',
                'recipes.*.name' => 'required',
                'recipes.*.quantity' => 'required',
                'recipes.*.unit' => 'required',
                'recipes.*.portion' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->failed($validator->errors(), 400);
            }
            $validatedData = $validator->validated();
            $recipeUuidsFromRequest = array_column($validatedData['recipes'], 'uuid');

            // Ambil semua UUID resep yang ada di database untuk menu ini
            $existingRecipes = $this->recipeModel->getAll($uuidMenu)['data']->toArray();
            $existingRecipeUuids = array_column($existingRecipes, 'uuid');
            // Hapus resep yang tidak ada di array request
            $recipesToDelete = array_diff($existingRecipeUuids, $recipeUuidsFromRequest);

            if (!empty($recipesToDelete)) {
                foreach ($recipesToDelete as $item) {
                    $this->recipeModel->drop($item);
                }
            }

            foreach ($validator->validated()["recipes"] as $item) {

                if (isset($item['uuid'])) {
                    $data = $this->recipeModel->edit(array_merge($item, [
                        'menu_id' => $uuidMenu
                    ]));
                } else {
                    $data = $this->recipeModel->store(array_merge($item, [
                        'menu_id' => $uuidMenu
                    ]));
                }
            }
            ActivityHelper::log('Mengubah Resep');
            $data = $this->recipeModel->getAll($uuidMenu);
            if (!$data['data']) return response()->failed($data, 404);
            return response()->success($data, 200);
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 400);
        }
    }

    public function destroyRecipes($uuid)
    {
        $checkExist = $this->recipeModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Recipe not found', 404);
        }

        $data = $this->recipeModel->drop($uuid);
        ActivityHelper::log('Menghapus Resep');
        if ($data['status']) {
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 400);
    }
}

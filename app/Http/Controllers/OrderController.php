<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityHelper;
use App\Models\EventLogisticModel;
use App\Models\EventStaffModel;
use App\Models\EventTransferItemModel;
use App\Models\InventoryItemModel;
use App\Models\OrderCustomizationModel;
use App\Models\OrderDetailModel;
use App\Models\OrderPackageItems;
use App\Models\OrderPackageModel;
use App\Models\OrderPackageRelModel;
use App\Models\OrdersCustomersModel;
use App\Models\PackageFoodStallModel;
use App\Models\PackagesDataModel;
use App\Models\PackagesModel;
use App\Models\PaymentDetailModel;
use App\Models\PaymentInstallmentsModel;
use App\Models\PaymentsModel;
use App\Models\RecipesModel;
use App\Models\UserModel;
use App\Models\CustomerModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    private $orderModel;
    private $orderDetailModel;
    private $orderPackageModel;
    private $orderPackageRelModel;
    private $orderPackageItem;
    private $orderMenuModel;
    private $payment;
    private $paymentDetailModel;
    private $installmentModel;
    private $eventItemModel;
    private $recipesModel;
    private $inventoryModel;
    private $eventStaffModel;
    private $transferItemModel;
    private $packageModel;
    private $packageDataModel;
    private $packageFoodStall;
    private $customerModel;

    public function __construct()
    {
        $this->orderModel = new OrdersCustomersModel();
        $this->orderDetailModel = new OrderDetailModel();
        $this->orderPackageModel = new OrderPackageModel();
        $this->orderPackageRelModel = new OrderPackageRelModel();
        $this->orderPackageItem = new OrderPackageItems();
        $this->orderMenuModel = new OrderCustomizationModel();
        $this->payment = new PaymentsModel();
        $this->installmentModel = new PaymentInstallmentsModel();
        $this->eventItemModel = new EventLogisticModel();
        $this->recipesModel = new RecipesModel();
        $this->inventoryModel = new InventoryItemModel();
        $this->eventStaffModel = new EventStaffModel();
        $this->transferItemModel = new EventTransferItemModel();
        $this->packageModel = new PackagesModel();
        $this->paymentDetailModel = new PaymentDetailModel();
        $this->packageDataModel = new PackagesDataModel();
        $this->packageFoodStall = new PackageFoodStallModel();
        $this->customerModel = new CustomerModel();
    }

    public function index(Request $request)
    {
        $filter = [
            'order_name' => $request->order_name ?? '',
            'customer_id' => $request->customer_id ?? '',
            'customer_name' => $request->customer_name ?? '',
            'field_coordinator' => $request->field_coordinator ?? '',
            'location' => $request->location ?? '',
            'event_date_start' => $request->event_date_start ?? '',
            'event_date_end' => $request->event_date_end ?? '',
            'event_time_start' => $request->event_time_start ?? '',
            'event_time_end' => $request->event_time_end ?? '',
            'status' => $request->status ?? '',
            'year' => $request->year ?? '',
            'month' => $request->month ?? '',
        ];

        $data = $this->orderModel->getAll($filter);

        if (!$data) {
            return response()->failed($data, 404);
        }

        return response()->success($data, 200);
    }

    public function show($uuid)
    {
        $data = $this->orderModel->getById($uuid);
        if (!$data['data']) {
            return response()->failed('Order not found', 404);
        }

        return response()->success($data, 200);
    }

    public function update($uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'location' => 'nullable',
            'event_date' => 'required',
            'event_time' => 'nullable',
            'portion' => 'nullable',
            'note' => 'nullable',
            'status' => 'required',
            'akad_start' => 'nullable',
            'akad_end' => 'nullable',
            'resepsi_start' => 'nullable',
            'resepsi_end' => 'nullable',
            'nuance' => 'nullable',
            'general_buffet' => 'nullable',
            'vip_buffet' => 'nullable',
            'vip_table' => 'nullable',
            'wedding_food_table' => 'nullable',
            'akad_table' => 'nullable',
            'reception_table' => 'nullable',
            'for_naib' => 'nullable',
            'ayam_bekakak_nasi_punar' => 'nullable',
            'mica_for_besan' => 'nullable'
        ]);
        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $checkExist = $this->orderModel->getById($uuid);

        if (!$checkExist['data']) {
            return response()->failed('Order not found', 404);
        }

        $validatedData = $validator->validated();
        $orderData = [
            'customer_id' => $validatedData['customer_id'],
            'location' => $validatedData['location'] ?? null,
            'event_date' => $validatedData['event_date'],
            'event_time' => $validatedData['event_time'] ?? null,
            'portion' => $validatedData['portion'] ?? null,
            'note' => $validatedData['note'] ?? null,
            'status' => $validatedData['status']
        ];

        $update = $this->orderModel->edit($orderData, $uuid);
        $getDetail = $this->orderDetailModel->getByOrder($uuid);
        if ($getDetail['data']) {
            $detailData = [
                'uuid' => $getDetail['data']['uuid'],
                'akad_start' => $validatedData['akad_start'] ?? null,
                'akad_end' => $validatedData['akad_end'] ?? null,
                'resepsi_start' => $validatedData['resepsi_start'] ?? null,
                'resepsi_end' => $validatedData['resepsi_end'] ?? null,
                'nuance' => $validatedData['nuance'] ?? null,
                'general_buffet' => $validatedData['general_buffet'] ?? null,
                'vip_buffet' => $validatedData['vip_buffet'] ?? null,
                'vip_table' => $validatedData['vip_table'] ?? null,
                'wedding_food_table' => $validatedData['wedding_food_table'] ?? null,
                'akad_table' => $validatedData['akad_table'] ?? null,
                'reception_table' => $validatedData['reception_table'] ?? null,
                'for_naib' => $validatedData['for_naib'] ?? null,
                'ayam_bekakak_nasi_punar' => $validatedData['ayam_bekakak_nasi_punar'] ?? null,
                'mica_for_besan' => $validatedData['mica_for_besan'] ?? null
            ];
            $updateDetail = $this->orderDetailModel->edit($detailData);
        } else {
            $detailData = [
                'order_id' => $uuid,
                'akad_start' => $validatedData['akad_start'] ?? null,
                'akad_end' => $validatedData['akad_end'] ?? null,
                'resepsi_start' => $validatedData['resepsi_start'] ?? null,
                'resepsi_end' => $validatedData['resepsi_end'] ?? null,
                'nuance' => $validatedData['nuance'] ?? null,
                'general_buffet' => $validatedData['general_buffet'] ?? null,
                'vip_buffet' => $validatedData['vip_buffet'] ?? null,
                'vip_table' => $validatedData['vip_table'] ?? null,
                'wedding_food_table' => $validatedData['wedding_food_table'] ?? null,
                'akad_table' => $validatedData['akad_table'] ?? null,
                'reception_table' => $validatedData['reception_table'] ?? null,
                'for_naib' => $validatedData['for_naib'] ?? null,
                'ayam_bekakak_nasi_punar' => $validatedData['ayam_bekakak_nasi_punar'] ?? null,
                'mica_for_besan' => $validatedData['mica_for_besan'] ?? null
            ];
            $updateDetail = $this->orderDetailModel->store($detailData);
        }

        if ($update['status'] && $updateDetail['status']) {
            $data = $this->orderModel->getById($uuid)['data'];
            ActivityHelper::log('Mengubah Order ' . $data['order_name']);
            return response()->success($data, 200);
        }
        return response()->failed('Something error', 422);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'location' => 'nullable',
            'event_date' => 'required',
            'event_time' => 'nullable',
            'portion' => 'nullable',
            'note' => 'nullable',
            'status' => 'required',
            'akad_start' => 'nullable',
            'akad_end' => 'nullable',
            'resepsi_start' => 'nullable',
            'resepsi_end' => 'nullable',
            'nuance' => 'nullable',
            'general_buffet' => 'nullable',
            'vip_buffet' => 'nullable',
            'vip_table' => 'nullable',
            'wedding_food_table' => 'nullable',
            'akad_table' => 'nullable',
            'reception_table' => 'nullable',
            'for_naib' => 'nullable',
            'ayam_bekakak_nasi_punar' => 'nullable',
            'mica_for_besan' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $validatedData = $validator->validated();
        $orderData = [
            'customer_id' => $validatedData['customer_id'],
            'location' => $validatedData['location'] ?? null,  // Gunakan null jika tidak ada data
            'event_date' => $validatedData['event_date'],
            'event_time' => $validatedData['event_time'] ?? null,
            'portion' => $validatedData['portion'] ?? null,
            'note' => $validatedData['note'] ?? null,
            'status' => $validatedData['status']
        ];
        $data = $this->orderModel->store($orderData)['data'];

        $detailData = [
            'order_id' => $data['uuid'],
            'akad_start' => $validatedData['akad_start'] ?? null,
            'akad_end' => $validatedData['akad_end'] ?? null,
            'resepsi_start' => $validatedData['resepsi_start'] ?? null,
            'resepsi_end' => $validatedData['resepsi_end'] ?? null,
            'nuance' => $validatedData['nuance'] ?? null,
            'general_buffet' => $validatedData['general_buffet'] ?? null,
            'vip_buffet' => $validatedData['vip_buffet'] ?? null,
            'vip_table' => $validatedData['vip_table'] ?? null,
            'wedding_food_table' => $validatedData['wedding_food_table'] ?? null,
            'akad_table' => $validatedData['akad_table'] ?? null,
            'reception_table' => $validatedData['reception_table'] ?? null,
            'for_naib' => $validatedData['for_naib'] ?? null,
            'ayam_bekakak_nasi_punar' => $validatedData['ayam_bekakak_nasi_punar'] ?? null,
            'mica_for_besan' => $validatedData['mica_for_besan'] ?? null
        ];
        $dataDetail = $this->orderDetailModel->store($detailData);
        $payment = $this->payment->store(['order_id' => $data['uuid'], 'status' => 'not finished']);
        $order = $this->orderModel->getById($data['uuid'])['data'];
        ActivityHelper::log('Membuat Order ' . $order['order_name']);
        return response()->success($order, 200);
    }

    public function storeWithCustomer(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                // Customer
                'customer_id' => 'nullable|uuid',
                'customer_name' => 'required',
                'phone_number_1' => 'nullable',
                'phone_number_2' => 'nullable',
                'address' => 'nullable',

                // Order
                'location' => 'nullable',
                'event_date' => 'required',
                'event_time' => 'nullable',
                'portion' => 'nullable',
                'note' => 'nullable',
                'status' => 'required',

                // Order Detail
                'akad_start' => 'nullable',
                'akad_end' => 'nullable',
                'resepsi_start' => 'nullable',
                'resepsi_end' => 'nullable',
                'nuance' => 'nullable',
                'general_buffet' => 'nullable',
                'vip_buffet' => 'nullable',
                'vip_table' => 'nullable',
                'wedding_food_table' => 'nullable',
                'akad_table' => 'nullable',
                'reception_table' => 'nullable',
                'for_naib' => 'nullable',
                'ayam_bekakak_nasi_punar' => 'nullable',
                'mica_for_besan' => 'nullable'
            ]);

            if ($validator->fails()) {
                return response()->failed($validator->errors(), 400);
            }

            $validatedData = $validator->validated();

            //Customer
            $customer = null;

            if (!empty($validatedData['customer_id'])) {
                $customer = $this->customerModel
                    ->where('uuid', $validatedData['customer_id'])
                    ->where('name', $validatedData['customer_name'])
                    ->first();
            }

            if (!$customer) {
                $customer = $this->customerModel
                    ->where('name', $validatedData['customer_name'])
                    ->first();
            }

            if (!$customer) {
                $customerData = [
                    'name' => $validatedData['customer_name'],
                    'phone_number_1' => $validatedData['phone_number_1'] ?? null,
                    'phone_number_2' => $validatedData['phone_number_2'] ?? null,
                    'address' => $validatedData['address'] ?? null
                ];

                $customer = $this->customerModel->store($customerData)['data'];
            }

            //Order
            $orderData = [
                'customer_id' => $customer['uuid'],
                'location' => $validatedData['location'] ?? null,
                'event_date' => $validatedData['event_date'],
                'event_time' => $validatedData['event_time'] ?? null,
                'portion' => $validatedData['portion'] ?? null,
                'note' => $validatedData['note'] ?? null,
                'status' => $validatedData['status']
            ];

            $data = $this->orderModel->store($orderData)['data'];

            $detailData = [
                'order_id' => $data['uuid'],
                'akad_start' => $validatedData['akad_start'] ?? null,
                'akad_end' => $validatedData['akad_end'] ?? null,
                'resepsi_start' => $validatedData['resepsi_start'] ?? null,
                'resepsi_end' => $validatedData['resepsi_end'] ?? null,
                'nuance' => $validatedData['nuance'] ?? null,
                'general_buffet' => $validatedData['general_buffet'] ?? null,
                'vip_buffet' => $validatedData['vip_buffet'] ?? null,
                'vip_table' => $validatedData['vip_table'] ?? null,
                'wedding_food_table' => $validatedData['wedding_food_table'] ?? null,
                'akad_table' => $validatedData['akad_table'] ?? null,
                'reception_table' => $validatedData['reception_table'] ?? null,
                'for_naib' => $validatedData['for_naib'] ?? null,
                'ayam_bekakak_nasi_punar' => $validatedData['ayam_bekakak_nasi_punar'] ?? null,
                'mica_for_besan' => $validatedData['mica_for_besan'] ?? null
            ];

            $dataDetail = $this->orderDetailModel->store($detailData);

            //payment
            $payment = $this->payment->store([
                'order_id' => $data['uuid'],
                'status' => 'not finished'
            ]);

            //Get Order
            $order = $this->orderModel->getById($data['uuid'])['data'];

            ActivityHelper::log('Membuat Order ' . $order['order_name']);

            return response()->success($order, 200);

        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 422);
        }
    }


    public function destroy($uuid)
    {
        $checkExist = $this->orderModel->getById($uuid);

        if (!$checkExist['data']) {
            return response()->failed('Order not found', 404);
        }

        // jika sudah membayar dilakukan cancle
        $checkPayment = $this->payment->getByOrder($uuid)['data'];

        if (!$checkPayment) {
            $checkInstallment = $this->installmentModel->getAllByPayment($checkPayment['uuid'])['data'];
            if (!$checkInstallment) {
                $data = $this->orderModel->drop($uuid);
                $dataDetail = $this->orderDetailModel->dropByOrderId($uuid);
                $dataPayment = $this->payment->drop($checkPayment['uuid']);
                ActivityHelper::log('Menghapus Order ' . $checkExist['data']['order_name']);
                return response()->success('Delete success', 200);
            } else {
                $data = $this->orderModel->edit(['status' => 'canceled'], $uuid);
                $dataPayment = $this->payment->edit($checkPayment['uuid'], ['status' => 'canceled']);
                ActivityHelper::log('Mengubah Order ' . $checkExist['data']['order_name']);
                return response()->success('Delete success', 200);
            }
        } else {
            $data = $this->orderModel->drop($uuid);
            $dataDetail = $this->orderDetailModel->dropByOrderId($uuid);
            ActivityHelper::log('Menghapus Order ' . $checkExist['data']['order_name']);
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    // 000000 tinggal refresh
    public function indexMenus($uuidOrder, Request $request)
    {
        $results = collect([
            'list_package' => $this->packageModel->getAll(),
            'package' => $this->orderPackageModel->getAllOrder($uuidOrder),
            'package_menu' => $this->orderPackageItem->getAllOrder($uuidOrder),
            'custom_menu' => $this->orderMenuModel->getAllOrder($uuidOrder),
            'payment' => $this->payment->getByOrder($uuidOrder)
        ]);
//        return $results;
        if ($results->contains(fn($result) => !$result['status'])) {
            return response()->failed('Items not found', 404);
        }
//        return $results['package']['data']->pluck('uuid')->first();

        $packageId = collect($results['package']['data'])->pluck('package_id');

        if ($packageId->isEmpty()) {
            $sortList = collect([]);
        } else {
            $sortArr = $this->packageDataModel->getSortCategory($packageId->toArray());
            $sortList = collect($sortArr['data'])->map(fn($item) => $item['category']['name']);
        }


        $handlers = [
            'list_package' => function ($result) {
                return $result['data'];
            },
            'package' => function ($result) {
                $result = $result['data']->map(function ($item) {
                    return [
                        'uuid' => $item->uuid,
                        'type' => $item->package->type,
                        'package_id' => $item->package_id,
                        'name' => $item->package->name,
                        'portion' => $item->portion,
                        'price' => $item->package->price,
                        'details' => $item->details,
                    ];
                });
                return $result;
            },
            'package_menu' => function ($result) use ($sortList) {
                $result = $result['data']->map(function ($item) {
                    // Cek kategori menu untuk mendapatkan quantity_percent
                    $quantityPercent = null;
                    if ($item->menu->category) {
                        $quantityPercent = collect($item->menu->category->packageData ?? [])
                            ->first()['quantity_percent'] ?? null;

                        if (is_null($quantityPercent)) {
                            $quantityPercent = collect($item->menu->food_stall ?? [])
                                ->first()['quantity_percent'] ?? null;
                        }
                    }

                    return [
                        'uuid' => $item->uuid,
                        'menu_id' => $item->menu_id,
                        'portion' => $item->portion,
                        'details' => $item->details,
                        'status' => $item->status,
                        'menu' => [
                            'uuid' => $item->menu->uuid,
                            'name' => $item->menu->name,
                            'type' => $item->menu->type,
                        ],
                        'category' => [
                            'category_id' => $item->menu->category_id ?? null,
                            'name' => $item->menu->category->name ?? null,
                            'quantity_percent' => $quantityPercent,
                        ],
                    ];
                });

                $result = $result->sortBy(function ($item) use ($sortList) {
                    $name = strtolower(trim($item['category']['name'] ?? ''));
                    $pos = array_search($name, (array)$sortList, true);
                    return $pos === false ? PHP_INT_MAX : $pos;
                })->values();
                return $result;
            },
            'custom_menu' => function ($result) {
                return $result['data'];
            },
            'payment' => function ($result) {
                return [
                    'uuid' => $result['data']->uuid,
                    'total_price' => $result['data']->amount,
                    'others' => collect($result['data']->paymentDetails)->map(function ($item) {
                        return [
                            'uuid' => $item->uuid,
                            'type' => $item->type,
                            'price' => $item->price,
                            'details' => $item->details,
                        ];
                    })
                ];
            },
        ];

        // Terapkan mapping berdasarkan key jika tersedia, jika tidak, return default
        $results = $results->map(function ($result, $key) use ($handlers) {
            return isset($handlers[$key]) ? $handlers[$key]($result) : $result;
        });

        return response()->success([
            'status' => true,
            'data' => $results->map(fn($result) => $result),
        ], 200);
    }

    public function storeMenus($uuidOrder, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'package' => 'nullable|array',
                'package.*.package_id' => 'required',
                'package.*.details' => 'nullable',
                'package_menu' => 'nullable|array',
                'package_menu.*.package_id' => 'required',
                'package_menu.*.menu_id' => 'required',
                'package_menu.*.portion' => 'required',
                'package_menu.*.details' => 'nullable',
                'custom_menu' => 'nullable|array',
                'custom_menu.*.menu_id' => 'required',
                'custom_menu.*.portion' => 'required',
                'custom_menu.*.details' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->failed($validator->errors(), 400);
            }

            $validatedData = $validator->validated();
            $orderPackages = [];
            $packageMenus = [];

            if (isset($validatedData['package']) && isset($validatedData['package_menu'])) {
                $packageMenusGrouped = collect($validatedData['package_menu'])->groupBy('package_id');
                foreach ($validatedData['package'] as $packageData) {
                    $packageData['order_id'] = $uuidOrder;
                    $storePackage = $this->orderPackageModel->store($packageData)['data'];
                    $orderPackages[] = $storePackage;

                    // Ambil menu yang sesuai dengan package_id
                    $menusForCurrentPackage = $packageMenusGrouped[$storePackage['package_id']] ?? [];

                    foreach ($menusForCurrentPackage as $item) {
                        unset($item['package_id']); // Hapus package_id sebelum disimpan
                        $item['order_package_id'] = $storePackage['uuid'];
                        $storeMenu = $this->orderPackageItem->store($item)['data'];
                        $packageMenus[] = $storeMenu;
                    }
                }
            }


            $customMenus = [];
            if (isset($validatedData['custom_menu'])) {
                foreach ($validatedData['custom_menu'] as $item) {
                    $item['order_id'] = $uuidOrder;
                    $customMenus[] = $this->orderMenuModel->store($item)['data'];
                }
            }
            ActivityHelper::log('Membuat Order Item Baru');

            $data = [
                'status' => true,
                'data' => [
                    'package' => $orderPackages,
                    'package_menu' => $packageMenus,
                    'custom_menu' => $customMenus
                ]
            ];
            return response()->success($data, 200);
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 422);
        }
    }

    public function showMenus($uuidOrderPackage)
    {
        $result = $this->orderPackageModel->getById($uuidOrderPackage);
        if (!$result['status']) return response()->failed('Items not found', 404);
        return response()->success($result, 200);
    }

    public function updateMenus($uuidOrder, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'package' => 'nullable|array',
                'package.*.uuid' => 'nullable|uuid',
                'package.*.package_id' => 'required|uuid',
                'package.*.portion' => 'required',
                'package.*.details' => 'nullable',
                'package_menu' => 'nullable|array',
                'package_menu.*.uuid' => 'nullable|uuid',
                'package_menu.*.menu_id' => 'required|uuid',
                'package_menu.*.portion' => 'required',
                'package_menu.*.details' => 'nullable',
                'package_menu.*.status' => 'required',
                'custom_menu' => 'nullable|array',
                'custom_menu.*.uuid' => 'nullable|uuid',
                'custom_menu.*.menu_id' => 'nullable|uuid',
                'custom_menu.*.portion' => 'nullable',
                'custom_menu.*.details' => 'nullable',
                'payment' => 'required',
                'payment.uuid' => 'required|uuid',
                'payment.price' => 'required',
                'payment.others' => 'nullable|array',
                'payment.others.*.uuid' => 'nullable|uuid',
                'payment.others.*.type' => 'required',
                'payment.others.*.price' => 'required',
                'payment.others.*.details' => 'nullable',
                'payment.total_price' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->failed($validator->errors(), 400);
            }

            $validatedData = $validator->validated();
            // return response()->success($validatedData, 200);

            // delete
            $existingPackages = $this->orderPackageModel->getAll($uuidOrder)['data']->pluck('uuid')->toArray();
            $existingPackageMenus = $this->orderPackageItem->getByOrder($uuidOrder)['data']->pluck('uuid')->toArray();
            $existingCustomMenus = $this->orderMenuModel->getAll($uuidOrder)['data']->pluck('uuid')->toArray();
            $existingPaymentDetail = $this->paymentDetailModel->getAll($validatedData['payment']['uuid'])['data']->pluck('uuid')->toArray();

            $newPackageUuids = array_column($validatedData['package'] ?? [], 'uuid');
            $newPackageMenuUuids = array_column($validatedData['package_menu'] ?? [], 'uuid');
            $newCustomMenuUuids = array_column($validatedData['custom_menu'] ?? [], 'uuid');
            $newPaymentDetailUuids = array_column($validatedData['payment']['others'], 'uuid');

            $packageIdDiff = array_diff($existingPackages, $newPackageUuids);

            $this->orderPackageModel->manyDrop($packageIdDiff);
            $this->orderPackageRelModel->manyDrop($packageIdDiff);
            $this->orderPackageItem->updateStatusBatchToFalse(array_diff($existingPackageMenus, $newPackageMenuUuids));
            $this->orderMenuModel->manyDrop(array_diff($existingCustomMenus, $newCustomMenuUuids));
            $this->paymentDetailModel->manyDrop(array_diff($existingPaymentDetail, $newPaymentDetailUuids));

            // insert
            $categoryPackage = [];
            foreach ($validatedData['package'] ?? [] as $packageData) {
                $packageData['order_id'] = $uuidOrder;
                if (!empty($packageData['uuid'])) {
                    $package = $this->orderPackageModel->edit($packageData);
                    $packageId = $packageData['uuid'];
                } else {
                    $package = $this->orderPackageModel->store($packageData);
                    $packageId = $package['data']['uuid'];
                }
                if (!$package['status']) throw new Exception("Update Order Package Failed");
                $getPackage = $this->packageModel->getById($packageData['package_id'])['data'];
                $categoryPackage[] = [
                    'package_uuid' => $packageId,
                    'menu_uuid' => collect($getPackage->packageData)
                        ->pluck('category.menu')
                        ->flatten(1)
                        ->pluck('uuid')
                        ->merge($getPackage->packageFoodStall->pluck('menu.uuid'))
                        ->values(),
                ];
            }

            foreach ($validatedData['package_menu'] ?? [] as $packageMenu) {
                if (!empty($packageMenu['uuid'])) {
                    $menuStatus = $this->orderPackageItem->edit($packageMenu, $packageMenu['uuid']);
                    $orderMenuId = $packageMenu['uuid'];
                } else {
                    $menuStatus = $this->orderPackageItem->store($packageMenu);
                    $orderMenuId = $menuStatus['data']['uuid'];
                }
                $menuId = $packageMenu['menu_id'];
                if (!$menuStatus['status']) throw new Exception("Update Order Menu Failed");

                $packageRels = collect($categoryPackage)->filter(function ($package) use ($menuId) {
                    return in_array($menuId, collect($package['menu_uuid'])->toArray());
                })->map(function ($package) use ($orderMenuId) {
                    return [
                        'order_package_id' => $package['package_uuid'],
                        'order_package_item_id' => $orderMenuId
                    ];
                })->toArray();

                foreach ($packageRels as $item) {
                    $test = $this->orderPackageRelModel->store($item);
                }
            }
            foreach ($validatedData['custom_menu'] ?? [] as $item) {
                $item["order_id"] = $uuidOrder;
                if (!empty($item['uuid'])) {
                    $this->orderMenuModel->edit($item, $item['uuid']);
                } else {
                    $this->orderMenuModel->store($item);
                }
            }

            $checkExist = $this->payment->getById($validatedData['payment']['uuid']);
            if (!$checkExist['data']) throw new Exception('Payment not found');
            $update = $this->payment->edit($validatedData['payment']['uuid'], ['amount' => $validatedData['payment']['total_price']]);

            foreach ($validatedData['payment']['others'] ?? [] as $item) {
                $item["payment_id"] = $validatedData['payment']['uuid'];
                if (!empty($item['uuid'])) {
                    $this->paymentDetailModel->edit($item, $item['uuid']);
                } else {
                    $this->paymentDetailModel->store($item);
                }
            }

            ActivityHelper::log('Mengubah Order Item');

            $results = collect([
                'package' => $this->orderPackageModel->getAllOrder($uuidOrder),
                'package_menu' => $this->orderPackageItem->getAllOrder($uuidOrder),
                'custom_menu' => $this->orderMenuModel->getAllOrder($uuidOrder),
                'payment' => $this->payment->getByOrder($uuidOrder)
            ]);

            if ($results->contains(fn($result) => !$result['status'])) {
                return response()->failed('Items not found', 404);
            }
//            dd($results);
//            return response()->success($results['package']['data'], 200);
            return response()->success([
                'status' => true,
                'message' => 'Order item berhasil diperbarui',
                'data' => [
                    'package' => $results['package']['data']->map(function ($item) {
                        return [
                            'uuid' => $item->uuid,
                            'type' => $item->package->type,
                            'package_id' => $item->package_id,
                            'name' => $item->package->name,
                            'portion' => $item->portion,
                            'price' => $item->package->price,
                            'details' => $item->details,
                        ];
                    }),
                    'package_menu' => $results['package_menu']['data']->map(function ($item) {
                        $quantityPercent = null;
                        if ($item->menu->category) {
                            $quantityPercent = collect($item->menu->category->packageData ?? [])
                                ->first()['quantity_percent'] ?? null;

                            if (is_null($quantityPercent)) {
                                $quantityPercent = collect($item->menu->food_stall ?? [])
                                    ->first()['quantity_percent'] ?? null;
                            }
                        }

                        return [
                            'uuid' => $item->uuid,
                            'menu_id' => $item->menu_id,
                            'portion' => $item->portion,
                            'details' => $item->details,
                            'status' => $item->status,
                            'menu' => [
                                'uuid' => $item->menu->uuid,
                                'name' => $item->menu->name,
                            ],
                            'category' => [
                                'category_id' => $item->menu->category_id ?? null,
                                'name' => $item->menu->category->name ?? null,
                                'quantity_percent' => $quantityPercent,
                            ],
                        ];
                    }),
                    'custom_menu' => $results['custom_menu']['data']->map(function ($item) {
                        return [
                            'uuid' => $item->uuid,
                            'order_id' => $item->order_id,
                            'menu_id' => $item->menu_id,
                            'portion' => $item->portion,
                            'details' => $item->details,
                            'menu' => [
                                'uuid' => $item->menu->uuid,
                                'name' => $item->menu->name,
                            ]
                        ];
                    }),
                    'payment' => [
                        'uuid' => $results['payment']['data']->uuid,
                        'total_price' => $results['payment']['data']->amount,
                        'others' => collect($results['payment']['data']->paymentDetails ?? [])->map(function ($item) {
                            return [
                                'uuid' => $item->uuid,
                                'type' => $item->type,
                                'price' => $item->price,
                                'details' => $item->details,
                            ];
                        })->values(),
                    ],
                ]
            ], 200);

        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 422);
        }
    }

    // get data menu dari paket tanpa duplikat
    public function listMenu($uuidOrder, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'package' => 'nullable|array',
                'package.*.uuid' => 'nullable|uuid',
                'package.*.package_id' => 'required|uuid',
                'package.*.portion' => 'required',
                'package.*.details' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->failed($validator->errors(), 400);
            }
            $validatedData = (object)$validator->validated();
            $existingPackages = $this->orderPackageModel->getAll($uuidOrder)['data']->pluck('uuid');
            $newPackageUuids = collect($validatedData->package)->pluck('uuid');
            $packageIdDiff = $existingPackages->diff($newPackageUuids)->toArray();
            $this->orderPackageModel->manyDrop($packageIdDiff);
            $this->orderPackageRelModel->manyDrop($packageIdDiff);
//            return "ok";
            $updatePackage = collect($validatedData->package)->map(function ($item) use ($uuidOrder) {
                $item['order_id'] = $uuidOrder;
                if ($item['uuid']) {
                    $package = $this->orderPackageModel->edit($item);
                    if (!$package['status']) throw new Exception("Update Order Package Failed");
                    return $packageId = $item['uuid'];
                } else {
                    $package = $this->orderPackageModel->store($item);
                    if (!$package['status']) throw new Exception("Insert Order Package Failed");
                    return $packageId = $package['data']['uuid'];
                }
            });

            $packageIdData = collect($validatedData->package)->pluck('package_id')->toArray();
            $packageItem = $this->packageDataModel->getAll($packageIdData)['data'];
            $packageMenus = collect($packageItem)
                ->pluck('category')
                ->flatten(1)
                ->flatMap(function ($category) {
                    return collect($category['menu'])->map(function ($menu) use ($category) {
                        return [
                            'uuid' => $menu['uuid'],
                            'name' => $menu['name'],
                            'type' => $menu['type'],
                            'category' => $category['name'], // simpan nama category
                        ];
                    });
                })
                ->unique('uuid')
                ->values();
            // ambil foodstall package pakcage->package foodstall
            $packageStallItem = $this->packageFoodStall->getAll($packageIdData)['data'];
            $FoodstallMenu = collect($packageStallItem)
                ->pluck('menu')
                ->flatten(1)
                ->unique('uuid')
                ->map(fn($menu) => [
                    'uuid' => $menu['uuid'],
                    'name' => $menu['name'],
                    'type' => $menu['type'],
                    'category' => strtolower($menu['type']),
                ])->values();

            $allItems = $packageMenus->merge($FoodstallMenu)->unique('uuid')->values();
            $orderItems = $this->orderPackageItem->getAllOrder($uuidOrder, 'all', $packageIdData);
//            return $orderItems;
            $orderMenus = collect($orderItems['data'])->map(fn($item) => [
                'uuid' => $item->uuid,
                'menu_id' => $item->menu_id,
                'name' => $item->menu->name,
                'type' => $item->menu->type,
                'category' => $item->menu->category->name,
                'status' => $item->status])->keyBy('menu_id');

            $sortArr = $this->packageDataModel->getSortCategory($packageIdData);
            $sortList = collect($sortArr['data'])->map(fn($item) => $item['category']['name'])->toArray();
            $categoryIndex = array_flip($sortList);
            $finalMenus = $allItems->map(function ($menu) use ($orderMenus) {
                $ordered = $orderMenus->get($menu['uuid']);
                return [
                    'uuid' => $ordered['uuid'] ?? "",
                    'menu_id' => $menu['uuid'],
                    'name' => $menu['name'],
                    'type' => $menu['type'],
                    'category' => $menu['category'],
                    'status' => $ordered['status'] ?? 0,
                ];
            })->sortBy(function ($item) use ($categoryIndex) {
                // Ambil nama kategori sebagai string
                $cat = $item['category'];
                $name = is_array($cat) ? ($cat['name'] ?? '') :
                    (is_object($cat) ? ($cat->name ?? '') : ($cat ?? ''));

                $key = strtolower(trim($name));
                $pos = $categoryIndex[$key] ?? PHP_INT_MAX; // yang tak terdaftar -> paling akhir

                // Gabungkan posisi kategori + nama menu agar stabil dalam satu kali sort
                return sprintf('%06d|%s', $pos, strtolower($item['name'] ?? ''));
            })->values();


            return response()->success($finalMenus, 200);

        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 422);
        }
    }

// jadi nanti pas di add dilakukan pengecekan dan update package menu
// logicnya begini, pertama input/update menu lalu update relasi antara package dan menu,
    public function addMenus($uuidOrder, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'menu' => 'nullable|array',
                'menu.*.uuid' => 'nullable|uuid',
                'menu.*.menu_id' => 'required|uuid',
                'menu.*.status' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->failed($validator->errors(), 400);
            }

            $validatedData = (object)$validator->validated();
            $existingPackages = $this->orderPackageModel->getAll($uuidOrder)['data'];
            $existingPackageMenus = $this->orderPackageItem->getByOrder($uuidOrder)['data']->pluck('uuid');
            $newPackageMenuUuids = collect($validatedData->menu)->pluck('uuid');
            $diffPackageMenuUuids = $existingPackageMenus->diff($newPackageMenuUuids)->toArray();
            $this->orderPackageItem->updateStatusBatchToFalse($diffPackageMenuUuids);

            $categoryPackage = $existingPackages->map(function ($item) {
                $getPackage = $this->packageModel->getById($item['package_id'])['data'];
                return [
                    'package_uuid' => $item['uuid'],
                    'menu_uuid' => collect($getPackage->packageData)
                        ->pluck('category.menu')
                        ->flatten(1)
                        ->pluck('uuid')
                        ->merge($getPackage->packageFoodStall->pluck('menu.uuid'))
                        ->values(),
                ];
            });


            $result = collect($validatedData->menu)->map(function ($item) use ($uuidOrder, $categoryPackage) {
                $menu = $this->orderPackageItem->addEditMenu($item);
                if (!$menu['status']) throw new Exception("Update Order Menu Failed");
                $packageRels = collect($categoryPackage)->filter(function ($package) use ($menu) {
                    return in_array($menu['data']['menu_id'], collect($package['menu_uuid'])->toArray());
                })->map(function ($package) use ($menu) {
                    $addRels = $this->orderPackageRelModel->store(
                        [
                            'order_package_id' => $package['package_uuid'],
                            'order_package_item_id' => $menu['data']['uuid']
                        ]
                    );
                    if (!$addRels['status']) throw new Exception("Update Rels Order Menu Failed");
                });
                return $menu['data'];
            });
            return response()->success([], 'Update Menu Items Success', 200);
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 422);
        }
    }

    public function refreshMenu($uuidOrder, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'package' => 'required|array',
                'package.*.uuid' => 'nullable',
                'package.*.package_id' => 'required',
                'package.*.portion' => 'required|integer|min:0',
                'package.*.details' => 'nullable',
            ]);

            if ($validator->fails()) {
                throw new Exception('Validasi data tidak cocok');
            }

            $validatedData = $validator->validated();

            // Ambil data paket dan menu yang sudah ada
            $existingPackages = $this->orderPackageModel->getAll($uuidOrder)['data'];
            $existingPackageMenus = $this->orderPackageItem->getByOrder($uuidOrder)['data'];
            $existingPackageRels = $this->orderPackageRelModel->getAllByOrder($uuidOrder)['data'];

            // Ambil UUID paket yang ada dan yang baru
            $existingPackageUuids = $existingPackages->pluck('uuid')->toArray();
            $newPackageUuids = array_column($validatedData['package'] ?? [], 'uuid');

            // Hitung paket yang dihapus
            $packageIdDiff = array_values(array_diff($existingPackageUuids, $newPackageUuids));

            // Hitung total porsi sebelum dan setelah perubahan
            $totalPortionOld = $existingPackages->sum('portion');
            $totalPortionNew = array_sum(array_column($validatedData['package'], 'portion'));

            // Grouping data berdasarkan UUID untuk efisiensi
            $packageRelsGroup = $existingPackageRels->groupBy('order_package_item_id');

            // Update porsi menu berdasarkan perubahan paket
            foreach ($existingPackageMenus as $menu) {
                if (!isset($packageRelsGroup[$menu['uuid']])) continue;

                foreach ($packageRelsGroup[$menu['uuid']] as $relation) {
                    if (in_array($relation['order_package_id'], $packageIdDiff)) {
                        $otherDataCheck = count($packageRelsGroup[$menu['uuid']]);

                        // Hitung porsi baru secara proporsional
                        $portion = ($totalPortionNew > 0) ?
                            max(0, $menu['portion'] * ($totalPortionNew / $totalPortionOld))
                            : 0;

                        if ($portion > 0 && $otherDataCheck > 1) {
                            $menu['portion'] = floor($portion);
                        } else {
                            $menu['portion'] = 0;
                            $menu['status'] = 0;
                        }

                        unset($menu['menu']);
                        $status = $this->orderPackageItem->edit($menu->toArray(), $menu['uuid']);
                        if ($status['data'] == false || $status['status'] == false) {
                            throw new Exception('Update Error');
                        }
                    }
                }
            }

            // Hapus paket & relasi yang tidak diperlukan
            $this->orderPackageModel->manyDrop($packageIdDiff);
            $this->orderPackageRelModel->manyDrop($packageIdDiff);

            foreach ($validatedData['package'] ?? [] as $packageData) {
                $packageData['order_id'] = $uuidOrder;
                if (!empty($packageData['uuid'])) {
                    $package = $this->orderPackageModel->edit($packageData);
                } else {
                    $package = $this->orderPackageModel->store($packageData);
                }
                if (!$package['status']) throw new Exception("Update Failed");
            }

            // Ambil hasil akhir setelah perubahan
            $results = collect([
                'package' => $this->orderPackageModel->getAllOrder($uuidOrder),
                'package_menu' => $this->orderPackageItem->getAllOrder($uuidOrder),
            ]);

            if ($results->contains(fn($result) => !$result['status'])) {
                return response()->failed('Items not found', 404);
            }

            // Optimasi hasil akhir dengan handler
            $handlers = [
                'package' => function ($result) {
                    $result['data'] = $result['data']->map(fn($item) => [
                        'uuid' => $item->uuid,
                        'type' => $item->package->type,
                        'package_id' => $item->package_id,
                        'name' => $item->package->name,
                        'portion' => $item->portion,
                        'price' => $item->package->price,
                        'details' => $item->details,
                    ]);
                    return $result;
                },
                'package_menu' => function ($result) {
                    $result['data'] = $result['data']->map(function ($item) {
                        $quantityPercent = optional($item->menu->category)->packageData[0]['quantity_percent']
                            ?? optional($item->menu->food_stall)[0]['quantity_percent']
                            ?? null;

                        return [
                            'uuid' => $item->uuid,
                            'menu_id' => $item->menu_id,
                            'portion' => $item->portion,
                            'details' => $item->details,
                            'status' => $item->status,
                            'menu' => [
                                'uuid' => $item->menu->uuid,
                                'name' => $item->menu->name,
                            ],
                            'category' => [
                                'category_id' => $item->menu->category_id ?? null,
                                'name' => optional($item->menu->category)->name,
                                'quantity_percent' => $quantityPercent,
                            ],
                        ];
                    });
                    return $result;
                },
            ];

            $results = $results->map(fn($result, $key) => $handlers[$key]($result) ?? $result);

            return response()->success([
                'status' => true,
                'data' => $results->map(fn($result) => $result),
            ], 200);
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 422);
        }
    }

    public function restorePackage($uuidOrderPackage)
    {
        $checkExist = $this->orderPackageModel->getByIdDeleted($uuidOrderPackage);
        if (!$checkExist['data']) {
            return response()->failed('Order not found', 404);
        }
        $data = $this->orderPackageModel->restoreById($uuidOrderPackage);
        ActivityHelper::log('Mengembalikan Order Item');

        if ($data['status']) {
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    public function restorePackageMenu($uuidOrderPackageMenu)
    {
        $checkExist = $this->orderPackageItem->getByIdDeleted($uuidOrderPackageMenu);
        if (!$checkExist['data']) {
            return response()->failed('Order not found', 404);
        }
        $data = $this->orderPackageItem->restoreById($uuidOrderPackageMenu);
        ActivityHelper::log('Mengembalikan Order Item');

        if ($data['status']) {
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    public function restoreCustomMenu($uuidOrderCustomMenu)
    {
        $checkExist = $this->orderMenuModel->getByIdDeleted($uuidOrderCustomMenu);
        if (!$checkExist['data']) {
            return response()->failed('Order not found', 404);
        }
        $data = $this->orderMenuModel->restoreById($uuidOrderCustomMenu);
        ActivityHelper::log('Mengembalikan Order Item');

        if ($data['status']) {
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    public function getAllFieldCoordinator()
    {
        try {
            $users = UserModel::whereHas('role', function ($query) {
                $query->where('name', 'Korlap'); // Ganti 'name' dengan nama kolom yang sesuai
            })->with('role')->get();
            return response()->success($users, 200);
        } catch (\Exception $e) {
            return response()->failed($e->getMessage(), 505);
        }
    }

    public function updateFieldCoordinator($uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'field_coordinator_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }
        $checkExist = $this->orderModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Order not found', 404);
        }

        $checkExist = UserModel::where('uuid', $validator->validated()['field_coordinator_id'])->first();
        if (!$checkExist) {
            return response()->failed('Field coordinator not found', 404);
        }

        $update = $this->orderModel->edit($validator->validated(), $uuid);
        if ($update) {
            $data = $this->orderModel->getById($uuid);
            ActivityHelper::log('Mengubah Korlap');
            return response()->success($data, 200);
        }
        return response()->failed('Something error', 422);
    }

    public function indexLogistic($uuidOrder)
    {
        $data = $this->eventItemModel->getAll($uuidOrder);
        if (!$data['status']) return response()->failed($data, 404);
        return response()->success($data, 200);
    }

    public function storeLogistic($uuidOrder, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.item_id' => 'required',
            'items.*.quantity' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $result = [];
        foreach ($validator->validated()["items"] as $item) {
            $item['order_id'] = $uuidOrder;
            $input = $this->eventItemModel->store($item);
            if ($input['status']) {
                $result[] = $input['data'];
            } else {
                return response()->failed('input error', 400);
            }
        }
        $data = [
            'status' => true,
            'data' => [
                'items' => $result
            ]
        ];
        ActivityHelper::log('Memasukan Event Item');
        return response()->success($data, 200);
    }

    public function updateLogistic($uuidOrder, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.uuid' => 'nullable',
            'items.*.item_id' => 'required',
            'items.*.quantity' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        // Ambil data yang sudah tervalidasi
        $validatedData = $validator->validated();

        // Ambil semua UUID dari request
        $itemUuidsFromRequest = array_column($validatedData['items'], 'uuid');

        // Ambil semua item yang ada di database untuk order ini
        $existingItems = $this->eventItemModel->getAll($uuidOrder)['data']->toArray();
        $existingItemUuids = array_column($existingItems, 'uuid');

        // Hapus item yang tidak ada di array request
        $itemsToDelete = array_diff($existingItemUuids, $itemUuidsFromRequest);
        if (!empty($itemsToDelete)) {
            foreach ($itemsToDelete as $uuid) {
                $this->eventItemModel->drop($uuid); // Hapus item yang tidak ada di request
            }
        }

        foreach ($validator->validated()["items"] as $item) {
            $item['order_id'] = $uuidOrder;
            if (isset($item['uuid'])) {
                $data = $this->eventItemModel->edit($item, $item['uuid']);
            } else {
                $data = $this->eventItemModel->store($item);
            }
            if (!$data['status']) {
                return response()->failed('input error', 400);
            }
        }
        $data = $this->eventItemModel->getAll($uuidOrder);
        ActivityHelper::log('Mengubah Event Item');
        return response()->success($data, 200);
    }

    public function destroyLogistic($uuid)
    {
        $checkExist = $this->eventItemModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Event Item not found', 404);
        }

        $data = $this->eventItemModel->drop($uuid);
        ActivityHelper::log('Menghapus Event Item');
        if ($data['status']) {
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    public function indexEventStaff($uuidOrder)
    {
        $data = $this->eventStaffModel->getAll($uuidOrder);

        if (!$data) return response()->failed($data, 404);
        return response()->success($data, 200);
    }

    public function storeEventStaff($uuidOrder, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff' => 'required|array',
            'staff.*.staff_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        foreach ($validator->validated()["staff"] as $item) {
            $item['order_id'] = $uuidOrder;
            $input = $this->eventStaffModel->store($item);
            if (!$input['status']) {
                return response()->failed('input error', 400);
            }
        }
        ActivityHelper::log('Memasukan Event Staff');
        $data = $this->eventStaffModel->getAll($uuidOrder);
        return response()->success($data, 200);
    }

    public function showEventStaff($uuid)
    {
        $data = $this->eventStaffModel->getById($uuid);

        if (!$data) return response()->failed($data, 404);
        return response()->success($data, 200);
    }

    public function updateEventStaff($uuidOrder, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staff' => 'required|array',
            'staff.*.uuid' => 'nullable',
            'staff.*.staff_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        // Ambil data yang sudah tervalidasi
        $validatedData = $validator->validated();

        // Ambil semua UUID dari request
        $itemUuidsFromRequest = array_column($validatedData['staff'], 'uuid');

        // Ambil semua item yang ada di database untuk order ini
        $existingItems = $this->eventStaffModel->getAll($uuidOrder)['data']->toArray();
        $existingItemUuids = array_column($existingItems, 'uuid');

        // Hapus item yang tidak ada di array request
        $itemsToDelete = array_diff($existingItemUuids, $itemUuidsFromRequest);
        if (!empty($itemsToDelete)) {
            foreach ($itemsToDelete as $uuid) {
                $this->eventStaffModel->drop($uuid); // Hapus item yang tidak ada di request
            }
        }

        foreach ($validator->validated()["staff"] as $item) {
            $item['order_id'] = $uuidOrder;
            if (isset($item['uuid'])) {
                $data = $this->eventStaffModel->edit($item, $item['uuid']);
            } else {
                $data = $this->eventStaffModel->store($item);
            }
            if (!$data['status']) {
                return response()->failed('input error', 400);
            }
        }
        $data = $this->eventStaffModel->getAll($uuidOrder);
        ActivityHelper::log('Mengubah Event Staff');
        return response()->success($data, 200);
    }

    public function destroyEventStaff($uuid)
    {
        $checkExist = $this->eventStaffModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Event Item not found', 404);
        }

        $data = $this->eventStaffModel->drop($uuid);
        ActivityHelper::log('Menghapus Event Item');
        if ($data['status']) {
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    public function eventEnd($uuidOrder, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'handover_of_leftovers' => 'nullable',
            'items' => 'required|array',
            'items.*.uuid' => 'required',
            'items.*.items_not_returned' => 'required',
            'items.*.in_client_hands' => 'required',
            'items.*.notes' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        // Ambil data yang sudah tervalidasi
        $validatedData = $validator->validated();
        $existingOrder = $this->orderModel->getById($uuidOrder);

        if (!$existingOrder['data']) {
            return response()->failed("Order not found", 404);
        }
        if ($existingOrder['data']['status'] == "event_completed" || $existingOrder['data']['status'] == "event_finish") {
            return response()->failed("Event has ended", 400);
        }

        foreach ($validatedData['items'] as $item) {
            $existingItem = $this->eventItemModel->getById($item['uuid'])['data'];

            if ($existingItem) {
                $getTransferItemQuantity = $this->transferItemModel->getByOrderAndItem($uuidOrder, $existingItem['item_id'])["data"];
                $itemDecrease = $item['items_not_returned'] + $item['in_client_hands'];

                // update barang acara
                $quantityItem = $getTransferItemQuantity - $itemDecrease;
                $this->eventItemModel->edit([
                    'quantity' => $quantityItem,
                    'items_not_returned' => $item['items_not_returned'],
                    'in_client_hands' => $item['in_client_hands'],
                    'notes' => $item['notes'],
                ], $existingItem->uuid);

                // update jumlah barang di gudang
                $existingWarehouse = $this->inventoryModel->getById($existingItem['item_id'])['data'];
                $quantityWarehouse = $existingWarehouse['quantity'] - $itemDecrease;
                $this->inventoryModel->edit([
                    'quantity' => $quantityWarehouse,
                ], $existingItem['item_id']);
            } else {
                return response()->failed('Item not found', 404);
            }
        }

        $checkPayment = $this->payment->getByOrder($uuidOrder)['data'];

        if ($checkPayment['status'] == 'not finished') {
            $update = $this->orderModel->edit([
                'status' => 'event_completed',
                'handover_of_leftovers' => $validatedData['handover_of_leftovers']
            ], $uuidOrder);
        } elseif ($checkPayment['status'] == 'completed') {
            $update = $this->orderModel->edit([
                'status' => 'event_finish',
                'handover_of_leftovers' => $validatedData['handover_of_leftovers']
            ], $uuidOrder);
        }

        // cek acara selanjutnya, lalu cek acara mana yang duluan dimulai,

        ActivityHelper::log('Order Done ' . $existingOrder['data']['order_name']);
        $data = $this->orderModel->getById($uuidOrder);
        return response()->success($data, 200);
        // kurang->update jumlah trasfer
    }

    public function eventEndUpdate($uuidOrder, Request $request)
    {
    }

    public function readPurchasePlan($uuidOrder)
    {
        $checkOrder = $this->orderModel->getById($uuidOrder);
        if (!$checkOrder['data']) {
            return response()->failed('Order not found', 404);
        }
        // ini dicek lagi aku lupa algonya
        //  karena dbnya berubah jadi ambil menu paket dari order package item dan order customisation
        //  di order package rel ada package id ambil dari sana aja nanti langsung ambil dari package item
        // jadi logicnya begini, pertama ambil menu id di order, lalu semua dan dikelompokan jadi satu dan di jumlah porsinya
        // lalu di loop unutk di cari resepnya dan dikalikan jumlahnya selesai
        $getOrderPackage = $this->orderPackageItem->getByOrder($uuidOrder);
        $getOrderCustom = $this->orderMenuModel->getByOrder($uuidOrder);

        $getOrderPackage = collect($getOrderPackage['data'])->map(function ($item) {
            return [
                'menu_id' => $item['menu_id'],
                'menu_name' => $item['menu']['name'],
                'portion' => $item['portion']
            ];
        });
        $getOrderCustom = collect(Arr::wrap($getOrderCustom['data']))->map(function ($item) {
            return [
                'menu_id' => $item['menu_id'],
                'menu_name' => $item['menu']['name'],
                'portion' => $item['portion']
            ];
        });
        $getMenu = $getOrderPackage->merge($getOrderCustom)->groupBy('menu_id')
            ->map(fn($items, $menuId) => [
                'menu_id' => $menuId,
                'menu_name' => $items->first()['menu_name'],
                'portion' => $items->sum('portion'),
            ])->values()->all();

        $dataPerMenu = [];
        $totalIngridients = [];

        foreach ($getMenu as $menu) {
            $recipe = $this->recipesModel->getAll($menu['menu_id'])['data'];
            $ingridient = [];
            if (count($recipe) > 0) {
                foreach ($recipe as $perIngridient) {
                    $quantity = ($menu['portion'] / $perIngridient['portion']) * $perIngridient['quantity'];
                    $ingridient[] = [
                        'name' => $perIngridient['name'],
                        'quantity' => $quantity,
                        'unit' => $perIngridient['unit'],
                    ];
                    $unit = $perIngridient['unit'];
                    $ingredientKey = $perIngridient['name'] . ' (' . $unit . ')';
                    if (isset($totalIngridients[$ingredientKey])) {
                        $totalIngridients[$ingredientKey]['quantity'] += $quantity;
                    } else {
                        $totalIngridients[$ingredientKey] = [
                            'name' => $perIngridient['name'],
                            'quantity' => $quantity,
                            'unit' => $unit,
                        ];
                    }
                }
            }
            $dataPerMenu[] = [
                'name' => $menu['menu_name'],
                'purchase' => $ingridient
            ];
        }

        ksort($totalIngridients);
        $totalIngridients = array_values($totalIngridients);
        $data = [
            'total' => $totalIngridients,
            'detail' => $dataPerMenu
        ];
        return response()->success($data, 200);
    }

    public function orderPrint($uuid, $type = null)
    {
        $order = $this->orderModel->getById($uuid)['data'];
        $orderPackage = $this->orderPackageModel->getAllOrder($uuid)['data'];
        $packageMenu = $this->orderPackageItem->getAllOrder($uuid)['data'];
        $customMenu = $this->orderMenuModel->getAllOrder($uuid)['data'];
//        return response()->success($packageMenu, 200);
        if (!$order) {
            return response()->failed('Order not found', 404);
        }
//        return response()->success($order, 200);
        $pdf = Pdf::loadView('order-print-layout', compact('order', 'orderPackage', 'packageMenu', 'customMenu', 'type'));

        return $pdf->download('pemesanan-' . $order->order_name . '.pdf');
    }

    public function purchasePlanPrint($uuidOrder)
    {
        $order = $this->orderModel->getById($uuidOrder)['data'];
        if (!$order) {
            return response()->failed('Order not found', 404);
        }
        // ini dicek lagi aku lupa algonya
        $getOrderPackage = $this->orderPackageItem->getByOrder($uuidOrder);
        $getOrderCustom = $this->orderMenuModel->getByOrder($uuidOrder);

        $getOrderPackage = collect($getOrderPackage['data'])->map(function ($item) {
            return [
                'menu_id' => $item['menu_id'],
                'menu_name' => $item['menu']['name'],
                'portion' => $item['portion']
            ];
        });
        $getOrderCustom = collect(Arr::wrap($getOrderCustom['data']))->map(function ($item) {
            return [
                'menu_id' => $item['menu_id'],
                'menu_name' => $item['menu']['name'],
                'portion' => $item['portion']
            ];
        });
        $getMenu = $getOrderPackage->merge($getOrderCustom)->groupBy('menu_id')
            ->map(fn($items, $menuId) => [
                'menu_id' => $menuId,
                'menu_name' => $items->first()['menu_name'],
                'portion' => $items->sum('portion'),
            ])->values()->all();

        $dataPerMenu = [];
        $totalIngridients = [];

        foreach ($getMenu as $menu) {
            $recipe = $this->recipesModel->getAll($menu['menu_id'])['data'];
            $ingridient = [];
            if (count($recipe) > 0) {
                foreach ($recipe as $perIngridient) {
                    $quantity = ($menu['portion'] / $perIngridient['portion']) * $perIngridient['quantity'];
                    $ingridient[] = [
                        'name' => $perIngridient['name'],
                        'quantity' => $quantity,
                        'unit' => $perIngridient['unit'],
                    ];
                    $unit = $perIngridient['unit'];
                    $ingredientKey = $perIngridient['name'] . ' (' . $unit . ')';
                    if (isset($totalIngridients[$ingredientKey])) {
                        $totalIngridients[$ingredientKey]['quantity'] += $quantity;
                    } else {
                        $totalIngridients[$ingredientKey] = [
                            'name' => $perIngridient['name'],
                            'quantity' => $quantity,
                            'unit' => $unit,
                        ];
                    }
                }
            }
            $dataPerMenu[] = [
                'name' => $menu['menu_name'],
                'purchase' => $ingridient
            ];
        }

        ksort($totalIngridients);
        $totalIngridients = array_values($totalIngridients);
        // return response()->success($totalIngridients, 200);
        $pdf = Pdf::loadView('purchaseplan-print-layout', compact('order', 'totalIngridients'));

        return $pdf->download('belanja-' . $order->order_name . '.pdf');
    }

    public function logisticPrint($uuidOrder)
    {
        $items = $this->eventItemModel->getAll($uuidOrder);
        if (!$items['data']) return response()->failed("Event logistic not found", 404);
        $order = $this->orderModel->getById($uuidOrder);
        if (!$order['data']) return response()->failed("Order not found", 404);
        $items = $items['data'];
        $order = $order['data'];
        $pdf = Pdf::loadView('eventInventory-print-layout', compact('order', 'items'));

        return $pdf->download('data barang-' . $order->order_name . '.pdf');
    }
}

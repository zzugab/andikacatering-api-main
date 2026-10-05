<?php

namespace App\Http\Controllers;

use App\Models\InventoryItemModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ActivityHelper;
use App\Models\EventLogisticModel;
use App\Models\EventTransferItemModel;
use App\Models\EventTransferModel;
use App\Models\OrdersCustomersModel;

class LogisticController extends Controller
{
    private $inventoryModel;
    private $transferModel;
    private $transferItemModel;
    private $orderModel;
    private $eventLogisticModel;

    public function __construct()
    {
        $this->inventoryModel = new InventoryItemModel();
        $this->transferModel = new EventTransferModel();
        $this->transferItemModel = new EventTransferItemModel();
        $this->orderModel = new OrdersCustomersModel();
        $this->eventLogisticModel = new EventLogisticModel();
    }

    public function indexInventory(Request $request)
    {
        $filter = [
            'event_date' => $request->event_date ?? now(),
        ];
        $dataInventory = $this->inventoryModel->getAll();
        if (!$dataInventory) return response()->failed($dataInventory, 404);

        $realtimeQuantities = [];
        foreach ($dataInventory["data"] as $item) {
            $quantityRealtime = $this->eventLogisticModel->getByDateAndItemId($filter, $item["uuid"]);

            $realtimeQuantities[] = [
                'uuid' => $item["uuid"],
                'item_name' => $item["name"],
                'quantity_initial' => $item["quantity"],
                'quantity_used' => intval($quantityRealtime["data"]),
                'quantity_remaining' => $item["quantity"] - $quantityRealtime["data"]
            ];
        }
        $data = [
            "status" => true,
            "data_inventory" => $dataInventory["data"],
            "quantity_realtime" => $realtimeQuantities

        ];
        return response()->success($data, 200);
    }

    public function storeInventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.name' => 'required',
            'items.*.quantity' => 'required',
            'items.*.status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }
        $data = [];
        foreach ($validator->validated()["items"] as $item) {
            $data[] = $this->inventoryModel->store($item)["data"];
        }
        ActivityHelper::log('Membuat Inventory Baru');
        return response()->success($data, 200);
    }

    public function showInventory($uuid)
    {
        $data = $this->inventoryModel->getById($uuid);
        if (!$data['data']) {
            return response()->failed('Inventory not found', 404);
        }

        return response()->success($data, 200);
    }

    public function updateInventory(Request $request, $uuid)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'quantity' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $checkExist = $this->inventoryModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Inventory not found', 404);
        }

        $update = $this->inventoryModel->edit($validator->validated(), $uuid);
        if ($update) {
            ActivityHelper::log('Mengubah Inventory');

            $data = $this->inventoryModel->getById($uuid);
            return response()->success($data, 200);
        }
        return response()->failed('Something error', 422);
    }

    public function destroyInventory($uuid)
    {
        $checkExist = $this->inventoryModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Inventory not found', 404);
        }

        $data = $this->inventoryModel->drop($uuid);
        if ($data['status']) {
            ActivityHelper::log('Menghapus Inventory');
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    //  membuat crud transfer barang
    public function indexTransfer(Request $request)
    {
        $filter = [
            'status' => $request->status ?? '',
            'order_id_from' => $request->order_id_from ?? '',
            'order_id_to' => $request->order_id_to ?? '',
        ];
        $data = $this->transferModel->getAll($filter);
        if (!$data) return response()->failed($data, 404);
        return response()->success($data, 200);
    }

    public function storeTransfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
            'order_id_from' => 'nullable',
            'order_id_to' => 'nullable',
            'note' => 'nullable',
            'items' => 'required|array',
            'items.*.item_id' => 'required',
            'items.*.quantity' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $validatedData = $validator->validated();
        if (isset($validatedData['order_id_from']) && $validatedData['order_id_from'] != '') {
            $checkExist = $this->orderModel->getById($validatedData['order_id_from']);
            if (!$checkExist['data']) {
                return response()->failed('Order not found', 404);
            }
        }

        if (isset($validatedData['order_id_to']) && $validatedData['order_id_to'] != '') {
            $checkExist = $this->orderModel->getById($validatedData['order_id_to']);
            if (!$checkExist['data']) {
                return response()->failed('Order not found', 404);
            }
        }

        $dataTransfer = [
            'status' => $validatedData['status'],
            'order_id_from' => $validatedData['order_id_from'] ?: null,
            'order_id_to' => $validatedData['order_id_to'] ?: null,
            'note' => $validatedData['note'],
        ];
        $storeResponse = $this->transferModel->store($dataTransfer);
        if (!$storeResponse['status']) {
            return response()->failed($storeResponse['error'], 500);
        }
        $data = $storeResponse['data'];

        $resultItem = [];
        foreach ($validatedData["items"] as $item) {
            $item['event_transfer_id'] = $data['uuid'];
            $checkExist = $this->inventoryModel->getById($item['item_id']);
            if (!$checkExist['data']) {
                return response()->failed('Item id not found', 404);
            }
            $storeItemResponse = $this->transferItemModel->store($item);
            if (!$storeItemResponse['status']) {
                return response()->failed($storeItemResponse['error'], 500);
            }
            $resultItem[] = $storeItemResponse['data'];
        }
        $data['items'] = $resultItem;

        ActivityHelper::log('Membuat Data Transfer Baru');
        return response()->success($data, 200);
    }

    public function showTransfer($uuid)
    {
        $data = $this->transferModel->getById($uuid);
        if (!$data['data']) return response()->failed($data, 404);
        return response()->success($data, 200);
    }

    public function updateTransfer(Request $request, $uuid)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required',
                'order_id_from' => 'nullable',
                'order_id_to' => 'nullable',
                'note' => 'nullable',
                'items' => 'required|array',
                'items.*.uuid' => 'nullable',
                'items.*.item_id' => 'required',
                'items.*.quantity' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->failed($validator->errors(), 400);
            }

            $checkExist = $this->transferModel->getById($uuid);
            if (!$checkExist['data']) return response()->failed("Transfer data not found", 404);

            $validatedData = $validator->validated();
            if (isset($validatedData['order_id_from'])) {
                $checkExist = $this->orderModel->getById($validatedData['order_id_from']);
                if (!$checkExist['data']) {
                    return response()->failed('Order not found', 404);
                }
            }

            if (isset($validatedData['order_id_to'])) {
                $checkExist = $this->orderModel->getById($validatedData['order_id_to']);
                if (!$checkExist['data']) {
                    return response()->failed('Order not found', 404);
                }
            }

            $dataTransfer = [
                'status' => $validatedData['status'],
                'order_id_from' => $validatedData['order_id_from'],
                'order_id_to' => $validatedData['order_id_to'],
                'note' => $validatedData['note'],
            ];
            $data = $this->transferModel->edit($dataTransfer, $uuid)["data"];
            if (!$data) return response()->failed('Update transfer data error', 500);

            // Mengambil UUID item dari request
            $itemUuidsFromRequest = array_column($validatedData['items'], 'uuid');

            // Mengambil semua item yang sudah ada di database untuk transfer ini
            $existingItems = $this->transferItemModel->getAllByTransferId($uuid)['data']->toArray();
            $existingItemUuids = array_column($existingItems, 'uuid');

            // Mengidentifikasi item yang harus dihapus
            $itemsToDelete = array_diff($existingItemUuids, $itemUuidsFromRequest);
            if (!empty($itemsToDelete)) {
                foreach ($itemsToDelete as $itemUuid) {
                    $this->transferItemModel->drop($itemUuid);
                }
            }

            // Proses update atau tambah item baru
            $resultItem = [];
            foreach ($validatedData["items"] as $item) {
                // Validasi keberadaan item di inventory berdasarkan item_id
                $checkExist = $this->inventoryModel->getById($item['item_id']);
                if (!$checkExist['data']) {
                    return response()->failed('Item id not found', 404);
                }

                // Jika UUID item ada, update; jika tidak, tambah item baru
                if (isset($item['uuid'])) {
                    $resultItem[] = $this->transferItemModel->edit(array_merge($item, [
                        'event_transfer_id' => $uuid
                    ]), $item['uuid'])['data'];
                } else {
                    $resultItem[] = $this->transferItemModel->store(array_merge($item, [
                        'event_transfer_id' => $uuid
                    ]))['data'];
                }
            }
            ActivityHelper::log('Mengubah Transfer');
            $data = $this->transferModel->getById($uuid);
            if (!$data['data']) return response()->failed($data, 404);
            return response()->success($data, 200);
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 400);
        }
    }

    public function destroyTransfer($uuid)
    {
        $checkExist = $this->transferModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Transfer data not found', 404);
        }

        $data = $this->transferModel->drop($uuid);
        $itemData = $this->transferItemModel->dropByTransfer($uuid);

        if ($data['status']) {
            ActivityHelper::log('Menghapus Inventory');
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    public function requestTransfer($uuidOrder, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.item_id' => 'required',
            'items.*.quantity' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $validatedData = $validator->validated();
        $checkExist = $this->orderModel->getById($uuidOrder);
        if (!$checkExist['data']) {
            return response()->failed('Order not found', 404);
        }

        $dataTransfer = [
            'status' => 'request warehouse',
            'order_id_to' => $uuidOrder,
        ];
        $response  = $this->transferModel->store($dataTransfer);
        if (empty($response["data"])) {
            return response()->failed('Input transfer data error', 500);
        }
        $data = $response["data"];
        $resultItem = [];
        foreach ($validatedData["items"] as $item) {
            $item['event_transfer_id'] = $data['uuid'];
            $checkExist = $this->inventoryModel->getById($item['item_id']);
            if (!$checkExist['data']) {
                return response()->failed('Item id not found', 404);
            }
            $resultItemData = $this->transferItemModel->store($item)['data'];
            if (!$resultItemData) return response()->failed('Input transfer data error', 500);
            $resultItem[] = $resultItemData;
        }
        $data['items'] = $resultItem;

        ActivityHelper::log('Request Transfer');
        return response()->success($data, 200);
    }
}

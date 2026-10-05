<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityHelper;
use App\Models\BankModel;
use App\Models\FinancialRecordModel;
use App\Models\Images;
use App\Models\OrdersCustomersModel;
use App\Models\PaymentInstallmentsModel;
use App\Models\PaymentsModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FinanceController extends Controller
{
    private $bankModel;
    private $paymentModel;
    private $installmentModel;
    private $image;
    private $financeRecordModel;
    private $orderModel;

    public function __construct()
    {
        $this->orderModel = new OrdersCustomersModel();
        $this->bankModel = new BankModel();
        $this->paymentModel = new PaymentsModel();
        $this->installmentModel = new PaymentInstallmentsModel();
        $this->image = new Images();
        $this->financeRecordModel = new FinancialRecordModel();
    }

    public function index(Request $request)
    {
        $filter = [
            'date_start' => $request->date_start ?? null,
            'date_end' => $request->date_end ?? null,
        ];
        $result = $this->paymentModel->getAll($filter);

        // tambah filter tanggal acara
        // total keseluruhan pembayaran, total yang sudah dibayar, total kurang bayar(total keseluruhan pembayaran-total yang sudah dibayar) (semua acara)

        if (!$result['status']) return response()->failed($result, 404);
        $array = collect($result['data']);
        $grandTotal = $array->reduce(function ($carry, $item) {
            $totalPayment = $item['amount'];
            $totalInstallment = collect($item['installment'])->sum('total_amount'); // Jumlahkan amount di installment
            $carry['total_payment'] += $totalPayment;
            $carry['total_income'] += $totalInstallment;
            return $carry;
        }, ['total_payment' => 0, 'total_income' => 0]);
        $grandTotal['total_gap'] = $grandTotal['total_income'] - $grandTotal['total_payment'];
        $data = [
            'status' => true,
            'data' => [
                'list_payment' => $array,
                'total_payment' => $grandTotal['total_payment'],
                'total_income' => $grandTotal['total_income'],
                'total_gap' => $grandTotal['total_gap'],
            ]
        ];
        return response()->success($data, 200);
    }

    public function show($uuid)
    {
        $data = $this->paymentModel->getById($uuid);
        if (!$data['data']) {
            return response()->failed('Payment not found', 404);
        }

        return response()->success($data, 200);
    }

    public function update($uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'nullable',
            'status' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $checkExist = $this->paymentModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Payment not found', 404);
        }

        $update = $this->paymentModel->edit($uuid, $validator->validated());
        ActivityHelper::log('Mengubah Pembayaran');
        if ($update) {
            $data = $this->paymentModel->getById($uuid);
            return response()->success($data, 200);
        }
        return response()->failed('Something error', 422);
    }

    public function destroy($uuid)
    {
        $checkExist = $this->paymentModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Payment not found', 404);
        }

        $data = $this->paymentModel->drop($uuid);
        ActivityHelper::log('Menghapus Pembayaran');

        if ($data['status']) {
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    public function indexInstallment($uuidPayment)
    {
        // total keseluruhan pembayaran, total yang sudah dibayar, total kurang bayar(total keseluruhan pembayaran-total yang sudah dibayar)
//        tambah info acara
        $resultPayment = $this->paymentModel->getById($uuidPayment);
        if (!$resultPayment['status']) return response()->failed($resultPayment, 404);
        unset($resultPayment['data']['installment']);

        $result = $this->installmentModel->getAllByPayment($uuidPayment);
        if (!$result['status']) return response()->failed($result, 404);

        $array = collect($result['data']);
        $totalIncome = $array->map(function ($item) {
            $imagePath = $item['image']['name'];
            $item['image']['link'] = url('/') . Storage::url('uploads/payment/' . $imagePath);
            return $item;
        })->reduce(function ($carry, $item) {
            return $carry + $item['amount'];
        }, 0);
        $totalGap = $totalIncome - $resultPayment['data']['amount'];

        $data = [
            'status' => true,
            'data' => [
                'payment' => $resultPayment['data'],
                'list_installment' => $array,
                'total_payment' => $resultPayment['data']['amount'],
                'total_income' => $totalIncome,
                'total_gap' => $totalGap,
            ]
        ];
        return response()->success($data, 200);
    }

    public function storeInstallment($uuidPayment, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'payment_bank_id' => 'required',
                'amount' => 'required',
                'payment_datelines' => 'required',
                'payment_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:30720',
            ]);

            if ($validator->fails()) {
                return response()->failed($validator->errors(), 400);
            }

            $validate = $validator->validated();

            $getPayment = $this->paymentModel->getById($uuidPayment);
            $getSumInstallment = $this->installmentModel->sumByPayment($uuidPayment);
            if (isset($getPayment["data"]["amount"]) && $getPayment["data"]["amount"] !== null) {
                $paymentRemaining = $getPayment["data"]["amount"] - ($getSumInstallment["data"] + $validate["amount"]);
                if ($paymentRemaining > 0) {
                    $paymentType = "payment installments";
                } else {
                    $paymentType = "final payment";
                }
            } else {
                $paymentType = "payment installments";
            }

            $imageData = $this->image->store($validate['payment_image'], 'payment')['data'];
            unset($validate['payment_image']);

            $data = $this->installmentModel->store(array_merge($validate, ['type' => $paymentType, 'payment_id' => $uuidPayment, 'payment_image_id' => $imageData['uuid']]));
            if ($data['status']) {
                if ($paymentType == "final payment") {
                    $updatePayment = $this->paymentModel->edit($uuidPayment, ['status' => 'completed']);
                }
                ActivityHelper::log('Menambahkan Cicilan Baru');
                $data = $this->installmentModel->getById($data['data']['uuid']);
                $imagePath = $data['data']['image']['name'];
                $imageUrl = url('/') . Storage::url('uploads/payment/' . $imagePath);
                $data['data']['image']['link'] = $imageUrl;
                return response()->success($data, 200);
            }
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 422);
        }
    }

    public function showInstallment($uuid)
    {
        $data = $this->installmentModel->getById($uuid);

        if (!$data['data']) return response()->failed($data, 404);
        $imagePath = $data['data']['image']['name'];
        $imageUrl = url('/') . Storage::url('uploads/payment/' . $imagePath);
        $data['data']['image']['link'] = $imageUrl;
        return response()->success($data, 200);
    }

    public function updateInstallment($uuid, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'payment_bank_id' => 'required',
                'amount' => 'required',
                'payment_datelines' => 'required',
                'payment_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:30720',
            ]);

            if ($validator->fails()) {
                return response()->failed($validator->errors(), 400);
            }

            $validatedData = $validator->validated();

            $installment = $this->installmentModel->getById($uuid);
            if (!$installment['data']) {
                return response()->failed('Cicilan tidak ditemukan', 404);
            }
            $paymentId = $installment['data']['payment_id'];

            $payment = $this->paymentModel->getById($paymentId);
            if (!$payment['data']) {
                return response()->failed('Pembayaran terkait tidak ditemukan', 404);
            }

            $originalInstallmentAmount = $installment['data']['amount'];
            $updateData = $validatedData;
            if (isset($validatedData['payment_image'])) {
                $imageData = $this->image->store($validatedData['payment_image'], 'payment')['data'];
                $updateData['payment_image_id'] = $imageData['uuid'];
                unset($updateData['payment_image']);
            }

            $this->installmentModel->edit($uuid, $updateData);
            $totalInstallments = $this->installmentModel->sumByPayment($paymentId)['data'];
            $totalInstallments = $totalInstallments - $originalInstallmentAmount + $validatedData['amount'];
            $remainingBalance = $payment['data']['amount'] - $totalInstallments;

            // Validasi cicilan terakhir (final payment)
            $lastInstallment = $this->installmentModel->getByInstallment($paymentId);
            if ($lastInstallment && $lastInstallment['data']['type'] === 'final payment') {
                if ($remainingBalance > 0) {
                    // Jika saldo masih ada, ubah status terakhir menjadi "payment installments"
                    $this->installmentModel->edit($lastInstallment['data']['uuid'], ['type' => 'payment installments']);
                }
            }

            if ($remainingBalance > 0) {
                // Jika masih ada saldo, ubah status menjadi "not finished"
                $this->paymentModel->edit($paymentId, ['status' => 'not finished']);
            } else {
                // Jika saldo kurang dari atau sama dengan 0, tandai sebagai "completed"
                $this->paymentModel->edit($paymentId, ['status' => 'completed']);
                if ($lastInstallment && $remainingBalance <= 0) {
                    // Pastikan cicilan terakhir tetap bertipe "final payment"
                    $this->installmentModel->edit($lastInstallment['data']['uuid'], ['type' => 'final payment']);
                }
            }

            ActivityHelper::log('Mengubah Cicilan');
            $data = $this->installmentModel->getById($uuid);
            $imagePath = $data['data']['image']['name'];
            $imageUrl = url('/') . Storage::url('uploads/payment/' . $imagePath);
            $data['data']['image']['link'] = $imageUrl;

            return response()->success($data, 200);
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 422);
        }
    }


    // public function destroyInstallment($uuid)
    // {
    //     $checkExist = $this->installmentModel->getById($uuid);
    //     if (!$checkExist['data']) {
    //         return response()->failed('Installment not found', 404);
    //     }

    //     $data = $this->installmentModel->drop($uuid);
    //     ActivityHelper::log('Menghapus Cicilan');

    //     if ($data['status']) {
    //         return response()->success('Delete success', 200);
    //     }
    //     return response()->failed('Something error', 422);
    // }

    public function destroyInstallment($uuid)
    {
        try {
            // Cek apakah installment dengan UUID ini ada
            $checkExist = $this->installmentModel->getById($uuid);
            if (!$checkExist['data']) {
                return response()->failed('Installment not found', 404);
            }

            $installment = $checkExist['data'];
            $paymentId = $installment['payment_id'];

            // Hapus installment
            $data = $this->installmentModel->drop($uuid);

            if ($data['status']) {
                ActivityHelper::log('Menghapus Cicilan');

                // Rekalkulasi total installment setelah penghapusan
                $totalInstallments = $this->installmentModel->sumByPayment($paymentId)['data'];

                // Ambil detail pembayaran
                $payment = $this->paymentModel->getById($paymentId);
                if (!$payment['data']) {
                    return response()->failed('Associated payment not found', 404);
                }

                $remainingBalance = $payment['data']['amount'] - $totalInstallments;

                // Validasi cicilan terakhir
                $lastInstallment = $this->installmentModel->getByInstallment($paymentId);
                if ($lastInstallment && $lastInstallment['data']['type'] === 'final payment') {
                    if ($remainingBalance > 0) {
                        // Jika masih ada saldo, ubah cicilan terakhir menjadi "payment installments"
                        $this->installmentModel->edit($lastInstallment['data']['uuid'], ['type' => 'payment installments']);
                    }
                }

                // Ubah status pembayaran berdasarkan sisa saldo
                if ($remainingBalance > 0) {
                    $this->paymentModel->edit($paymentId, ['status' => 'not finished']);
                } else {
                    $this->paymentModel->edit($paymentId, ['status' => 'completed']);
                    if ($lastInstallment && $remainingBalance <= 0) {
                        // Pastikan cicilan terakhir tetap bertipe "final payment"
                        $this->installmentModel->edit($lastInstallment['data']['uuid'], ['type' => 'final payment']);
                    }
                }

                return response()->success('Delete success', 200);
            }

            return response()->failed('Something went wrong', 422);
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 422);
        }
    }


    public function indexBank()
    {
        $data = $this->bankModel->getAll();

        if (!$data['data']) return response()->failed($data, 404);
        return response()->success($data, 200);
    }

    public function storeBank(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'account_number' => 'required',
            'account_owner' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }
        $data = $this->bankModel->store($validator->validated());
        ActivityHelper::log('Membuat Bank Baru');

        return response()->success($data, 200);
    }

    public function showBank($uuid)
    {
        $data = $this->bankModel->getById($uuid);

        if (!$data['data']) return response()->failed($data, 404);
        return response()->success($data, 200);
    }

    public function updateBank($uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'account_number' => 'required',
            'account_owner' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $checkExist = $this->bankModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Bank not found', 404);
        }

        $update = $this->bankModel->edit($validator->validated(), $uuid);
        ActivityHelper::log('Mengubah Bank');
        if ($update) {
            $data = $this->bankModel->getById($uuid);
            return response()->success($data, 200);
        }
        return response()->failed('Something error', 422);
    }

    public function destroyBank($uuid)
    {
        $checkExist = $this->bankModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Bank not found', 404);
        }

        $data = $this->bankModel->drop($uuid);
        ActivityHelper::log('Menghapus Bank');

        if ($data['status']) {
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    // global financial record (get semua data (income dari installment dan outcome dari financial record))
    public function indexFinancialRecord(Request $request)
    {
        $filter = [
            'date_start' => $request->date_start ?? null,
            'date_end' => $request->date_end ?? null,
            'type' => $request->type ?? null,
        ];

        $dataInstallment = $this->installmentModel->getAll($filter);
        $totalInstallmentTotal = $this->installmentModel->totalInstallment($filter);
        $dataRecord = $this->financeRecordModel->getAll($filter);
        $totalRecordTotal = $this->financeRecordModel->totalRecord($filter);

        if (!empty($filter['type']) && $filter['type'] == "event") {
            $dataIncome = collect($dataInstallment['data'])->groupBy(function ($item) {
                return $item['payment']['order_id'];
            })->map(function ($items, $orderId) {
                $firstItem = $items->first();
                return [
                    'order_id' => $orderId,
                    'payment_id' => $firstItem['payment_id'],
                    'payment_amount' => $firstItem['payment']['amount'],
                    'total_amount' => $items->sum('amount'),
                    'event_date' => $firstItem['payment']['order']['event_date'],
                    'customer_name' => $firstItem['payment']['order']['customer']['name']
                ];
            })->values()->toArray();

            $dataOutcome = collect($dataRecord['data'])->groupBy(function ($item) {
                return $item['order']['uuid'];
            })->map(function ($items, $orderId) {
                $firstItem = $items->first();
                return [
                    'order_id' => $orderId,
                    'payment_amount' => $firstItem['order']['payment']['amount'],
                    'total_amount' => $items->sum('amount'),
                    'event_date' => $firstItem['order']['event_date'],
                    'customer_name' => $firstItem['order']['customer']['name']
                ];
            })->values()->toArray();
        } else {
            $dataIncome = $dataInstallment['data'];
            $dataOutcome = $dataRecord['data'];
        }

        if ($dataInstallment['status'] && $totalInstallmentTotal['status'] && $dataRecord['status'] && $totalRecordTotal['status']) {
            $data = [
                "status" => true,
                'data' => [
                    'dataIncome' => $dataIncome,
                    'totalIncome' => $totalInstallmentTotal['data'],
                    'dataOutcome' => $dataOutcome,
                    'totalOutcome' => $totalRecordTotal['data'],
                ]
            ];
            return response()->success($data, 200);
        } else {
            return response()->failed("Something error", 400);
        }
    }

    public function showRecordIncome($uuid)
    {
        $data = $this->installmentModel->getById($uuid);

        if (!$data['data']) return response()->failed($data, 404);
        $imagePath = $data['data']['image']['name'];
        $imageUrl = url('/') . Storage::url('uploads/payment/' . $imagePath);
        $data['data']['image']['link'] = $imageUrl;
        return response()->success($data, 200);
    }

    public function showRecordOutcome($uuid)
    {
        $data = $this->financeRecordModel->getById($uuid);
        if (!$data['data']) return response()->failed($data, 404);
        $imagePath = $data['data']['image']['name'];
        $imageUrl = url('/') . Storage::url('uploads/financial_record/' . $imagePath);
        $data['data']['image']['link'] = $imageUrl;
        return response()->success($data, 200);
    }

    public function indexFinancialRecordSA(Request $request)
    {
        $filter = [
            'date_start' => $request->date_start ?? null,
            'date_end' => $request->date_end ?? null,
            'type' => 'event'
        ];

        $totalInstallmentTotal = $this->installmentModel->totalInstallment($filter);
        $totalRecordTotal = $this->financeRecordModel->totalRecord($filter);
        $orderList = $this->orderModel->getFinance($filter);

        $orderList = collect($orderList['data'])->map(function ($order) {
            $installmentTotal =
                $order->payment && isset($order->payment['installment'])
                    ? collect($order->payment['installment'])->sum('amount')
                    : 0;
            $order->total_payment = $order->payment->amount ?? 0;
            $order->total_paid = $installmentTotal ?? 0;
            $order->remaining_funds = ($order->payment->amount ?? 0) - ($installmentTotal ?? 0);

            return $order;
        });

        if ($orderList && $totalInstallmentTotal['status'] && $totalRecordTotal['status']) {
            $data = [
                "status" => true,
                'data' => [
                    'total_income' => $totalInstallmentTotal['data'],
                    'total_expenses' => $totalRecordTotal['data'],
                    'profit_remaining_funds' => $totalInstallmentTotal['data'] - $totalRecordTotal['data'],
                    'data_order' => $orderList
                ]
            ];
            return response()->success($data, 200);
        } else {
            return response()->failed("Something error", 400);
        }
    }

    public function showRecordSA($uuid)
    {
        $orderDetail = $this->orderModel->getById($uuid);
        if (!$orderDetail['status']) return response()->failed($orderDetail, 404);
        $orderDetail = $orderDetail['data'];

        $installmetDetail = $this->installmentModel->getByOrder($uuid);
        if (!$installmetDetail['status']) return response()->failed($installmetDetail, 404);

        $installmetDetail = collect($installmetDetail['data'])->map(function ($item) {
            return [
                'payment_proof' => url('/') . Storage::url('uploads/payment/' . $item['image']['name']),
                'payment_amount' => $item['amount'],
                'payment_date' => $item['date'],
                'bank_name' => $item['bank']['name']
            ];
        });
        $paymentTotal = collect($installmetDetail)->sum('payment_amount');

        $recordDetail = $this->financeRecordModel->getAllByOrder($uuid);
        if (!$recordDetail['status']) return response()->failed($recordDetail, 404);

        $recordDetail = collect($recordDetail['data'])->map(function ($item) {
            return [
                'receipt_proof' => url('/') . Storage::url('uploads/financial_record/' . $item['image']['name']),
                'shopping_date' => $item['date'],
                'total_spent' => $item['amount'],
                'description' => $item['description']
            ];
        });
        $recordTotal = collect($recordDetail)->sum('total_spent');

        $data = [
            'customer' => [
                "name" => $orderDetail['customer']['name'],
                "phone_1" => $orderDetail['customer']['phone_number_1'],
                "phone_2" => $orderDetail['customer']['phone_number_2'],
                "location_event" => $orderDetail['location'],
                "event_date" => $orderDetail['event_date']
            ],
            'income' => [
                'total_payment' => $orderDetail['payment']['amount'],
                'total_paid' => $paymentTotal,
                'remaining_funds' => $orderDetail['payment']['amount'] - $paymentTotal,
                'payment' => $installmetDetail
            ],
            'outcome' => [
                'customer_funds' => $paymentTotal,
                'total_expenditure' => $recordTotal,
                'remaining_funds' => $paymentTotal - $recordTotal,
                'payment' => $recordDetail
            ]
        ];

        return response()->success($data, 200);
    }


    public function indexRecord($uuidOrder)
    {
        $dataIncome = $this->installmentModel->getByOrder($uuidOrder);
        foreach ($dataIncome['data'] as $item) {
            $imagePath = $item['image']['name'];
            $imageUrl = url('/') . Storage::url('uploads/financial_record/' . $imagePath);
            $item['image']['link'] = $imageUrl;
        }
        $totalIncome = $this->installmentModel->totalByOrder($uuidOrder);
        $dataOutcome = $this->financeRecordModel->getAllByOrder($uuidOrder);
        if (!$dataOutcome['data']) return response()->failed($dataOutcome, 404);
        foreach ($dataOutcome['data'] as $item) {
            $imagePath = $item['image']['name'];
            $imageUrl = url('/') . Storage::url('uploads/financial_record/' . $imagePath);
            $item['image']['link'] = $imageUrl;
        }
        $totalOutcome = $this->financeRecordModel->totalByOrder($uuidOrder);
        if ($dataIncome['status'] && $totalIncome['status'] && $dataOutcome['status'] && $totalOutcome['status']) {
            $data = [
                'status' => true,
                'data' => [
                    'dataIncome' => $dataIncome['data'],
                    'totalIncome' => $totalIncome['data'],
                    'dataOutcome' => $dataOutcome['data'],
                    'totalOutcome' => $totalOutcome['data']
                ]
            ];
            return response()->success($data, 200);
        } else {
            return response()->failed("Internal server error", 500);
        }
    }

    public function storeRecord($uuidOrder, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required',
                'amount' => 'required',
                'description' => 'required',
                'record_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:30720',
            ]);

            if ($validator->fails()) {
                return response()->failed($validator->errors(), 400);
            }

            $validate = $validator->validated();

            $checkOrder = $this->orderModel->getById($uuidOrder);
            if (!$checkOrder['data']) {
                return response()->failed('Order not found', 404);
            }

            $imageData = $this->image->store($validate['record_image'], 'financial_record')['data'];
            unset($validate['record_image']);

            $data = $this->financeRecordModel->store(array_merge($validate, ['order_id' => $uuidOrder, 'record_image_id' => $imageData['uuid']]));
            ActivityHelper::log('Membuat Catatan Keuangan Baru');

            $data = $this->financeRecordModel->getById($data['data']['uuid']);
            $imagePath = $data['data']['image']['name'];
            $imageUrl = url('/') . Storage::url('uploads/financial_record/' . $imagePath);
            $data['data']['image']['link'] = $imageUrl;
            return response()->success($data, 200);
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 422);
        }
    }

    public function storeRefund($uuidOrder, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required',
                'amount' => 'required',
                'description' => 'required',
                'record_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:30720',
            ]);

            if ($validator->fails()) {
                return response()->failed($validator->errors(), 400);
            }

            $validate = $validator->validated();


            $checkOrder = $this->orderModel->getById($uuidOrder);
            if (!$checkOrder['data']) {
                return response()->failed('Order not found', 404);
            }

            $imageData = $this->image->store($validate['record_image'], 'financial_record')['data'];
            unset($validate['record_image']);

            $data = $this->financeRecordModel->store(array_merge($validate, ['order_id' => $uuidOrder, 'record_image_id' => $imageData['uuid']]));
            if ($data['status']) {
                $getPayment = $this->orderModel->getById($uuidOrder)['data']['payment']['uuid'];
                $updatePayment = $this->paymentModel->edit($getPayment, ['status' => 'refund']);
                ActivityHelper::log('Refund pembayaran');
                $data = $this->financeRecordModel->getById($data['data']['uuid']);
                $imagePath = $data['data']['image']['name'];
                $imageUrl = url('/') . Storage::url('uploads/payment/' . $imagePath);
                $data['data']['image']['link'] = $imageUrl;
                return response()->success($data, 200);
            }
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 422);
        }
    }

    public function showRecord($uuid)
    {
        $data = $this->financeRecordModel->getById($uuid);

        if (!$data['data']) return response()->failed($data, 404);
        $imagePath = $data['data']['image']['name'];
        $imageUrl = url('/') . Storage::url('uploads/financial_record/' . $imagePath);
        $data['data']['image']['link'] = $imageUrl;
        return response()->success($data, 200);
    }

    public function updateRecord($uuid, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required',
                'amount' => 'required',
                'description' => 'required',
                'record_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:30720',
            ]);
            if ($validator->fails()) {
                return response()->failed($validator->errors(), 400);
            }

            $validatedData = $validator->validated();

            // Retrieve the existing record by UUID
            $checkRecord = $this->financeRecordModel->getById($uuid); // Assume getByUUID is a method that retrieves the installment
            if (!$checkRecord['data']) {
                return response()->failed('Record not found', 404);
            }

            $updateData = $validatedData;
            if (isset($validatedData['record_image'])) {
                $imageData = $this->image->store($validatedData['record_image'], 'financial_record')['data'];
                $updateData['record_image_id'] = $imageData['uuid'];
                unset($updateData['record_image']);
            }

            $updatedRecord = $this->financeRecordModel->edit($uuid, $updateData);
            ActivityHelper::log('Mengubah Catatan Keuangan');
            $data = $this->financeRecordModel->getById($uuid);
            $imagePath = $data['data']['image']['name'];
            $imageUrl = url('/') . Storage::url('uploads/financial_record/' . $imagePath);
            $data['data']['image']['link'] = $imageUrl;
            return response()->success($data, 200);
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 422);
        }
    }

    public function destroyRecord($uuid)
    {
        $checkExist = $this->financeRecordModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Record not found', 404);
        }

        $data = $this->financeRecordModel->drop($uuid);
        ActivityHelper::log('Menghapus Catatan Keuangan');

        if ($data['status']) {
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    public function invoicePrint($uuidPayment)
    {
        $resultPayment = $this->paymentModel->getById($uuidPayment)['data'];
        if (!$resultPayment['status']) return response()->failed($resultPayment, 404);
//        return $resultPayment;

        $arrPaket = collect($resultPayment['order']['orderPackage'])->map(function ($item) {
            return [
                "name" => $item['package']['name'],
                "portion" => $item['portion'],
                "price" => $item['package']['price'],
            ];
        });

        $arrMenu = collect($resultPayment['order']['orderCustom'])->map(function ($item) {
            return [
                "name" => $item['menu']['name'],
                "portion" => $item['portion'],
                "price" => $item['menu']['price'],
            ];
        });
        $resultMenu = $arrPaket->merge($arrMenu)->values();

        $pdf = Pdf::loadView('invoice-print-layout', compact('resultPayment', 'resultMenu'));

        return $pdf->download('invoice-' . $resultPayment->order->order_name . '.pdf');
    }

    public function installmentDetailPrint($uuid)
    {
        $installment = $this->installmentModel->getPrintDataById($uuid);
        if (!$installment['data']) {
            return response()->failed('Record not found', 404);
        }
        $installment = $installment['data'];
        $getAllInstallment = $this->installmentModel->getAllByPayment($installment['payment_id']);
        $remainingPayment = $installment['payment']['amount'];
        foreach ($getAllInstallment['data'] ?? [] as $dataInstallment) {
            if ($dataInstallment['payment_datelines'] <= $installment['payment_datelines']) {
                $remainingPayment -= $dataInstallment['amount'];
            }
        }
//        return $installment;
        $pdf = Pdf::loadView('payment-print-layout', compact('installment', 'remainingPayment'));

        return $pdf->download('pembayaran-' . $installment->payment->order->order_name . '.pdf');
    }

    public function indexFinanceSA()
    {
    }
}

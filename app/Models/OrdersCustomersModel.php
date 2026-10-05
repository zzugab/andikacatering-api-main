<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Throwable;


class OrdersCustomersModel extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'order_name',
        'customer_id',
        'location',
        'event_date',
        'event_time',
        'portion',
        'note',
        'status',
        'field_coordinator_id',
        'handover_of_leftovers'
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'orders_customers';

    protected static function booted()
    {
        static::deleting(function ($item) {
            if (!$item->isForceDeleting()) {
                $item->financialRecord()->get()->each->delete();
                $item->logistic()->get()->each->delete();
                $item->orderPackage()->get()->each->delete();
                $item->orderCustom()->get()->each->delete();
                $item->orderFrom()->get()->each->delete();

                $detail = $item->detail()->first();
                if ($detail) {
                    $detail->delete();
                }
                $payment = $item->payment()->first();
                if ($payment) {
                    $payment->delete();
                }
            } else {
                $item->financialRecord()->withTrashed()->get()->each->forceDelete();
                $item->logistic()->withTrashed()->get()->each->forceDelete();
                $item->orderPackage()->withTrashed()->get()->each->forceDelete();
                $item->orderCustom()->withTrashed()->get()->each->forceDelete();
                $item->orderFrom()->withTrashed()->get()->each->forceDelete();

                $detail = $item->detail()->withTrashed()->first();
                if ($detail) {
                    $detail->forceDelete();
                }
                $payment = $item->payment()->withTrashed()->first();
                if ($payment) {
                    $payment->forceDelete();
                }
            }
        });

        static::restoring(function ($item) {
            $item->financialRecord()->onlyTrashed()->get()->each->restore();
            $item->logistic()->onlyTrashed()->get()->each->restore();
            $item->orderPackage()->onlyTrashed()->get()->each->restore();
            $item->orderCustom()->onlyTrashed()->get()->each->restore();
            $item->orderFrom()->onlyTrashed()->get()->each->restore();

            $detail = $item->detail()->onlyTrashed()->first();
            if ($detail) {
                $detail->restore();
            }
            $payment = $item->payment()->onlyTrashed()->first();
            if ($payment) {
                $payment->restore();
            }
        });
    }

    public function financialRecord()
    {
        return $this->hasMany(FinancialRecordModel::class, 'order_id', 'uuid');
    }

    public function logistic()
    {
        return $this->hasMany(EventLogisticModel::class, 'order_id', 'uuid');
    }

    public function orderPackage()
    {
        return $this->hasMany(OrderPackageModel::class, 'order_id', 'uuid');
    }

    public function orderCustom()
    {
        return $this->hasMany(OrderCustomizationModel::class, 'order_id', 'uuid');
    }

    public function orderFrom()
    {
        return $this->hasMany(EventTransferModel::class, 'order_id_from', 'uuid');
    }

    public function detail()
    {
        return $this->hasOne(OrderDetailModel::class, 'order_id', 'uuid');
    }

    public function payment()
    {
        return $this->hasOne(PaymentsModel::class, 'order_id', 'uuid');
    }

    public function customer()
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id', 'uuid');
    }

    public function fieldCoordinator()
    {
        return $this->belongsTo(UserModel::class, 'field_coordinator_id', 'uuid');
    }

    public function orderTo()
    {
        return $this->hasMany(EventTransferModel::class, 'order_id_to', 'uuid');
    }

    public function getAll(array $filter)
    {
        $result = $this->query();

        if (!empty($filter['order_name'])) {
            $result->where('order_name', 'like', '%' . $filter['order_name'] . '%');
        }

        if (!empty($filter['customer_id'])) {
            $result->where('customer_id', $filter['customer_id']);
        }

        if (!empty($filter['customer_name'])) {
            $result->whereHas('customer', function ($query) use ($filter) {
                $query->where('name', 'like', '%' . $filter['customer_name'] . '%');
            });
        }

        if (!empty($filter['field_coordinator_id'])) {
            $result->where('field_coordinator_id', $filter['field_coordinator_id']);
        }

        if (!empty($filter['field_coordinator_name'])) {
            $result->whereHas('field_coordinator', function ($query) use ($filter) {
                $query->where('name', 'like', '%' . $filter['field_coordinator_name'] . '%');
            });
        }

        if (!empty($filter['location'])) {
            $result->where('location', 'like', '%' . $filter['location'] . '%');
        }

        if (!empty($filter['event_date_start'])) {
            $result->where('event_date', '>=', $filter['event_date_start']);
        }

        if (!empty($filter['event_date_end'])) {
            $result->where('event_date', '<=', $filter['event_date_end']);
        }

        if (!empty($filter['event_time_start'])) {
            $result->where('event_time', '>=', $filter['event_time_start']);
        }

        if (!empty($filter['event_time_end'])) {
            $result->where('event_time', '<=', $filter['event_time_end']);
        }

        if (!empty($filter['status'])) {
            $result->where('status', $filter['status']);
        }

        if (!empty($filter['year'])) {
            $result->whereYear('event_date', $filter['year']);
        }

        if (!empty($filter['month'])) {
            $result->whereMonth('event_date', $filter['month']);
        }
        try {
            return [
                'status' => true,
                'data' => $result->with('customer', 'payment')
                    ->selectRaw(
                        "*,
                        CASE
                            WHEN event_date >= ?::date
                                AND status IN ('booking', 'confirmed', 'preparing', 'ready_for_delivery')
                            THEN event_date - ?::date
                            ELSE NULL
                        END AS days_remaining",
                        [
                            Carbon::now()->toDateString(),
                            Carbon::now()->toDateString()
                        ]
                    )
                    ->get()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function store(array $payload)
    {
        try {
            $today = date('Ymd');
            $orderCountToday = $this->whereDate('created_at', today())->count() + 1;
            $orderId = 'ORD-' . $today . '-' . str_pad($orderCountToday, 3, '0', STR_PAD_LEFT);
            $payload['order_name'] = $orderId;

            return [
                'status' => true,
                'data' => $this->firstOrCreate($payload)
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getById(string $id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->with('customer', 'fieldCoordinator', 'detail', 'payment')
                    ->selectRaw(
                        "*,
                        CASE
                            WHEN event_date >= ?::date
                                AND status IN ('booking', 'confirmed', 'preparing', 'ready_for_delivery')
                            THEN event_date - ?::date
                            ELSE NULL
                        END AS days_remaining",
                        [
                            Carbon::now()->toDateString(),
                            Carbon::now()->toDateString()
                        ]
                    )
                    ->where('uuid', $id)->first()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function edit(array $payload, string $id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->where('uuid', $id)->first()->update($payload)
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function drop(string $id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->where('uuid', $id)->first()->delete()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function dropForce(string $id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->where('uuid', $id)->first()->forceDelete()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getFinance(array $filter)
    {
        $result = $this->query();

        if (!empty($filter['date_start'])) {
            $result->where('event_date', '>=', $filter['date_start']);
        }

        if (!empty($filter['date_end'])) {
            $result->where('event_date', '<=', $filter['date_end']);
        }
        $data = $result->select('uuid', 'customer_id', 'order_name', 'location', 'event_date', 'event_time')
            ->with([
                'customer:uuid,name',
                'payment:uuid,order_id,amount',
                'payment.installment:uuid,payment_id,amount',
            ])
            ->get();

        try {
            return [
                'status' => true,
                'data' => $data
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }
}

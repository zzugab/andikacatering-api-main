<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Throwable;

class PaymentInstallmentsModel extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'payment_id',
        'payment_bank_id',
        'type',
        'amount',
        'payment_datelines',
        'payment_image_id',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'payment_installments';

    protected static function booted()
    {
        static::deleting(function ($item) {
            if (!$item->isForceDeleting()) {
                $image = $item->image()->first();
                if ($image) {
                    $image->delete();
                }
            } else {
                $image = $item->image()->withTrashed()->first();
                if ($image) {
                    $image->forceDelete();
                }
            }
        });

        static::restoring(function ($item) {
            $image = $item->image()->onlyTrashed()->first();
            if ($image) {
                $image->restore();
            }
        });
    }

    public function image()
    {
        return $this->belongsTo(Images::class, 'payment_image_id', 'uuid');
    }

    public function payment()
    {
        return $this->belongsTo(PaymentsModel::class, 'payment_id', 'uuid');
    }

    public function bank()
    {
        return $this->belongsTo(BankModel::class, 'payment_bank_id', 'uuid');
    }


    public function getAll(array $filter)
    {
        try {
            $query = $this->query()->addSelect('uuid', 'amount', 'payment_datelines as date', 'payment_id')->whereHas('payment', function ($query) {
                $query->whereNull('deleted_at');
            });

            if (!empty($filter['type']) && $filter['type'] == "event") {
                if (!empty($filter['date_start'])) {
                    $query->whereHas('payment.order', function ($q) use ($filter) {
                        $q->where('event_date', '>=', $filter['date_start']);
                    });
                }

                if (!empty($filter['date_end'])) {
                    $query->whereHas('payment.order', function ($q) use ($filter) {
                        $q->where('event_date', '<=', $filter['date_end']);
                    });
                }
            } else {
                if (!empty($filter['date_start'])) {
                    $query->where('payment_datelines', '>=', $filter['date_start']);
                }

                if (!empty($filter['date_end'])) {
                    $query->where('payment_datelines', '<=', $filter['date_end']);
                }
            }

            return [
                'status' => true,
                'data' => $query->with(
                    'payment:uuid,order_id,amount',
                    'payment.order:uuid,event_date,customer_id',
                    'payment.order.customer:uuid,name'
                )->get()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function totalInstallment(array $filter)
    {
        try {
            $query = $this->query()->whereHas('payment', function ($query) {
                $query->whereNull('deleted_at');
            });

            if (!empty($filter['type']) && $filter['type'] == "event") {
                if (!empty($filter['date_start'])) {
                    $query->whereHas('payment.order', function ($q) use ($filter) {
                        $q->where('event_date', '>=', $filter['date_start']);
                    });
                }

                if (!empty($filter['date_end'])) {
                    $query->whereHas('payment.order', function ($q) use ($filter) {
                        $q->where('event_date', '<=', $filter['date_end']);
                    });
                }
            } else {
                if (!empty($filter['date_start'])) {
                    $query->where('payment_datelines', '>=', $filter['date_start']);
                }

                if (!empty($filter['date_end'])) {
                    $query->where('payment_datelines', '<=', $filter['date_end']);
                }
            }

            return [
                'status' => true,
                'data' => $query->sum('amount')
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function totalByOrder(string $orderId)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->whereHas('payment', function ($query) use ($orderId) {
                    $query->where('order_id', $orderId)->whereNull('deleted_at');
                })->sum('amount')
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getAllByPayment($id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->where('payment_id', $id)->with('image', 'bank:uuid,name')->orderBy('payment_datelines', 'asc')->get()
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
                'data' => $this->query()->where('uuid', $id)->with('image')->first()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getPrintDataById(string $id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->where('uuid', $id)->whereHas('payment', function ($query) {
                    $query->whereNull('deleted_at');
                })->with(['payment.order.customer', 'bank'])->first()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getByInstallment(string $id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->where('payment_id', $id)->orderBy('payment_datelines', 'desc')->first()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getByOrder(string $orderId)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->select(
                    "uuid",
                    "payment_id",
                    "payment_bank_id",
                    "payment_image_id",
                    "type",
                    "amount",
                    "payment_datelines  as date",
                )
                    ->whereHas('payment', function ($query) use ($orderId) {
                        $query->where('order_id', $orderId)->whereNull('deleted_at');
                    })
                    ->with('image', 'bank')->get()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function sumByPayment(string $paymentId)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->where('payment_id', $paymentId)->whereHas('payment', function ($query) {
                    $query->whereNull('deleted_at');
                })->sum('amount')
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function edit(string $id, array $payload)
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
}

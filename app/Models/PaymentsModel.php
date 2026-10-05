<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Throwable;

class PaymentsModel extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'order_id',
        'amount',
        'status'
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    protected $table = 'payments';

    protected static function booted()
    {
        static::deleting(function ($item) {
            if (!$item->isForceDeleting()) {
                $item->installment()->get()->each->delete();
                $item->paymentDetails()->get()->each->delete();
            } else {
                $item->installment()->withTrashed()->get()->each->forceDelete();
                $item->paymentDetails()->withTrashed()->get()->each->forceDelete();
            }
        });

        static::restoring(function ($item) {
            $item->installment()->onlyTrashed()->get()->each->restore();
            $item->paymentDetails()->onlyTrashed()->get()->each->restore();
        });
    }

    public function installment()
    {
        return $this->hasMany(PaymentInstallmentsModel::class, 'payment_id', 'uuid');
    }

    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetailModel::class, 'payment_id', 'uuid');
    }

    public function order()
    {
        return $this->belongsTo(OrdersCustomersModel::class, 'order_id', 'uuid');
    }

    public function getAll(array $filter)
    {
        try {
            $query = $this->query();

            if (!empty($filter['date_start'])) {
                $query->whereHas('order', function ($q) use ($filter) {
                    $q->where('event_date', '>=', $filter['date_start']);
                });
            }

            if (!empty($filter['date_end'])) {
                $query->whereHas('order', function ($q) use ($filter) {
                    $q->where('event_date', '<=', $filter['date_end']);
                });
            }

            $query->with(['installment' => function ($q) {
                $q->selectRaw('payment_id, SUM(amount) as total_amount')
                    ->groupBy('payment_id');
            }]);

            return [
                'status' => true,
                'data' => $query->with("order:uuid,order_name,event_date,customer_id", "order.customer")->get()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getDPFromPayment(array $filter)
    {
        try {
            $result = $this->query()->where('status', 'not finished')
                ->whereHas('order', function ($query) use ($filter) {
                    $query->whereBetween('event_date', [$filter['event_date_start'], $filter['event_date_end']]);
                })
                ->with(['order', 'installment'])
                ->get();

            return [
                'status' => true,
                'data' => $result
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
                'data' => $this->query()->where('uuid', $id)->with('installment', 'order:uuid,order_name,location,event_date,customer_id', 'order.customer', 'order.orderPackage.package', 'order.orderCustom.menu')->first()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getByOrder(string $order_id)
    {

        try {
            return [
                'status' => true,
                'data' => $this->query()->where('order_id', $order_id)->with('paymentDetails')->first()
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

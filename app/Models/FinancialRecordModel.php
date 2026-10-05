<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Throwable;

class FinancialRecordModel extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'order_id',
        'date',
        'amount',
        'description',
        'record_image_id',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'financial_records';

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
        return $this->belongsTo(Images::class, 'record_image_id', 'uuid');
    }

    public function order()
    {
        return $this->belongsTo(OrdersCustomersModel::class, 'order_id', 'uuid');
    }

    public function getAll(array $filter)
    {
        try {
            $query = $this->query()->select('uuid', 'amount', 'date', 'order_id');

            if (!empty($filter['type']) && $filter['type'] == "event") {
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
            } else {
                if (!empty($filter['date_start'])) {
                    $query->where('date', '>=', $filter['date_start']);
                }

                if (!empty($filter['date_end'])) {
                    $query->where('date', '<=', $filter['date_end']);
                }
            }

            return [
                'status' => true,
                'data' => $query->with(
                    'order:uuid,event_date,customer_id',
                    'order.customer:uuid,name',
                    'order.payment:uuid,order_id,amount'
                )->get()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function totalRecord(array $filter)
    {
        try {
            $query = $this->query();

            if (!empty($filter['type']) && $filter['type'] == "event") {
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
            } else {
                if (!empty($filter['date_start'])) {
                    $query->where('date', '>=', $filter['date_start']);
                }

                if (!empty($filter['date_end'])) {
                    $query->where('date', '<=', $filter['date_end']);
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
                'data' => $this->query()->where('order_id', $orderId)->sum('amount')
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getAllByOrder($id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->where('order_id', $id)->with('image')->get()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getExpense(array $filter)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()
                    ->whereHas('order', function ($query) use ($filter) {
                        $query->whereBetween('event_date', [$filter['event_date_start'], $filter['event_date_end']])
                            ->whereHas('payment', function ($query) {
                                $query->where('status', 'not finished');
                            });;
                    })->get()
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
                'data' => $this->firstOrCreate($payload)->load('image')
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

    public function getByOrderNDate($order_id, $date)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->where('order_id', $order_id)->where('date', $date)->first()
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

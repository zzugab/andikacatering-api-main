<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Uuid;
use Throwable;

class EventLogisticModel extends Model
{

    use HasFactory, SoftDeletes, Uuid;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'order_id',
        'item_id',
        'quantity',
        'items_not_returned',
        'in_client_hands',
        'notes',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'event_logistics';

    public function order()
    {
        return $this->belongsTo(OrdersCustomersModel::class, 'order_id', 'uuid');
    }

    public function inventory()
    {
        return $this->belongsTo(InventoryItemModel::class, 'item_id', 'uuid');
    }

    public function getAll($orderUuid)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->where('order_id', $orderUuid)->with('inventory:uuid,name')->get()
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
                'data' => $this->firstOrCreate($payload)->load('inventory:uuid,name')
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
                'data' => $this->query()->where('uuid', $id)->first()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getByOrderAndItemId(string $order_id, string $item_id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->where('order_id', $order_id)->where('item_id', $item_id)->first()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getByDateAndItemId($filter, $item_id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->whereHas('order', function ($query) use ($filter) {
                    $query->where('event_date', $filter["event_date"])
                        ->whereNotIn('status', ['refund', 'event_completed', 'event_finish']);
                })->where('item_id', $item_id)->sum('quantity')
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
}

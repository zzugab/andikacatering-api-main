<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Uuid;
use Throwable;

class EventTransferItemModel extends Model
{
    use HasFactory, SoftDeletes, Uuid;
    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'event_transfer_id',
        'item_id',
        'quantity',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'event_transfer_items';

    public function eventTransfer()
    {
        return $this->belongsTo(EventTransferModel::class, 'event_transfer_id', 'uuid');
    }

    public function inventory()
    {
        return $this->belongsTo(InventoryItemModel::class, 'item_id', 'uuid');
    }

    public function getAll()
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->with('item_id')->get()
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
                'data' => $this->query()->with('inventory')->where('uuid', $id)->first()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getAllByTransferId(string $id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->where('event_transfer_id', $id)->get()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getByOrderAndItem($uuidOrder, $item_id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->whereHas('eventTransfer', function ($query) use ($uuidOrder) {
                    $query->where('order_id_to', $uuidOrder);
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

    public function dropByTransfer(string $id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->where('event_transfer_id', $id)->delete()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }
}

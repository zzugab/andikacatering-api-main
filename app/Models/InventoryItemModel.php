<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Uuid;
use Throwable;

class InventoryItemModel extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'name',
        'quantity',
        'status',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'inventory_items';

    public function orderLogistic()
    {
        return $this->hasMany(EventLogisticModel::class, 'item_id', 'uuid');
    }
    public function transferLogistic()
    {
        return $this->hasMany(EventTransferItemModel::class, 'item_id', 'uuid');
    }

    public function getAll()
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->get()
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
                'data' => $this->query()->where('uuid', $id)->first()
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

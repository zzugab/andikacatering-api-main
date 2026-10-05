<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Uuid;
use Exception;
use Throwable;

class EventTransferModel extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'status',
        'order_id_from',
        'order_id_to',
        'note'
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'event_transfers';

    protected static function booted()
    {
        static::deleting(function ($item) {
            if (! $item->isForceDeleting()) {
                $item->transferItems()->get()->each->delete();
            } else {
                $item->transferItems()->withTrashed()->get()->each->forceDelete();
            }
        });

        static::restoring(function ($item) {
            $item->transferItems()->onlyTrashed()->get()->each->restore();
        });
    }

    public function orderFrom()
    {
        return $this->belongsTo(OrdersCustomersModel::class, 'order_id_from', 'uuid');
    }

    public function orderTo()
    {
        return $this->belongsTo(OrdersCustomersModel::class, 'order_id_to', 'uuid');
    }

    public function transferItems()
    {
        return $this->hasMany(EventTransferItemModel::class, 'event_transfer_id', 'uuid');
    }

    public function getAll(array $filter)
    {
        $result = $this->query();

        if (!empty($filter['status'])) {
            $result->where('status',  $filter['status']);
        }

        if (!empty($filter['order_id_from'])) {
            $result->where('order_id_from', $filter['order_id_from']);
        }

        if (!empty($filter['order_id_to'])) {
            $result->where('order_id_to', $filter['order_id_to']);
        }

        try {
            return [
                'status' => true,
                'data' => $result->with('orderFrom')->with('orderTo')->get()
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
            $createdData = $this->firstOrCreate($payload);

            if ($createdData) {
                return [
                    'status' => true,
                    'data' => $createdData
                ];
            } else {
                return [
                    'status' => false,
                    'error' => 'Failed to create or find the entry.'
                ];
            }
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
                'data' => $this->query()->with('orderFrom')->with('orderTo')->with('transferItems.inventory:uuid,name')->where('uuid', $id)->first()
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

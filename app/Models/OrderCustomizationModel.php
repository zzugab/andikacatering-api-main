<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Uuid;
use Throwable;

class OrderCustomizationModel extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'order_id',
        'menu_id',
        'portion',
        'details'
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'order_customizations';

    public function order()
    {
        return $this->belongsTo(OrdersCustomersModel::class, 'order_id', 'uuid');
    }
    public function menu()
    {
        return $this->belongsTo(MenuModel::class, 'menu_id', 'uuid');
    }

    public function getAll($order_id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->where('order_id', $order_id)->with('menu:uuid,name,price')->get()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getAllOrder($order_id, array $filter = null)
    {
        try {
            $result = $this->query();
            if (!empty($filter['scope']) && $filter['scope'] == "deleted") {
                $result->onlyTrashed();
            }
            return [
                'status' => true,
                'data' => $result->where('order_id', $order_id)
                    ->with(['menu' => function ($query) {
                        $query->withTrashed()->select('uuid', 'name','type','price');
                    }])
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
                'data' => $this->query()->with('menu')->where('uuid', $id)->first()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getByIdDeleted(string $id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->onlyTrashed()->with('menu')->where('uuid', $id)->first()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getByOrder($uuidOrder)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->with('menu')->where('order_id', $uuidOrder)->first()
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

    public function manyDrop(array $array)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->whereIn('uuid', $array)->delete()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function restoreById(string $id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->onlyTrashed()->where('uuid', $id)->first()->restore()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }
}

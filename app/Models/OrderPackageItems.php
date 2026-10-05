<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Throwable;

class OrderPackageItems extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'menu_id',
        'portion',
        'details',
        'status'

    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'order_package_items';

    public function orderPackageRel()
    {
        return $this->hasMany(OrderPackageRelModel::class, 'order_package_item_id', 'uuid');
    }

    public function menu()
    {
        return $this->belongsTo(MenuModel::class, 'menu_id', 'uuid');
    }

    public function category()
    {
        return $this->belongsTo(MenuCategoryModel::class, 'category_id', 'id');
    }

    // public function orderPackage()
    // {
    //     return $this->belongsTo(OrderPackageModel::class, 'order_package_id', 'uuid');
    // }

    public function getAll()
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->with('menu')->get()
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
            $query = $this->firstOrCreate($payload);
            $query->load('menu:uuid,name');
            return [
                'status' => true,
                'data' => $query
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
                'data' => $this->query()->with('menu:uuid,name,category_id')->whereHas('orderPackageRel', function ($query) use ($uuidOrder) {
                    $query->whereHas('orderPackage', function ($subQuery) use ($uuidOrder) {
                        $subQuery->where('order_id', $uuidOrder);
                    });
                })->get()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getAllOrder($uuidOrder, $status = null, $arrUuidPackage = null)
    {
        try {
            if ($status == null) $status = 1;
            $result = $this->query()
                ->with([
                    'menu' => function ($query) {
                        $query->withTrashed()
                            ->select('uuid', 'name', 'type', 'category_id');
                    },
                    'menu.category' => function ($query) {
                        $query->select('uuid', 'name');
                    },
                    'menu.category.packageData' => function ($query) {
                        $query->select('uuid', 'quantity_percent', 'category_id', 'packages_id');
                    },
                    'menu.foodStall' => function ($query) {
                        $query->select('uuid', 'quantity_percent', 'menu_id', 'packages_id');
                    },
                    'orderPackageRel' => function ($query) {
                        $query->select('uuid', 'order_package_id', 'order_package_item_id');
                    },
                    // 'orderPackageRel.orderPackage' => function ($query) {
                    //     $query->select('uuid', 'order_id', 'package_id', 'portion', 'details');
                    // }
                ])
                ->whereHas('orderPackageRel', function ($query) use ($uuidOrder, $arrUuidPackage) {
                    $query->whereHas('orderPackage', function ($subQuery) use ($uuidOrder, $arrUuidPackage) {
                        $subQuery->where('order_id', $uuidOrder);
                        if (isset($arrUuidPackage)) $subQuery->whereIn('package_id', $arrUuidPackage);
                    });
                });
            if ($status != 'all') $result->where('status', $status);
            return [
                'status' => true,
                'data' => $result->get()
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

    public function addEditMenu(array $payload)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->updateOrCreate(
                    ['uuid' => $payload['uuid'] ?? null],
                    [
                        'menu_id' => $payload['menu_id'],
                        'status' => $payload['status']
                    ])
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function manyUpdate(array $payload)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->upsert($payload, ['uuid'], ['menu_id', 'portion', 'details', 'status'])
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

    public function updateStatusBatchToFalse(array $array)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->whereIn('uuid', $array)->update(['status' => false, 'portion' => 0, 'details' => null])
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

    public function restore(array $payload, string $id)
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
}

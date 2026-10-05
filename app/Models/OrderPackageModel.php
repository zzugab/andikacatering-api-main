<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Throwable;

class OrderPackageModel extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'order_id',
        'package_id',
        'portion',
        'details',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'order_packages';

    protected static function booted()
    {
        static::deleting(function ($item) {
            if (!$item->isForceDeleting()) {
                $item->orderPackageRel()->get()->each->delete();
            } else {
                $item->orderPackageRel()->withTrashed()->get()->each->forceDelete();
            }
        });

        static::restoring(function ($item) {
            $item->orderPackageRel()->onlyTrashed()->get()->each->restore();
        });
    }

    public function orderPackageRel()
    {
        return $this->hasMany(OrderPackageRelModel::class, 'order_package_id', 'uuid');
    }

    public function order()
    {
        return $this->belongsTo(OrdersCustomersModel::class, 'order_id', 'uuid');
    }

    public function package()
    {
        return $this->belongsTo(PackagesModel::class, 'package_id', 'uuid');
    }

    public function getAll($order_id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->where('order_id', $order_id)->with('package')->get()
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
        $result = $this->query();
        if (!empty($filter['scope']) && $filter['scope'] == "deleted") {
            $result->onlyTrashed();
        }

        try {
            return [
                'status' => true,
                'data' => $result->where('order_id', $order_id)
                    ->with(['package' => function ($query) {
                        $query->withTrashed()
                            ->orderBy('type', 'desc')
                            ->orderBy('name', 'asc');
                    }])->get()
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
                'data' => $this->query()->where('uuid', $id)->with('package')->first()
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
                'data' => $this->query()->onlyTrashed()->where('uuid', $id)->with('package')->first()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

//    public function getByOrder($uuidOrder)
//    {
//        try {
//            return [
//                'status' => true,
//                'data' => $this->query()->with('package')->with('orderPackageItems.menu:uuid,name')->where('order_id', $uuidOrder)->get()
//            ];
//        } catch (Throwable $th) {
//            return [
//                'status' => false,
//                'error' => $th->getMessage()
//            ];
//        }
//    }

    public function edit(array $payload)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->where('uuid', $payload['uuid'])->first()->update($payload)
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

    // public function restoreById(string $id)
    // {
    //     try {
    //         $data = $this->query()->onlyTrashed()->where('uuid', $id)->first();
    //         $data->restore();
    //         $data->orderPackageItems()->onlyTrashed()->get()->each(function ($item) {
    //             $item->restore();
    //         });
    //         return [
    //             'status' => true,
    //             'data' => $data
    //         ];
    //     } catch (Throwable $th) {
    //         return [
    //             'status' => false,
    //             'error' => $th->getMessage()
    //         ];
    //     }
    // }
}

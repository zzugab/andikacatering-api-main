<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Throwable;

class OrderPackageRelModel extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'order_package_id',
        'order_package_item_id',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'order_package_rel';

    protected static function booted()
    {
        static::deleting(function ($item) {
            $orderPackageItem = $item->orderPackageItems()->first(); // atau ambil yang spesifik kalau perlu

            if (!$orderPackageItem) {
                return;
            }

            $stillUsed = $orderPackageItem
                ->orderPackageRel()
                ->where('uuid', '!=', $item->uuid)
                ->exists();

            if (!$stillUsed) {
                $orderPackageItem->delete();
            }
        });

        static::restoring(function ($item) {
            $trashedItem = $item->orderPackageItems()->onlyTrashed()->first();

            if ($trashedItem) {
                $trashedItem->restore();
            }
        });
    }

    public function orderPackageItems()
    {
        return $this->belongsTo(OrderPackageItems::class, 'order_package_item_id', 'uuid');
    }

    public function orderPackage()
    {
        return $this->belongsTo(OrderPackageModel::class, 'order_package_id', 'uuid');
    }

    public function getAllByOrder($uuidOrder)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()
                    ->whereHas('orderPackage', function ($query) use ($uuidOrder) {
                        $query->where('order_id', $uuidOrder);
                    })
                    ->get()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function store(array $item)
    {
        try {
            return [
                'status' => true,
                'data' => $this->firstOrCreate($item)
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
                'data' => $this->query()->whereIn('order_package_id', $array)->delete()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }
}

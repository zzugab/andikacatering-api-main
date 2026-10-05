<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Throwable;

class PackagesModel extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'price',
        'type',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'packages';

    public function packageData()
    {
        return $this->hasMany(PackagesDataModel::class, 'packages_id', 'uuid');
    }

    public function packageFoodStall()
    {
        return $this->hasMany(PackageFoodStallModel::class, 'packages_id', 'uuid');
    }

    public function getAll($limit = null, array $filter = null)
    {
        $query = $this->query();
        if ($limit) {
            $query->limit($limit);
        }

        if (!empty($filter['name'])) {
            $query->where('name', 'like', '%' . $filter['name'] . '%');
        }

        if (!empty($filter['price_start'])) {
            $query->where('price', '>=', $filter['price_start']);
        }

        if (!empty($filter['price_end'])) {
            $query->where('price', '<=', $filter['price_end']);
        }

        if (!empty($filter['type'])) {
            $query->where('type', 'like', '%' . $filter['type'] . '%');
        }

        try {
            return [
                'status' => true,
                'data' => $query
                    ->orderBy('type', 'desc')
                    ->orderBy('name', 'asc')->get()
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
                'data' => $this->query()->with('packageData.category.menu', 'packageFoodStall.menu')->where('uuid', $id)->first()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getByManyId(array $arrId)
    {
        try {
            $packages = $this->query()
                ->with('packageData.category.menu', 'packageFoodStall.menu')
                ->whereIn('uuid', $arrId)
                ->get();


            // Kumpulkan semua menu dari relasi
            $allMenus = collect();
            foreach ($packages as $package) {
                // 1. Dari package_data -> category -> menu
                foreach ($package['packageData'] ?? [] as $packageData) {
                    $menus = $packageData['category']['menu'] ?? [];
                    $allMenus = $allMenus->merge($menus);
                }

                // 2. Dari package_food_stall -> menu
                foreach ($package['packageFoodStall'] ?? [] as $foodStall) {
                    if (isset($foodStall['menu'])) {
                        $allMenus->push($foodStall['menu']);
                    }
                }
            }

            // Filter menu yang duplikat berdasarkan id (atau kode unik lain)
            $uniqueMenus = $allMenus->unique('uuid')->map(fn($m) => [
                'uuid' => $m['uuid'],
                'name' => $m['name']
            ])->values();

            return [
                'status' => true,
                'data' => $uniqueMenus
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

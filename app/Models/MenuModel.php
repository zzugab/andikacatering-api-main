<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Uuid;
use Throwable;

class MenuModel extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'name',
        'price',
        'category_id',
        'type',
        'description',
        'image_id',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'menus';

    public function category()
    {
        return $this->belongsTo(MenuCategoryModel::class, 'category_id', 'uuid');
    }

    public function foodStall()
    {
        return $this->hasMany(PackageFoodStallModel::class, 'menu_id', 'uuid');
    }

    public function image()
    {
        return $this->belongsTo(Images::class, 'image_id', 'uuid');
    }

    public function getAll($limit = null, $filter = [])
    {
        $query = $this->query();
        if ($limit) {
            $query->inRandomOrder()->limit($limit);
        }
        if (isset($filter['type'])) {
            $query->where('type',  $filter['type']);
        }

        try {
            return [
                'status' => true,
                'data' => $query->with(['category' => function ($query) {
                    $query->orderBy('name', 'asc');
                }])->with('image')->get()
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
            $menu = $this->firstOrCreate($payload);
            $menu->load('category', 'image');

            return [
                'status' => true,
                'data' => $menu
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
                'data' => $this->query()->where('uuid', $id)->with('category')->with('image')->first()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getByOrderId(string $orderId)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->with('category.packageData.package')->whereHas('category.packageData.package', function ($query) use ($orderId) {
                    $query->where('uuid', $orderId);
                })->get()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getFoodStallByOrderId(string $orderId)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->with('foodStall.package')->whereHas('foodStall.package', function ($query) use ($orderId) {
                    $query->where('uuid', $orderId);
                })->get()
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

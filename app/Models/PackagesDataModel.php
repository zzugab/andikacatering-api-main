<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Throwable;

class PackagesDataModel extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'packages_id',
        'category_id',
        'quantity_percent',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'packages_data';

    public function package()
    {
        return $this->belongsTo(PackagesModel::class, 'packages_id', 'uuid');
    }

    public function category()
    {
        return $this->belongsTo(MenuCategoryModel::class, 'category_id', 'uuid');
    }

    public function getAll(array $packages_id)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()
                    ->whereIn('packages_id', $packages_id)
                    ->with('category.menu')
                    ->get()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function getSortCategory(array $packages_id)
    {
        try {
            $winner = $this->query()->select('packages_id', DB::raw('count(*) as total'))->whereIn('packages_id', $packages_id)->groupBy('packages_id')->orderByDesc('total')->first();
//            return $winner['packages_id'];
            return [
                'status' => true,
                'data' => $this->query()
                    ->where('packages_id', $winner['packages_id'])
                    ->with(['category'])
                    ->orderBy('created_at', 'asc')
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
                'data' => $this->query()->with('category.menu')->where('uuid', $id)->first()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

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
}

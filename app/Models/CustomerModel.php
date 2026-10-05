<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Uuid;
use Throwable;

class CustomerModel extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'name',
        'phone_number_1',
        'phone_number_2',
        'address',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'customers';

    protected static function booted()
    {
        static::deleting(function ($item) {
            if (! $item->isForceDeleting()) {
                $item->order()->get()->each->delete();
            } else {
                $item->order()->withTrashed()->get()->each->forceDelete();
            }
        });

        static::restoring(function ($item) {
            $item->order()->onlyTrashed()->get()->each->restore();
        });
    }
    public function order()
    {
        return $this->hasMany(OrdersCustomersModel::class, 'customer_id', 'uuid');
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
                'data' => $this->query()->with(['order' => function ($query) {
                    $query->orderBy('status', 'asc')
                        ->orderBy('event_date', 'desc');
                }])
                    ->where('uuid', $id)->first()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    //Untuk filtering ketika membuat order
    public function getByName(string $name)
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()
                    ->where('name', 'LIKE', '%' . $name . '%')
                    ->limit(5)
                    ->get()
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

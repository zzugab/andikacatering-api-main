<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Http\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Throwable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserModel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Uuid;

    protected $table = 'users';

    protected $fillable = [
        'uuid',
        'name',
        'username',
        'password',
        'role_id',
        'email',
        'phone_number',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    public function role()
    {
        return $this->belongsTo(RolesModel::class, 'role_id', 'uuid');
    }
    public function activities()
    {
        return $this->hasMany(ActivityModel::class, 'user_id');
    }

    public function order()
    {
        return $this->hasMany(OrdersCustomersModel::class, 'field_coordinator_id', 'uuid');
    }

    public function getAll()
    {
        try {
            return [
                'status' => true,
                'data' => $this->query()->with('role')->get()
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
                'data' => $this->query()->where('uuid', $id)->with('role')->first()
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
            $query = $this->query()->where('uuid', $id)->first()->update($payload);
            return [
                'status' => true,
                'data'  => $query
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Uuid;
use Throwable;

class ActivityModel extends Model
{
    use HasFactory, Uuid;

    protected $table = 'activities';

    protected $fillable = ['uuid', 'user_id', 'description'];

    protected $hidden = [
        'id',
        'updated_at',
        'deleted_at'
    ];

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'uuid');
    }

    public function getAll(array $filter)
    {
        $result = $this->query();
        if (!empty($filter['user_id'])) {
            $result->where('user_id', $filter['user_id']);
        }
        try {
            return [
                'status' => true,
                'data' => $result->with('user')->get()
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
                'data' => $this->query()->where('uuid', $id)->with('user')->first()
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }
}

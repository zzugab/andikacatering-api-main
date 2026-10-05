<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Uuid;
use Throwable;

class TestimoniModel extends Model
{
    use HasFactory, SoftDeletes, Uuid;


    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'customer_id',
        'rating',
        'testimonial',
        'is_filled'
    ];

    protected $hidden = [
        'id',
        'created_at',
        'deleted_at'
    ];

    protected $table = 'testimonials';

    public function customer()
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id', 'uuid');
    }

    public function getAll($limit = null)
    {
        $query = $this->query()->select(
            'uuid',
            'customer_id',
            'rating',
            'testimonial',
            'is_filled',
            \DB::raw('DATE(updated_at) as date')
        );
        if ($limit) {
            $query->where('rating', '>=', '4')->inRandomOrder()->limit($limit);
        }
        try {
            return [
                'status' => true,
                'data' => $query->with('customer:uuid,name,address')->get()
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
                'data' => $this->query()->with('customer')->where('uuid', $id)->first()
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

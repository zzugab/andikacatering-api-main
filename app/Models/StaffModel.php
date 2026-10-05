<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Uuid;
use Throwable;

class StaffModel extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'name',
        'phone_number',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'staff';

    public function eventStaff()
    {
        return $this->hasMany(EventStaffModel::class, 'staff_id', 'uuid');
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
            $staff = $this->query()->where('uuid', $id)->with('eventStaff.order')->first();

            if (!$staff) {
                return [
                    'status' => false,
                    'error' => 'Staff not found'
                ];
            }

            return [
                'status' => true,
                'data' => [
                    'uuid' => $staff->uuid,
                    'name' => $staff->name,
                    'phone_number' => $staff->phone_number,
                    'event' => $staff->eventStaff->map(function ($eventStaff) {
                        return [
                            'uuid' => $eventStaff->uuid,
                            'order_id' => $eventStaff->order_id,
                            'order' => $eventStaff->order ? [
                                'uuid' => $eventStaff->order->uuid,
                                'order_name' => $eventStaff->order->order_name,
                                'customer_id' => $eventStaff->order->customer_id,
                                'location' => $eventStaff->order->location,
                                'event_date' => $eventStaff->order->event_date,
                                'event_time' => $eventStaff->order->event_time,
                            ] : null,
                        ];
                    })
                ]
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

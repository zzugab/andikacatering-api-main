<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Uuid;
use Throwable;

class MeetingScheduleModel extends Model
{
    use HasFactory, SoftDeletes, Uuid;
    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'title',
        'date',
        'start_time',
        'end_time',
        'location',
        'note',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'meeting_schedules';

    public function getAll(array $filter)
    {
        $result = $this->query()->select('*')
            ->selectRaw("
                CASE
                    WHEN date = CURRENT_DATE AND CURRENT_TIME BETWEEN start_time AND end_time THEN 'ongoing'
                    WHEN date < CURRENT_DATE OR (date = CURRENT_DATE AND CURRENT_TIME > end_time) THEN 'done'
                    ELSE 'upcoming'
                END AS status
            ");

        $statusFilter = $filter['status'] ?? ['upcoming', 'ongoing']; // Default adalah 'upcoming' dan 'ongoing'

        // Pastikan $statusFilter selalu menjadi array
        if (!is_array($statusFilter)) {
            // Jika input berupa string (misalnya "done,ongoing"), ubah menjadi array
            $statusFilter = explode(',', $statusFilter);
        }

        if (!empty($filter['title'])) {
            $result->where('title', 'like', '%' . $filter['title'] . '%');
        }

        if (!empty($filter['event_date_start'])) {
            $result->where('date', '>=', $filter['event_date_start']);
        }

        if (!empty($filter['event_date_end'])) {
            $result->where('date', '<=', $filter['event_date_end']);
        }

        if (!empty($filter['location'])) {
            $result->where('location', 'like', '%' . $filter['location'] . '%');
        }

        try {
            $data = $result->get();
            if (!empty($statusFilter) && $statusFilter !== ['all']) {
                // ponytail: filter status computed di collection; cukup kecil untuk jadwal meeting.
                $data = $data->whereIn('status', $statusFilter)->values();
            }

            return [
                'status' => true,
                'data' => $data
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
                'data' => $this->query()->select('*')
                    ->selectRaw("
                        CASE
                            WHEN date = CURRENT_DATE AND CURRENT_TIME BETWEEN start_time AND end_time THEN 'ongoing'
                            WHEN date < CURRENT_DATE OR (date = CURRENT_DATE AND CURRENT_TIME > end_time) THEN 'done'
                            ELSE 'upcoming'
                        END AS status
                    ")->where('uuid', $id)->first()
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

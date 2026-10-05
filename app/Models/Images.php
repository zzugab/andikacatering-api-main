<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\Uuid;
use Throwable;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class Images extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'type',
        'name',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $table = 'images';

    public function menu()
    {
        return $this->hasMany(MenuModel::class, 'image_id', 'uuid');
    }

    public function installment()
    {
        return $this->hasMany(PaymentInstallmentsModel::class, 'payment_id', 'uuid');
    }

    public function store($imageFile, $type)
    {
        try {
            $manager = new ImageManager(new Driver());
            $filename = time() . '_' . $imageFile->getClientOriginalName();
            $image = $manager->read($imageFile);
            $image = $image->toJpeg(80);

            $image->save(public_path('storage/uploads/' . $type . "/" . $filename));

            return [
                'status' => true,
                'data' => $this->create([
                    'type' => $type,
                    'name' => $filename,
                ])
            ];
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function edit($imageFile, $type, $uuid) {}
}

<?php

namespace App\Http\Traits;

use Ramsey\Uuid\Exception\UnableToBuildUuidException;
use Ramsey\Uuid\Uuid as Generator;

trait Uuid
{
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            try {
                if (empty($model->uuid)) {
                    $model->uuid = Generator::uuid4()->toString();
                }
            } catch (UnableToBuildUuidException $e) {
                abort(500, $e->getMessage());
            }
        });
    }
}

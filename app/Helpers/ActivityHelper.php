<?php

namespace App\Helpers;

use App\Models\ActivityModel;
use Illuminate\Support\Facades\Auth;

class ActivityHelper
{
    public static function log($description)
    {
        $userId = Auth::check() ? Auth::user()->uuid : null;

        ActivityModel::create([
            'user_id' => $userId,
            'description' => $description,
        ]);
    }
}

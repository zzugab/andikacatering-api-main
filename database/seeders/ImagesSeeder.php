<?php

namespace Database\Seeders;

use App\Models\Images;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'uuid' => 'd30e0078-01f1-4d66-b32f-a7a6105deeec',
            'type' => 'menu',
            'name' => 'default.png',
        ];

        Images::create($data);
    }
}

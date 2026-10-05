<?php

namespace Database\Seeders;

use App\Models\UserModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'uuid' => 'df74b085-a8a9-4823-8d7d-cf03b1b41af7',
            'name' => "admin",
            'username' => 'admin',
            'password' => 'password',
            'role' => 'ceca460d-3d10-4465-ace3-0e5487da9614',
            'email' => 'admin@mail.com',
            'phone_number' => '088888888888',
        ];

        UserModel::create($data);
    }
}

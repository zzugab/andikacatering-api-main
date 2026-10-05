<?php

namespace Database\Seeders;

use App\Models\RolesModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'uuid' => 'ceca460d-3d10-4465-ace3-0e5487da9614',
                'name' => 'Superadmin'
            ],
            [
                'uuid' => 'fba6b7bf-4d5e-4bde-8951-851a0c0d8a76',
                'name' => 'Admin'
            ],
            [
                'uuid' => '4c45ecb6-b7c3-4227-a2cc-75a1b4d76187',
                'name' => 'Korlap'
            ],
            [
                'uuid' => 'a2d660bb-eb2a-4c50-a724-ef560e378906',
                'name' => 'Pjgudang'
            ],
            [
                'uuid' => 'e67db8f9-21e2-454a-911f-bb50b27251bf',
                'name' => 'Akuntan'
            ],
        ];

        foreach ($roles as $roleName) {
            RolesModel::create($roleName);
        }
    }
}

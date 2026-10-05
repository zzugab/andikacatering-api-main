<?php

namespace Database\Seeders;

use App\Models\OrderDetailModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'uuid' => 'd30e0078-01f1-4d66-b32f-a7a6105deeec',
            'order_id' => '',
            'akad_time' => 'ff39e07e-6e5e-42ef-8c12-fae11821cb2d',
            'akad_end' => '',
            'resepsi_time' => '2024-08-25',
            'resepsi_end' => '',
            'nuance' => '',
            'general_buffet' => '',
            'vip_buffet' => '',
            'vip_table' => '',
            'wedding_food_table' => '',
            'akad_table' => '',
            'reception_table' => '',
            'for_naib' => '',
            'ayam_bekakak_nasi_punar' => '',
            'mica_for_besan' => '',
        ];

        OrderDetailModel::create($data);
    }
}

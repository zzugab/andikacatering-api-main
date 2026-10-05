<?php

namespace Database\Seeders;

use App\Models\OrderCustomizationModel;
use App\Models\OrdersCustomersModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrdersCustomersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        $data = [
            'uuid' => 'd30e0078-01f1-4d66-b32f-a7a6105deeec',
            'field_coordinator_id' => '',
            'customer_id' => 'ff39e07e-6e5e-42ef-8c12-fae11821cb2d',
            'location' => '',
            'event_date' => '2024-08-25',
            'event_time' => '',
            'portion' => '',
            'note' => '',
            'status' => '',
            'handover_of_leftovers' => '',
        ];

        OrdersCustomersModel::create($data);
    }
}

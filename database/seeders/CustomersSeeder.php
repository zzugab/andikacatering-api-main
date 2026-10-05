<?php

namespace Database\Seeders;

use App\Models\CustomerModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'uuid' => 'ff39e07e-6e5e-42ef-8c12-fae11821cb2d',
            'name' => 'Rizka/Abdul Muhit',
            'phone_number_1' => '089603528994',
            'phone_number_2' => '0811127660',
            'address' => 'metro cilegon',
        ];

        CustomerModel::create($data);
    }
}

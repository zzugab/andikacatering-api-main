<?php

namespace Database\Seeders;

use App\Models\BankModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'uuid' => 'b639bbd0-eb09-4c61-8446-7bfb600e63e3',
            'name' => 'Bri',
            'account_number' => '089808989080',
            'account_owner' => 'naufal tamam',
        ];

        BankModel::create($data);
    }
}

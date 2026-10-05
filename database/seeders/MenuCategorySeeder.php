<?php

namespace Database\Seeders;

use App\Models\MenuCategoryModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                "uuid" => "7e8ae4b9-cd68-4636-842e-0a6497b49ad7",
                "name" => "aneka nasi"
            ],
            [
                "uuid" => "9d979654-9427-4c3e-83fc-53500d136dd2",
                "name" => "aneka daging sapi"
            ],
            [
                "uuid" => "6efa083a-fc8a-4e0f-938f-a7000e5be289",
                "name" => "aneka ayam"
            ],
            [
                "uuid" => "a73023d2-9e71-4564-b914-9ed9f4aa5719",
                "name" => "aneka ikan"
            ],
            [
                "uuid" => "8d6962b1-e0a4-4591-b195-9286a131599b",
                "name" => "aneka sayur kuah"
            ],
            [
                "uuid" => "2169a250-2eb6-472d-bf8f-481454f1aedd",
                "name" => "aneka minuman"
            ],
            [
                "uuid" => "9968289d-fae5-413e-9425-3199a385fd95",
                "name" => "aneka kerupuk"
            ],
            [
                "uuid" => "bb49f4c2-fceb-42da-b371-98a51100ab9d",
                "name" => "aneka sambal"
            ],
            [
                "uuid" => "8cea0087-22c7-4e01-a3f8-380ab87bea0f",
                "name" => "aneka tumisan"
            ],
            [
                "uuid" => "e304dedc-8867-4dc4-842d-2412b06023c6",
                "name" => "sarapan"
            ],
            [
                "uuid" => "f2381588-9e5f-489d-af72-92248ef0cd54",
                "name" => "food stall",
            ]
        ];
        foreach ($data as $item) {
            MenuCategoryModel::create($item);
        }
    }
}

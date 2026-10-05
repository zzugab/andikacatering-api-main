<?php

namespace Database\Seeders;

use App\Models\MenuModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data =
            [
                ["uuid" => "10b02c13-ce67-4849-84e6-0211d55c2972", "name" => "Nasi Putih", "price" => "4000", "category_id" => "7e8ae4b9-cd68-4636-842e-0a6497b49ad7", "type" => "Buffet", "description" => "Nasi putih pulen", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "8eb601ab-5c1c-4fd0-b75c-8e1ee393c6fc", "name" => "Nasi Goreng", "price" => "4000", "category_id" => "7e8ae4b9-cd68-4636-842e-0a6497b49ad7", "type" => "Buffet", "description" => "Nasi goreng gurih dilengkapi dengan mix vegetable", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "0715bcb3-3cb4-4fb3-9651-c6c710c338db", "name" => "Rendang", "price" => "0", "category_id" => "9d979654-9427-4c3e-83fc-53500d136dd2", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "b3ca036f-b0de-43bc-a5fc-cf6c43dced6e", "name" => "Gepuk", "price" => "0", "category_id" => "9d979654-9427-4c3e-83fc-53500d136dd2", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "9dbdb4e7-b9b0-4e1f-b1ee-2d99387bddc9", "name" => "Bistik", "price" => "0", "category_id" => "9d979654-9427-4c3e-83fc-53500d136dd2", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "a593094c-9090-43a4-ae99-86782c85da3d", "name" => "Teriyaki", "price" => "0", "category_id" => "9d979654-9427-4c3e-83fc-53500d136dd2", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "34b2553f-9c46-4859-95e2-b217faf06df6", "name" => "Balado", "price" => "0", "category_id" => "9d979654-9427-4c3e-83fc-53500d136dd2", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "ba016cdd-2ce5-4e36-a5d5-4ddb138c4c5e", "name" => "Lada hitam", "price" => "0", "category_id" => "9d979654-9427-4c3e-83fc-53500d136dd2", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "5fab5200-c4af-49e4-aa82-0938f6166139", "name" => "Suwir", "price" => "0", "category_id" => "6efa083a-fc8a-4e0f-938f-a7000e5be289", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "741628ef-b5a6-4cab-a2be-75e472557937", "name" => "Rolade", "price" => "0", "category_id" => "6efa083a-fc8a-4e0f-938f-a7000e5be289", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "0a964868-8f66-482e-b692-a0363dba7c58", "name" => "Teriyaki", "price" => "0", "category_id" => "6efa083a-fc8a-4e0f-938f-a7000e5be289", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "828dc322-145f-453c-a262-734c85058a4a", "name" => "Rica-rica", "price" => "0", "category_id" => "6efa083a-fc8a-4e0f-938f-a7000e5be289", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "6d5ea32f-ddba-44b2-8681-e64f1c5c6925", "name" => "Woku", "price" => "0", "category_id" => "6efa083a-fc8a-4e0f-938f-a7000e5be289", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "45ebbfc1-3db8-4e84-96e0-f3173fa2b636", "name" => "Cah ayam paprika", "price" => "0", "category_id" => "6efa083a-fc8a-4e0f-938f-a7000e5be289", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "4d7a2963-8330-445e-9669-8a15e7e4e0d3", "name" => "Lada hitam", "price" => "0", "category_id" => "6efa083a-fc8a-4e0f-938f-a7000e5be289", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "d339a1d3-9d44-4856-946e-b2fed3aba231", "name" => "Kungpao", "price" => "0", "category_id" => "6efa083a-fc8a-4e0f-938f-a7000e5be289", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "5842ea4d-2e72-4d55-832b-955eb002bd5a", "name" => "Sop kimlo", "price" => "0", "category_id" => "8d6962b1-e0a4-4591-b195-9286a131599b", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "511c37f2-34af-4265-acca-af7c3e43ac62", "name" => "Soto", "price" => "0", "category_id" => "8d6962b1-e0a4-4591-b195-9286a131599b", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "d7dc0058-0c26-4872-b216-0aaea6d27a1f", "name" => "Sambal Goreng Ati Sapi", "price" => "0", "category_id" => "8cea0087-22c7-4e01-a3f8-380ab87bea0f", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "55be663b-10b3-4ed2-87f8-e9574a57c2cb", "name" => "Rujak", "price" => "0", "category_id" => "8cea0087-22c7-4e01-a3f8-380ab87bea0f", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "245b7013-77b4-4225-bf94-ca6df970fa68", "name" => "Gado-gado", "price" => "0", "category_id" => "8cea0087-22c7-4e01-a3f8-380ab87bea0f", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "a134a55e-551a-49b0-84d8-f79ad0be4dae", "name" => "Salad bangkok", "price" => "0", "category_id" => "8cea0087-22c7-4e01-a3f8-380ab87bea0f", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "0fc62cca-4cd7-467a-baca-5015f0edeae6", "name" => "Capcay", "price" => "0", "category_id" => "8cea0087-22c7-4e01-a3f8-380ab87bea0f", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "ffe8d7fc-dad2-4f10-9c21-8d1941303a5d", "name" => "Mustofa", "price" => "0", "category_id" => "8cea0087-22c7-4e01-a3f8-380ab87bea0f", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "c65060af-fc8d-4a95-ae96-167726fac1ee", "name" => "Kerupuk udang", "price" => "0", "category_id" => "9968289d-fae5-413e-9425-3199a385fd95", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "00a4651a-c77c-4fc0-91b1-e08521d32e91", "name" => "Air Mineral", "price" => "0", "category_id" => "2169a250-2eb6-472d-bf8f-481454f1aedd", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "f747f618-922b-4f6d-8556-64143ee9165b", "name" => "Sambal", "price" => "0", "category_id" => "bb49f4c2-fceb-42da-b371-98a51100ab9d", "type" => "Buffet", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "a128594a-42d3-4e51-9f29-6be18c82070b", "name" => "Buah potong", "price" => "0", "category_id" => "f2381588-9e5f-489d-af72-92248ef0cd54", "type" => "Foodstall", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "0fa69d8c-23e7-40b4-b2e8-11c2893950bd", "name" => "Es Cendol", "price" => "0", "category_id" => "f2381588-9e5f-489d-af72-92248ef0cd54", "type" => "Foodstall", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "3bb04b47-feca-4a1d-8da0-02c308a6a755", "name" => "Soft Drink", "price" => "0", "category_id" => "f2381588-9e5f-489d-af72-92248ef0cd54", "type" => "Foodstall", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "a704e482-0527-4868-8264-49a57bd9ecef", "name" => "Siomay", "price" => "0", "category_id" => "f2381588-9e5f-489d-af72-92248ef0cd54", "type" => "Foodstall", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "a17ac384-a4a4-496e-a476-e30245d13bd1", "name" => "Batagor", "price" => "0", "category_id" => "f2381588-9e5f-489d-af72-92248ef0cd54", "type" => "Foodstall", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "9f13f13e-0134-4cd0-a77f-1b71f7e5229d", "name" => "Mie Baso", "price" => "0", "category_id" => "f2381588-9e5f-489d-af72-92248ef0cd54", "type" => "Foodstall", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "bf143316-d224-4b94-b5b7-45b63ed23043", "name" => "Mie Kocok", "price" => "0", "category_id" => "f2381588-9e5f-489d-af72-92248ef0cd54", "type" => "Foodstall", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "6dccdc1f-12f1-4bbe-b606-d8e67281cfab", "name" => "Zoupa Soup", "price" => "0", "category_id" => "f2381588-9e5f-489d-af72-92248ef0cd54", "type" => "Foodstall", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "b3c343ca-c0f2-4371-80ce-bd94127bc1be", "name" => "Spaghetti", "price" => "0", "category_id" => "f2381588-9e5f-489d-af72-92248ef0cd54", "type" => "Foodstall", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "43468b65-fca5-4a1a-860a-c0bb34f5290c", "name" => "Sate Lontong", "price" => "0", "category_id" => "f2381588-9e5f-489d-af72-92248ef0cd54", "type" => "Foodstall", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "a26f4868-dfcd-4ec0-8f8c-c6ca41d35964", "name" => "Kebab", "price" => "0", "category_id" => "f2381588-9e5f-489d-af72-92248ef0cd54", "type" => "Foodstall", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "17914feb-6860-4058-83d8-5828f23fe841", "name" => "Pempek", "price" => "0", "category_id" => "f2381588-9e5f-489d-af72-92248ef0cd54", "type" => "Foodstall", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "dd1dfd7c-4aa8-4d51-93c6-d349f2859c57", "name" => "Kambing Guling", "price" => "3000000", "category_id" => "f2381588-9e5f-489d-af72-92248ef0cd54", "type" => "Foodstall", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "7502cbf7-7777-439f-a8f1-497f2d1c7721", "name" => "Martabak Telor", "price" => "6000", "category_id" => "f2381588-9e5f-489d-af72-92248ef0cd54", "type" => "Foodstall", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "2d5e4a83-d90b-4089-9fa6-e886c23571cd", "name" => "Kebab", "price" => "10000", "category_id" => "f2381588-9e5f-489d-af72-92248ef0cd54", "type" => "Foodstall", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
                ["uuid" => "d0d840e5-dd57-46a8-af50-922d4559311b", "name" => "Es Cream", "price" => "0", "category_id" => "f2381588-9e5f-489d-af72-92248ef0cd54", "type" => "Foodstall", "description" => "-", "image_id" => "d30e0078-01f1-4d66-b32f-a7a6105deeec"],
            ];
        foreach ($data as $item) {
            MenuModel::create($item);
        }
    }
}

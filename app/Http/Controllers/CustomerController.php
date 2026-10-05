<?php

namespace App\Http\Controllers;

use App\Models\CustomerModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ActivityHelper;

class CustomerController extends Controller
{
    private $customerModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
    }

    public function index()
    {
        $data = $this->customerModel->getAll();
        if (!$data) return response()->failed($data, 404);
        return response()->success($data, 200);
    }

    public function show($uuid)
    {
        $data = $this->customerModel->getById($uuid);
        if (!$data['data']) {
            return response()->failed('Customer not found', 404);
        }

        return response()->success($data, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone_number_1' => 'required',
            'phone_number_2' => 'required',
            'address' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }
        $data = $this->customerModel->store($validator->validated());
        ActivityHelper::log('Membuat Customer Baru');

        return response()->success($data, 200);
    }

    public function update($uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone_number_1' => 'required',
            'phone_number_2' => 'required',
            'address' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $checkExist = $this->customerModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Customer not found', 404);
        }

        $update = $this->customerModel->edit($validator->validated(), $uuid);
        if ($update) {
            ActivityHelper::log('Mengubah Customer');
            $data = $this->customerModel->getById($uuid);
            return response()->success($data, 200);
        }
        return response()->failed('Something error', 422);
    }

    public function destroy($uuid)
    {
        $checkExist = $this->customerModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Customer not found', 404);
        }

        $data = $this->customerModel->drop($uuid);

        if ($data['status']) {
            ActivityHelper::log('Menghapus Customer');
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    public function search($name)
    {
        try {
            $customer = $this->customerModel->getByName($name);

            return response()->success($customer['data'], 200);
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 422);
        }
    }
}

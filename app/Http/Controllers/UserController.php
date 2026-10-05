<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ActivityHelper;
use App\Models\ActivityModel;
use App\Models\FinancialRecordModel;
use App\Models\MeetingScheduleModel;
use App\Models\OrdersCustomersModel;
use App\Models\PaymentsModel;
use App\Models\RolesModel;
use App\Models\UserModel;
use App\Models\StaffModel;
use App\Models\TestimoniModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $staffModel;
    private $meetModel;
    private $orderModel;
    private $paymentModel;
    private $financialRecordModel;
    private $testimonialModel;
    private $userModel;
    private $roleModel;
    private $activityModel;

    public function __construct()
    {
        $this->staffModel = new StaffModel();
        $this->meetModel = new MeetingScheduleModel();
        $this->orderModel = new OrdersCustomersModel();
        $this->paymentModel = new PaymentsModel();
        $this->financialRecordModel = new FinancialRecordModel();
        $this->testimonialModel = new TestimoniModel();
        $this->userModel = new UserModel();
        $this->roleModel = new RolesModel();
        $this->activityModel = new ActivityModel();
    }

    // user SU-side
    public function indexUser()
    {
        try {
            $users = $this->userModel->getAll();
            return response()->success($users, 200);
        } catch (\Exception $e) {
            return response()->failed($e->getMessage(), 505);
        }
    }

    public function storeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'username' => 'required|string|unique:users',
            'password' => 'required|min:8',
            'phone_number' => 'nullable|string',
            'role_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $validatedData = $validator->validated();

        try {
            ActivityHelper::log('Membuat User Baru');
            $user = $this->userModel->store([
                'name'        => $validatedData['name'],
                'username'    => $validatedData['username'],
                'email'       => $validatedData['email'],
                'password'    => Hash::make($validatedData['password']),
                'phone_number' => $validatedData['phone_number'],
                'role_id'        => $validatedData['role_id'],
            ]);
            return response()->success($user, 200);
        } catch (\Exception $e) {
            return response()->failed($e->getMessage(), 505);
        }
    }

    public function showUser($uuid)
    {
        try {
            $user = $this->userModel->getById($uuid);
            if (!$user['data']) {
                return response()->failed('user not found', 404);
            }
            return response()->success($user, 200);
        } catch (\Exception $e) {
            return response()->failed($e->getMessage(), 505);
        }
    }
    public function updateUser($uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $uuid . ',uuid',
            'email' => 'required|email|max:255|unique:users,email,' . $uuid . ',uuid',
            'phone_number' => 'nullable|string|max:20',
            'role_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 422);
        }
        $validatedData = $validator->validated();

        $user = $this->userModel->getById($uuid);
        if (!$user['data']) {
            return response()->failed('user not found', 404);
        }

        try {
            $update = $this->userModel->edit([
                'name' => $validatedData['name'],
                'username' => $validatedData['username'],
                'email' => $validatedData['email'],
                'phone_number' => $validatedData['phone_number'],
                'role_id' => $validatedData['role_id']
            ], $uuid);

            ActivityHelper::log('update user profile');
            $user = $this->userModel->getById($uuid);
            return response()->success($user, 200);
        } catch (\Exception $e) {
            return response()->failed($e->getMessage(), 505);
        }
    }

    public function updatePasswordByAdmin($uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $checkExistUser = $this->userModel->getById($uuid);
        if (!$checkExistUser['data']) {
            return response()->failed('user not found', 404);
        }
        try {
            $user = $this->userModel->edit([
                'password' => Hash::make($request->new_password),
            ], $uuid);
            ActivityHelper::log('Update user password');
            return response()->success($user, 200);
        } catch (\Exception $e) {
            return response()->failed($e->getMessage(), 505);
        }
    }

    public function updateUserRole($uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 422);
        }

        $checkExistUser = $this->userModel->getById($uuid);
        if (!$checkExistUser['data']) {
            return response()->failed('user not found', 404);
        }

        $validatedData = $validator->validated();

        try {

            $user = $this->userModel->edit([
                'role_id' => $validatedData['role_id'],
            ], $uuid);
            ActivityHelper::log('Update user role');
            $user = $this->userModel->getById($uuid);
            return response()->success($user, 200);
        } catch (\Exception $e) {
            return response()->failed($e->getMessage(), 505);
        }
    }

    public function reactiveUser($uuid)
    {
        $checkExistUser = $this->userModel->getById($uuid);
        if (!$checkExistUser['data']) {
            return response()->failed('user not found', 404);
        }

        try {
            $user = $this->userModel->edit([
                'is_active' => true,
            ], $uuid);
            ActivityHelper::log('User re-active account');
            $user = $this->userModel->getById($uuid);
            return response()->success($user, 200);
        } catch (\Exception $e) {
            return response()->failed($e->getMessage(), 505);
        }
    }

    public function suspendUser($uuid)
    {
        $checkExistUser = $this->userModel->getById($uuid);
        if (!$checkExistUser['data']) {
            return response()->failed('user not found', 404);
        }

        try {
            $user = $this->userModel->edit([
                'is_active' => false,
            ], $uuid);

            ActivityHelper::log('User suspend account');
            $user = $this->userModel->getById($uuid);
            return response()->success($user, 200);
        } catch (\Exception $e) {
            return response()->failed($e->getMessage(), 505);
        }
    }


    // user User-side
    public function indexProfile()
    {
        try {
            $user = Auth::user();
            $result = $this->userModel->getById($user->uuid);
            if (!$result['data']) {
                return response()->failed('user not found', 404);
            }
            return response()->success($result, 200);
        } catch (\Exception $e) {
            return response()->failed($e->getMessage(), 505);
        }
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 422);
        }

        try {
            $user->update([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
            ]);

            ActivityHelper::log('User update profile');
            return response()->success($user, 200);
        } catch (\Exception $e) {
            return response()->failed($e->getMessage(), 505);
        }
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password does not match'
            ], 400);
        }

        try {
            ActivityHelper::log('User update password');
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);
            return response()->success($user, 200);
        } catch (\Exception $e) {
            return response()->failed($e->getMessage(), 505);
        }
    }

    public function indexActivities(Request $request)
    {
        try {
            $filter = [
                'user_id' => $request->user_id ?? '',
            ];

            $activities = $this->activityModel->getAll($filter);
            return response()->success($activities, 200);
        } catch (\Exception $e) {
            return response()->failed($e->getMessage(), 505);
        }
    }

    public function showActivity($uuid)
    {
        try {
            $activity = $this->activityModel->getById($uuid);
            if (!$activity['data']) {
                return response()->failed('Activity not found', 404);
            }
            return response()->success($activity, 200);
        } catch (\Throwable $th) {
            return response()->failed($th->getMessage(), 505);
        }
    }

    // role
    public function indexRole()
    {
        try {
            // ponytail: cache list role selama 1 jam karena data sangat statis.
            $data = \Illuminate\Support\Facades\Cache::remember('user_roles_list', 3600, function () {
                return $this->roleModel->getAll();
            });
            return response()->success($data, 200);
        } catch (\Exception $e) {
            return response()->failed($e->getMessage(), 505);
        }
    }

    public function showRole($uuid)
    {
        try {
            $data = $this->roleModel->getById($uuid);
            if (!$data['data']) {
                return response()->failed('Role not found', 404);
            }
            return response()->success($data, 200);
        } catch (\Exception $e) {
            return response()->failed($e->getMessage(), 505);
        }
    }

    // staff
    public function indexStaff()
    {
        $data = $this->staffModel->getAll();

        if (!$data['data']) return response()->failed($data, 404);
        return response()->success($data, 200);
    }

    public function storeStaff(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone_number' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->failed('Invalid input', 400);
        }
        $validatedData = $validator->validated();
        $crtData = $this->staffModel->store($validatedData)['data'];
        ActivityHelper::log('Membuat Staff Baru');
        return response()->success($crtData, 200);
    }

    public function showStaff($uuid)
    {
        $data = $this->staffModel->getById($uuid);
        if (!$data['data']) {
            return response()->failed('Staff not found', 404);
        }

        return response()->success($data, 200);
    }

    public function updateStaff($uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone_number' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->failed('Invalid input', 400);
        }

        $checkExist = $this->staffModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Staff not found', 404);
        }

        $update = $this->staffModel->edit($validator->validated(), $uuid);

        if ($update) {
            $data = $this->staffModel->getById($uuid);
            ActivityHelper::log('Mengubah Staff');
            return response()->success($data, 200);
        }
        return response()->failed('Something error', 422);
    }

    public function destroyStaff($uuid)
    {
        $checkExist = $this->staffModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Staff not found', 404);
        }

        $data = $this->staffModel->drop($uuid);

        if ($data['status']) {
            ActivityHelper::log('Menghapus Staff');
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    public function indexMeet(Request $request)
    {
        $filter = [
            'title' => $request->title ?? null,
            'event_date_start' => $request->event_date_start ?? null,
            'event_date_end' => $request->event_date_end ?? null,
            'status' => $request->status ?? null,
            'location' => $request->location ?? null,
        ];
        $data = $this->meetModel->getAll($filter);
        if (!$data) return response()->failed($data, 404);
        return response()->success($data, 200);
    }

    public function showMeet($uuid)
    {
        $data = $this->meetModel->getById($uuid);
        if (!$data['data']) {
            return response()->failed('Meeting schedule not found', 404);
        }

        return response()->success($data, 200);
    }

    public function storeMeet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'date' => 'required',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'location' => 'nullable',
            'note' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }
        $data = $this->meetModel->store($validator->validated());
        if ($data['status']) {
            ActivityHelper::log('Membuat Meeting Schedule Baru');
            return response()->success($data, 200);
        }
        return response()->failed('Something error', 422);
    }

    public function updateMeet($uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'date' => 'required',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'location' => 'nullable',
            'note' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $checkExist = $this->meetModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Meeting schedule not found', 404);
        }

        $update = $this->meetModel->edit($validator->validated(), $uuid);
        if ($update) {
            ActivityHelper::log('Mengubah Customer');
            $data = $this->meetModel->getById($uuid);
            return response()->success($data, 200);
        }
        return response()->failed('Something error', 422);
    }

    public function destroyMeet($uuid)
    {
        $checkExist = $this->meetModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Customer not found', 404);
        }

        $data = $this->meetModel->drop($uuid);

        if ($data['status']) {
            ActivityHelper::log('Menghapus Customer');
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }

    public function dashboardSuperAdmin(Request $request)
    {
        $filter = [
            'event_date_start' => $request->event_date_start ?? now(),
            'event_date_end' => $request->event_date_end ?? now()->addDays(7),
        ];

        $dataOrder = $this->orderModel->getAll($filter)['data'];
        // dari acara yang akan berlangsung
        $dataDP = $this->paymentModel->getDPFromPayment($filter)['data'];

        $totalDP = 0;
        $paymentTotal = 0;
        foreach ($dataDP as $item) {
            $paymentTotal += $item['amount'];
            foreach ($item['installment'] as $itemAmount) {
                $totalDP += $itemAmount['amount'];
            }
        }

        //  pengeluaran perminggu dari acara
        $dataExpense = $this->financialRecordModel->getExpense($filter)['data'];

        $totalExpense = 0;
        foreach ($dataExpense as $item) {
            $totalExpense += $item['amount'];
        }

        //  selisih harga DP dengan uang yang seharusnya didapat(kurang berapa)
        $paymentGap = $totalDP - $paymentTotal;

        $data =  [
            'status' => true,
            'data' => [
                'order' => $dataOrder,
                'totalDP' => $totalDP,
                'totalExpense' => $totalExpense,
                'paymentGap' => $paymentGap,
            ]
        ];
        return response()->success($data, 200);
    }

    public function dashboardAdmin(Request $request)
    {
        $filter = [
            'event_date_start' => $request->event_date_start ?? now(),
            'event_date_end' => $request->event_date_end ?? now()->addDays(7),
        ];

        // acara berdasarkan bulan,
        $dataOrder = $this->orderModel->getAll($filter)['data'];

        // kolom kalender jadwal meeting,
        $dataMeeting = $this->meetModel->getAll($filter);

        // customer belum lunas,
        // belum lunas H-10 dari acara,
        if (!$request->event_date_end) {
            $filter = [
                'event_date_start' => $request->event_date_start ?? now(),
                'event_date_end' => $request->event_date_end ?? now()->addDays(10),
            ];
        }

        $dataPayment = $this->paymentModel->getDPFromPayment($filter)['data'];
        foreach ($dataPayment as $payment) {

            $totalInstallmentAmount = 0;
            foreach ($payment->installment as $installment) {
                $totalInstallmentAmount += $installment->amount;
            }
            $remainingAmount = $totalInstallmentAmount - $payment->amount;
            $payment['remaining_amount'] = $remainingAmount;
        }

        // data barang gudang(belum),
        // data pegawai ada 2 harian dan tetap
        $dataStaff = $this->staffModel->getAll()['data'];

        $data =  [
            'status' => true,
            'data' => [
                'order' => $dataOrder,
                'meeting' => $dataMeeting,
                'unpaid' => $dataPayment,
                'staff' => $dataStaff,
            ]
        ];
        return response()->success($data, 200);
    }

    public function dashboardFinance(Request $request)
    {
        $filter = [
            'event_date_start' => $request->event_date_start ?? now(),
            'event_date_end' => $request->event_date_end ?? now()->addDays(7),
        ];

        $dataOrder = $this->orderModel->getAll($filter)['data'];
        // dari acara yang akan berlangsung
        $dataDP = $this->paymentModel->getDPFromPayment($filter)['data'];
        $totalDP = 0;
        $paymentTotal = 0;
        foreach ($dataDP as $item) {
            $paymentTotal += $item['amount'];
            foreach ($item['installment'] as $itemAmount) {
                $totalDP += $itemAmount['amount'];
            }
        }

        //  pengeluaran perminggu dari acara
        $dataExpense = $this->financialRecordModel->getExpense($filter)['data'];
        $totalExpense = 0;
        foreach ($dataExpense as $item) {
            $totalExpense += $item['amount'];
        }

        //  selisih harga DP dengan uang yang seharusnya didapat(kurang berapa)
        $paymentGap = $totalDP - $paymentTotal;

        // kolom kalender jadwal meeting,
        $dataMeeting = $this->meetModel->getAll($filter);

        if (!$request->event_date_end) {
            $filter = [
                'event_date_start' => $request->event_date_start ?? now(),
                'event_date_end' => $request->event_date_end ?? now()->addDays(10),
            ];
        }

        $dataPayment = $this->paymentModel->getDPFromPayment($filter)['data'];
        foreach ($dataPayment as $payment) {

            $totalInstallmentAmount = 0;
            foreach ($payment->installment as $installment) {
                $totalInstallmentAmount += $installment->amount;
            }
            $remainingAmount = $totalInstallmentAmount - $payment->amount;
            $payment['remaining_amount'] = $remainingAmount;
        }

        $data =  [
            'status' => true,
            'data' => [
                'order' => $dataOrder,
                'totalDP' => $totalDP,
                'totalExpense' => $totalExpense,
                'paymentGap' => $paymentGap,
                'meeting' => $dataMeeting,
                'unpaid' => $dataPayment,
            ]
        ];
        return response()->success($data, 200);
    }

    // testimoni
    public function indexTestimonial()
    {
        $data = $this->testimonialModel->getAll();
        if (!$data['status']) return response()->failed($data, 404);
        return response()->success($data, 200);
    }

    public function storeTestimonial(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'rating' => 'nullable',
            'testimonial' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }
        $validatedData = $validator->validated();
        if (isset($validatedData['rating'])) {
            $validatedData['is_filled'] = true;
        } else {
            $validatedData['is_filled'] = false;
        }

        $result = $this->testimonialModel->store($validatedData)['data'];
        $data = $this->testimonialModel->getById($result['uuid']);
        ActivityHelper::log('Membuat Testimoni Baru');
        return response()->success($data, 200);
    }

    public function showTestimonial($uuid)
    {
        $data = $this->testimonialModel->getById($uuid);
        if (!$data['data']) {
            return response()->failed('Testimonial not found', 404);
        }

        return response()->success($data, 200);
    }

    public function updateTestimonial($uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable',
            'rating' => 'nullable',
            'testimonial' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $checkExist = $this->testimonialModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Testimonial not found', 404);
        }

        $validatedData = $validator->validated();
        $validatedData['uuid'] = $uuid;
        if (isset($validatedData['rating'])) {
            $validatedData['is_filled'] = true;
        } else {
            $validatedData['is_filled'] = false;
        }

        $update = $this->testimonialModel->edit($validatedData, $uuid);
        ActivityHelper::log('Mengubah Testimoni');
        if ($update) {
            $data = $this->testimonialModel->getById($uuid);
            return response()->success($data, 200);
        }
        return response()->failed('Something error', 422);
    }

    public function updateTestimonialCustomer($uuid, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required',
            'testimonial' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->failed($validator->errors(), 400);
        }

        $checkExist = $this->testimonialModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Testimonial not found', 404);
        } elseif ($checkExist['data']['is_filled'] == true) {
            return response()->failed('Testimonials already filled', 409);
        }

        $validatedData = $validator->validated();
        $validatedData['uuid'] = $uuid;
        if (isset($validatedData['rating'])) {
            $validatedData['is_filled'] = true;
        } else {
            $validatedData['is_filled'] = false;
        }

        $update = $this->testimonialModel->edit($validatedData);
        if ($update['status']) {
            $data = $this->testimonialModel->getById($uuid);
            return response()->success($data, 200);
        }
        return response()->failed('Something error', 422);
    }

    public function destroyTestimonial($uuid)
    {
        $checkExist = $this->testimonialModel->getById($uuid);
        if (!$checkExist['data']) {
            return response()->failed('Testimonial not found', 404);
        }

        $data = $this->testimonialModel->drop($uuid);
        ActivityHelper::log('Menghapus Testimoni');
        if ($data['status']) {
            return response()->success('Delete success', 200);
        }
        return response()->failed('Something error', 422);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\MPasswordreset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\UserModel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use App\Helpers\ActivityHelper;


class AuthController extends Controller
{


    public function login(Request $request)
    {
        try {
            // Temukan pengguna dengan email atau username
            $user = UserModel::where('email', $request->namemail)
                ->orWhere('username', $request->namemail)
                ->with('role')
                ->first();

            // Periksa jika pengguna ditemukan dan password cocok
            if (!$user || !Auth::attempt(['email' => $user->email, 'password' => $request->password])) {
                return response()->failed('Pastikan Email atau Username dan Password Anda benar', 401);
            }

            // Periksa jika akun tidak aktif
            if (!$user->is_active) { // Misalkan kolomnya bernama is_active
                return response()->failed('Akun telah dibekukan, silahkan hubungi admin', 401);
            }

            // Buat token jika login berhasil
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->success([
                'user' => $user,
                'access_token' => $token,
            ], 200);
        } catch (\Exception $e) {
            return response()->failed($e->getMessage(), 505);
        }
    }

    public function sendForgotPassword(Request $request)
    {
        require base_path("vendor/autoload.php");

        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => [],
                'message' => $validator->errors(),
                'success' => false
            ]);
        }
        $validatordata = $validator->validated();
        MPasswordreset::where('email', $validatordata['email'])->delete();

        $tokenrandom = Str::random(70);
        $linkreset = "<a href='http://127.0.0.1:1234/api/forgotpassword/" . $tokenrandom . "'>Reset Password</a>";

        try {
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host       = 'mail.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'official@andikasari.com';
            $mail->Password   = '@And1k4321';
            $mail->SMTPSecure = 'ssl';
            $mail->Port       = '465';

            $mail->IsHTML(TRUE);
            $mail->setFrom('official@andikasari.com', 'CS Andika Sari');
            $mail->addAddress($validatordata['email']);

            $mail->Subject = 'Password Reset';
            $mail->Body    = 'A request for forgot password has been made. If you have not made this request, please ignore this email. If you have made this request, please click on the link below to reset your password. <br>' . $linkreset;
            $mail->AltBody = 'reset password';

            if (!$mail->send()) {
                return response()->json([
                    'status' => false,
                    'message' => "Data Tidak terikirim",
                ]);
            } else {
                $emailToken = MPasswordreset::create([
                    'email' => $validatordata['email'],
                    'token' => $tokenrandom,
                ]);
                return response()->json([
                    'message' => true,
                    'data' => $emailToken,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal',
                'success' => true,
                'data' => $e
            ]);
        }
    }
    public function Forgotpassword(Request $request, $token)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'data' => [],
                'message' => $validator->errors(),
                'success' => false
            ]);
        }
        $validatordata = $validator->validated();
        //Cek-token
        $user = MPasswordreset::where('token', $token)
            ->orderBy('created_at', 'desc')->first();

        try {
            if (!$user) {
                return response()->json([
                    'message' => 'Link Reset Expired.',
                    'success' => false
                ]);
            } else {
                $user = UserModel::where('email', $user['email'])->firstOrFail();
                $user->update([
                    'password' => Hash::make($validatordata['password'])
                ]);
                MPasswordreset::where('email', $user['email'])->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Password Berhasil DiUpdate.',
                    'data' => $user
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal',
                'success' => true,
                'data' => $e
            ]);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Mendapatkan token pengguna yang sedang aktif
            $user = $request->user();

            // Revoke (batalkan) semua token pengguna saat ini
            $user->tokens()->delete();

            return response()->success('Logout berhasil', 200);
        } catch (\Exception $e) {
            return response()->failed('Terjadi kesalahan saat logout: ' . $e->getMessage(), 505);
        }
    }
}

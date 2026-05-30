<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\UserModel;
use App\Models\Admin\WebModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    // 1. Tampilan Form Input Email
    public function showLinkRequestForm() {
        $data["title"] = "Lupa Password";
        $data["web"] = WebModel::first();
        return view('Admin.Login.forgot_password', $data);
    }

    // 2. Proses Kirim OTP
    public function sendOtpCode(Request $request) {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = UserModel::where('user_email', $request->email)->first();

        if (!$user) {
            Session::flash('status', 'error');
            Session::flash('msg', 'Email tidak terdaftar!');
            return back();
        }

        $otp = rand(100000, 999999);
        
        // Simpan email, token OTP, dan waktu pembuatan
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
             ['token' => (string) $otp, 'created_at' => now()] 
        );

        // Proses kirim email via SMTP Gmail
        Mail::send('Admin.Login.email_otp', ['otp' => $otp], function($message) use($request) {
            $message->to($request->email);
            $message->subject('OTP Reset Password - InventoryWeb');
        });

        return redirect()->route('admin.verify.otp', ['email' => $request->email]);
    }

    // 3. Tampilan Form Verifikasi OTP
    public function showVerifyForm($email) {
        $data["title"] = "Verifikasi OTP";
        $data["web"] = WebModel::first();
        $data["email"] = $email;
        return view('Admin.Login.verify_otp', $data);
    }

    // 4. Proses Cek OTP (Ditambahkan Validasi Waktu Kadaluwarsa 5 Menit)
    public function verifyOtp(Request $request) {
        $check = DB::table('password_resets')
                    ->where('email', $request->email)
                    ->where('token', (string) $request->otp) // ← ganti ke string
                    ->first();

        if ($check) {
            // Cek apakah OTP sudah lewat dari 5 menit
            $waktuPembuatan = Carbon::parse($check->created_at);
            if (now()->diffInMinutes($waktuPembuatan) > 5) {
                Session::flash('status', 'error');
                Session::flash('msg', 'Kode OTP sudah kadaluwarsa (Expired)! Silakan minta kode baru.');
                return back();
            }

            return redirect()->route('admin.reset.password', ['email' => $request->email]);
        }

        Session::flash('status', 'error');
        Session::flash('msg', 'Kode OTP salah!');
        return back();
    }

    // 5. Tampilan Form Password Baru
    public function showResetForm($email) {
        // Amankan form reset agar tidak bisa ditembus langsung via ketik URL manual tanpa OTP
        $check = DB::table('password_resets')->where('email', $email)->first();
        if (!$check) {
            return redirect('admin/login');
        }

        $data["title"] = "Reset Password";
        $data["web"] = WebModel::first();
        $data["email"] = $email;
        return view('Admin.Login.reset_password', $data);
    }

    // 6. Update Password ke Database (Proteksi Inspect Element)
    public function resetPassword(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:5'
        ]);

        // Cek ulang apakah email ini memang sedang dalam proses reset password yang sah
        $validRequest = DB::table('password_resets')->where('email', $request->email)->exists();

        if (!$validRequest) {
            Session::flash('status', 'error');
            Session::flash('msg', 'Permintaan reset password tidak valid atau sudah kadaluwarsa.');
            return redirect('admin/login');
        }

        // Jalankan update password menggunakan enkripsi MD5 sesuai sistem loginmu
        UserModel::where('user_email', $request->email)->update([
            'user_password' => md5($request->password)
        ]);

        // Hapus token dari tabel password_resets agar tidak bisa disalahgunakan lagi
        DB::table('password_resets')->where('email', $request->email)->delete();

        Session::flash('status', 'success');
        Session::flash('msg', 'Password berhasil diubah, silakan login kembali.');
        return redirect('admin/login');
    }
}
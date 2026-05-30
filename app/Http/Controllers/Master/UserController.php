<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Admin\RoleModel;
use App\Models\Admin\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        $data["title"] = "User";
        $data["role"] = RoleModel::latest()->get();
        return view('Master.User.index', $data);
    }

    public function profile(UserModel $user)
    {
        $data["title"] = "Profile";
        $data["data"] = UserModel::leftJoin('tbl_role', 'tbl_role.role_id', '=', 'tbl_user.role_id')->select()->where('tbl_user.user_id', '=', $user->user_id)->first();
        return view('Master.User.profile', $data);
    }

public function show(Request $request)
{
    if ($request->ajax()) {
        $data = UserModel::leftJoin('tbl_role', 'tbl_role.role_id', '=', 'tbl_user.role_id')
                ->select('tbl_user.*', 'tbl_role.role_title')
                ->orderBy('user_id', 'DESC')
                ->get();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('img', function ($row) {
                // Jalur folder assets
                $url = ($row->user_foto == "undraw_profile.svg" || $row->user_foto == "") 
                       ? url('/assets/default/users/undraw_profile.svg') 
                       : url('/assets/default/users/' . $row->user_foto);

                // Style lingkaran sempurna
                return '<div class="text-center">
                            <img src="' . $url . '" 
                                 class="rounded-circle" 
                                 style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #ddd;">
                        </div>';
            })
            ->addColumn('role', function ($row) {
                return '<span class="badge bg-primary">' . $row->role_title . '</span>';
            })
            ->addColumn('action', function ($row) {
                // Pembersihan karakter aneh agar JSON tidak rusak
                $array = [
                    "user_id" => $row->user_id,
                    "user_nama" => str_replace('"', '', $row->user_nama),
                    "user_nmlengkap" => str_replace('"', '', $row->user_nmlengkap),
                    "user_foto" => $row->user_foto,
                    "role_id" => $row->role_id,
                    "user_email" => $row->user_email
                ];
                
                return '
                <div class="g-2 text-center">
                    <a class="btn btn-sm text-primary" data-bs-toggle="modal" href="#Umodaldemo8" onclick=\'update(' . json_encode($array) . ')\'><span class="fe fe-edit text-success fs-14"></span></a>
                    <a class="btn btn-sm text-danger" data-bs-toggle="modal" href="#Hmodaldemo8" onclick=\'hapus(' . json_encode($array) . ')\'><span class="fe fe-trash-2 fs-14"></span></a>
                </div>';
            })
            ->rawColumns(['action', 'img', 'role'])
            ->make(true);
    }
}
    public function store(Request $request)
{
    $img = "undraw_profile.svg";

    if ($request->hasFile('photo')) {
        $image = $request->file('photo');
        $nama_file = time() . "_" . $image->getClientOriginalName();
        // Simpan ke assets agar muncul di tabel
        $image->move(public_path('assets/default/users'), $nama_file);
        $img = $nama_file;
    }

    UserModel::create([
        'user_foto' => $img,
        'user_nmlengkap' => $request->nmlengkap,
        'user_nama' => $request->username,
        'user_email' => $request->email,
        'role_id' => $request->role,
        'user_password' => md5($request->pwd)
    ]);

    Session::flash('status', 'success');
    Session::flash('msg', 'Berhasil ditambah!');
    return redirect()->route('user.index');
}
 public function update(Request $request, $id) // Gunakan $id agar lebih fleksibel
{
    $user = UserModel::findOrFail($id); // Cari user berdasarkan ID yang dikirim dari form

    if ($request->hasFile('photoU')) {
        $image = $request->file('photoU');
        $nama_file = time() . "_" . $image->getClientOriginalName();

        // Hapus foto lama di assets agar folder tidak penuh
        if ($user->user_foto && $user->user_foto != 'undraw_profile.svg') {
            $pathLama = public_path('assets/default/users/' . $user->user_foto);
            if (file_exists($pathLama)) { 
                unlink($pathLama); 
            }
        }

        // Pindahkan ke public/assets agar sinkron dengan List User
        $image->move(public_path('assets/default/users'), $nama_file);
        $user->user_foto = $nama_file;
    }

    // Sesuaikan dengan name="nmlengkapU" yang ada di modal ubah Anda
    $user->user_nmlengkap = $request->nmlengkapU;
    $user->user_nama = $request->usernameU;
    $user->user_email = $request->emailU;
    $user->role_id = $request->roleU;

    // Jika password diisi (tidak kosong), maka update passwordnya
    if ($request->pwdU != '') {
        $user->user_password = md5($request->pwdU);
    }
// Tambahkan ini sebelum return jika yang diupdate adalah user yang sedang login
if ($user->user_id == Session::get('user')->user_id) {
    Session::put('user', $user);
}
    $user->save();
    
    Session::flash('status', 'success');
    Session::flash('msg', 'Data User Berhasil diubah!');
    
    return redirect()->route('user.index');
}
    public function updatePassword(Request $request, UserModel $user)
    {
        $checkPassword = UserModel::where(array('user_id' => $user->user_id, 'user_password' => md5($request->currentpassword)))->count();
        if ($checkPassword > 0) {
            $user->update([
                'user_password' => md5($request->newpassword)
            ]);
            Session::flash('status', 'success');
            Session::flash('msg', 'Password berhasil di ubah!');
        } else {
            Session::flash('status', 'error');
            Session::flash('msg', 'Password saat ini tidak sama dengan password lama!');
            Session::flash('currentpassword', $request->currentpassword);
            Session::flash('newpassword', $request->newpassword);
            Session::flash('confirmpassword', $request->confirmpassword);
        }

        $data['title'] = "Profile";
        //redirect to index
        return redirect(url('admin/profile/' . $user->user_id))->with($data);
    }

 public function updateProfile(Request $request, UserModel $user)
{
    // 1. Cek jika ada file foto yang diunggah
    if ($request->hasFile('photoU')) {

        // Ambil file dan buat nama unik
        $image = $request->file('photoU');
        $nama_file = time() . "_" . $image->getClientOriginalName();

        // 2. Hapus foto lama di folder assets (jika ada dan bukan foto default)
        if ($user->user_foto && $user->user_foto != 'undraw_profile.svg') {
            $pathLama = public_path('assets/default/users/' . $user->user_foto);
            if (file_exists($pathLama)) {
                unlink($pathLama); // Hapus file fisik
            }
        }

        // 3. Pindahkan foto baru ke public/assets/default/users
        $image->move(public_path('assets/default/users'), $nama_file);

        // 4. Update database dengan nama file baru
        $user->update([
            'user_foto'      => $nama_file,
            'user_nmlengkap' => $request->nmlengkap,
            'user_nama'      => $request->username,
            'user_email'     => $request->email,
        ]);
    } else {
        // Update tanpa mengganti foto
        $user->update([
            'user_nmlengkap' => $request->nmlengkap,
            'user_nama'      => $request->username,
            'user_email'     => $request->email,
        ]);
    }

    $data['title'] = "Profile";
    Session::put('user', $user);
    Session::flash('status', 'success');
    Session::flash('msg', 'Profile Berhasil diubah!');

    return redirect(url('admin/profile/' . $user->user_id))->with($data);
}
// ================= HAPUS =================
    public function hapus(Request $request)
    {
        // 1. Cari data user berdasarkan iduser yang dikirim form
        $user = UserModel::findOrFail($request->iduser);

        // 2. Samakan jalur folder penghapusan dengan public_path asset kamu
        if ($user->user_foto && $user->user_foto != 'undraw_profile.svg') {
            $pathFoto = public_path('assets/default/users/' . $user->user_foto);
            
            // Cek secara fisik apakah file fotonya benar-benar ada di folder public
            if (file_exists($pathFoto)) { 
                unlink($pathFoto); // Hapus file foto secara permanen
            }
        }

        // 3. Hapus data user dari database (panggil dari variabel yang sudah dicari di atas)
        $user->delete();

        Session::flash('status', 'success');
        Session::flash('msg', 'Berhasil dihapus!');

        // redirect ke index halaman user
        return redirect()->route('user.index');
    }
}
<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Admin\WebModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class WebController extends Controller
{
    public function index()
    {
        $data["title"] = "Web";
        $data["data"] = WebModel::all();
        return view('Master.Web.index', $data);
    }

 public function update(Request $request, WebModel $web)
{
    // Cek jika ada file logo yang diunggah
    if ($request->hasFile('photo')) {

        $image = $request->file('photo');
        $nama_logo = "logo_" . time() . "." . $image->getClientOriginalExtension();

        // 1. Hapus logo lama di folder assets (jika bukan default)
        if ($web->web_logo && $web->web_logo != 'default.png') {
            $pathLama = public_path('assets/default/web/' . $web->web_logo);
            if (file_exists($pathLama)) {
                unlink($pathLama);
            }
        }

        // 2. Pindahkan logo baru ke public/assets/default/web
        $image->move(public_path('assets/default/web'), $nama_logo);

        // 3. Update database dengan nama file baru
        $web->update([
            'web_logo' => $nama_logo,
            'web_nama' => $request->nmweb,
            'web_deskripsi' => $request->desk,
        ]);
    } else {
        // Update tanpa mengganti logo
        $web->update([
            'web_nama' => $request->nmweb,
            'web_deskripsi' => $request->desk,
        ]);
    }

    $data['title'] = "Web";
    Session::flash('status', 'success');
    Session::flash('msg', 'Pengaturan Web berhasil diubah!');

    return redirect()->route('web.index')->with($data);
}
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AksesModel;
use App\Models\Admin\BarangkeluarModel;
use App\Models\Admin\BarangmasukModel;
use App\Models\Admin\BarangModel;
use App\Models\Admin\JenisBarangModel;
use App\Models\Admin\MerkModel;
use App\Models\Admin\SatuanModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class BarangController extends Controller
{
    public function index()
    {
        $data["title"] = "Barang";
        $data["hakTambah"] = AksesModel::leftJoin('tbl_submenu', 'tbl_submenu.submenu_id', '=', 'tbl_akses.submenu_id')->where(array('tbl_akses.role_id' => Session::get('user')->role_id, 'tbl_submenu.submenu_judul' => 'Barang', 'tbl_akses.akses_type' => 'create'))->count();
        $data["jenisbarang"] =  JenisBarangModel::orderBy('jenisbarang_id', 'DESC')->get();
        $data["satuan"] =  SatuanModel::orderBy('satuan_id', 'DESC')->get();
        $data["merk"] =  MerkModel::orderBy('merk_id', 'DESC')->get();
        return view('Admin.Barang.index', $data);
    }

    public function getbarang($id)
    {
        $data = BarangModel::leftJoin('tbl_jenisbarang', 'tbl_jenisbarang.jenisbarang_id', '=', 'tbl_barang.jenisbarang_id')->leftJoin('tbl_satuan', 'tbl_satuan.satuan_id', '=', 'tbl_barang.satuan_id')->leftJoin('tbl_merk', 'tbl_merk.merk_id', '=', 'tbl_barang.merk_id')->where('tbl_barang.barang_kode', '=', $id)->get();
        return json_encode($data);
    }

    public function show(Request $request)
    {
        if ($request->ajax()) {
            $data = BarangModel::leftJoin('tbl_jenisbarang', 'tbl_jenisbarang.jenisbarang_id', '=', 'tbl_barang.jenisbarang_id')
                ->leftJoin('tbl_satuan', 'tbl_satuan.satuan_id', '=', 'tbl_barang.satuan_id')
                ->leftJoin('tbl_merk', 'tbl_merk.merk_id', '=', 'tbl_barang.merk_id')
                ->orderBy('barang_id', 'DESC')
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('img', function ($row) {
                    $url = ($row->barang_gambar == "image.png" || $row->barang_gambar == "") 
                        ? url('/assets/default/barang/image.png') 
                        : url('/assets/default/barang/' . $row->barang_gambar);

                    return '<img src="' . $url . '" class="rounded shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">';
                })
                ->addColumn('jenisbarang', function ($row) {
                    return $row->jenisbarang_id == '' ? '-' : $row->jenisbarang_nama;
                })
                ->addColumn('satuan', function ($row) {
                    return $row->satuan_id == '' ? '-' : $row->satuan_nama;
                })
                ->addColumn('merk', function ($row) {
                    return $row->merk_id == '' ? '-' : $row->merk_nama;
                })
                ->addColumn('currency', function ($row) {
                    return $row->barang_harga == '' ? '-' : 'Rp ' . number_format($row->barang_harga, 0);
                })
                ->addColumn('totalstok', function ($row) {
                    // KODE BARU: Query SUM yang berat didelete, langsung ambil field stok
                    $totalstok = $row->barang_stok;
                    if($totalstok == 0){
                        return '<span class="">'.$totalstok.'</span>';
                    }else if($totalstok > 0){
                        return '<span class="text-success">'.$totalstok.'</span>';
                    }else{
                        return '<span class="text-danger">'.$totalstok.'</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $array = array(
                        "barang_id" => $row->barang_id,
                        "jenisbarang_id" => $row->jenisbarang_id,
                        "satuan_id" => $row->satuan_id,
                        "merk_id" => $row->merk_id,
                        "barang_kode" => $row->barang_kode,
                        "barang_nama" => trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $row->barang_nama)),
                        "barang_harga" => $row->barang_harga,
                        "barang_stok" => $row->barang_stok,
                        "barang_gambar" => $row->barang_gambar,
                    );
                    $button = '';
                    $hakEdit = AksesModel::leftJoin('tbl_submenu', 'tbl_submenu.submenu_id', '=', 'tbl_akses.submenu_id')->where(array('tbl_akses.role_id' => Session::get('user')->role_id, 'tbl_submenu.submenu_judul' => 'Barang', 'tbl_akses.akses_type' => 'update'))->count();
                    $hakDelete = AksesModel::leftJoin('tbl_submenu', 'tbl_submenu.submenu_id', '=', 'tbl_akses.submenu_id')->where(array('tbl_akses.role_id' => Session::get('user')->role_id, 'tbl_submenu.submenu_judul' => 'Barang', 'tbl_akses.akses_type' => 'delete'))->count();
                    
                    if ($hakEdit > 0 && $hakDelete > 0) {
                        $button .= '
                        <div class="g-2">
                        <a class="btn modal-effect text-primary btn-sm" data-bs-effect="effect-super-scaled" data-bs-toggle="modal" href="#Umodaldemo8" data-bs-toggle="tooltip" data-bs-original-title="Edit" onclick=update(' . json_encode($array) . ')><span class="fe fe-edit text-success fs-14"></span></a>
                        <a class="btn modal-effect text-danger btn-sm" data-bs-effect="effect-super-scaled" data-bs-toggle="modal" href="#Hmodaldemo8" onclick=hapus(' . json_encode($array) . ')><span class="fe fe-trash-2 fs-14"></span></a>
                        </div>';
                    }
                    // ... (logika hak akses button ke bawahnya tetap sama seperti kodemu)
                    return $button;
                })
                ->rawColumns(['action', 'img', 'jenisbarang', 'satuan', 'merk', 'currency', 'totalstok'])
                ->make(true);
        }
    }
public function listbarang(Request $request)
{
    if ($request->ajax()) {

        $ruangan = $request->ruangan;

        // ===============================
        // FILTER JIKA ADA RUANGAN DIPILIH
        // ===============================
        if ($request->get('param') == 'tambah' && $ruangan) {

  $data = \DB::table('tbl_barang_ruangan')
    ->join('tbl_barang', 'tbl_barang.barang_kode', '=', 'tbl_barang_ruangan.barang_kode')
    ->leftJoin('tbl_jenisbarang', 'tbl_jenisbarang.jenisbarang_id', '=', 'tbl_barang.jenisbarang_id')
    ->leftJoin('tbl_satuan', 'tbl_satuan.satuan_id', '=', 'tbl_barang.satuan_id')
    ->leftJoin('tbl_merk', 'tbl_merk.merk_id', '=', 'tbl_barang.merk_id')
    ->where('tbl_barang_ruangan.customer_id', $ruangan)
    ->where('tbl_barang_ruangan.stok', '>', 0)
    ->orderBy('tbl_barang.barang_id', 'DESC')
    ->select(
        'tbl_barang.*',
        'tbl_barang_ruangan.stok as barang_stok',
        'tbl_jenisbarang.jenisbarang_nama',
        'tbl_satuan.satuan_nama',
        'tbl_merk.merk_nama'
    )
    ->get();


        } else {

            // DEFAULT (SEMUA BARANG)
            $data = BarangModel::leftJoin('tbl_jenisbarang', 'tbl_jenisbarang.jenisbarang_id', '=', 'tbl_barang.jenisbarang_id')
                ->leftJoin('tbl_satuan', 'tbl_satuan.satuan_id', '=', 'tbl_barang.satuan_id')
                ->leftJoin('tbl_merk', 'tbl_merk.merk_id', '=', 'tbl_barang.merk_id')
                ->orderBy('barang_id', 'DESC')
                ->get();
        }

        return DataTables::of($data)
            ->addIndexColumn()

           // Ganti bagian addColumn('img') di fungsi listbarang menjadi:
->addColumn('img', function ($row) {
    $url = ($row->barang_gambar == "image.png" || $row->barang_gambar == "") 
           ? url('/assets/default/barang/image.png') 
           : url('/assets/default/barang/' . $row->barang_gambar);

    return '<div class="text-center">
                <img src="' . $url . '" class="rounded shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
            </div>';
})

            ->addColumn('jenisbarang', function ($row) {
                return $row->jenisbarang_nama ?? '-';
            })

            ->addColumn('satuan', function ($row) {
                return $row->satuan_nama ?? '-';
            })

            ->addColumn('merk', function ($row) {
                return $row->merk_nama ?? '-';
            })

            ->addColumn('currency', function ($row) {
                return $row->barang_harga == '' ? '-' : 'Rp ' . number_format($row->barang_harga, 0);
            })

            ->addColumn('totalstok', function ($row) {

                $totalstok = $row->barang_stok;

                if ($totalstok == 0) {
                    return '<span>' . $totalstok . '</span>';
                } else if ($totalstok > 0) {
                    return '<span class="text-success">' . $totalstok . '</span>';
                } else {
                    return '<span class="text-danger">' . $totalstok . '</span>';
                }
            })

            ->addColumn('action', function ($row) use ($request) {

                $array = array(
                    "barang_kode" => $row->barang_kode,
                    "barang_nama" => trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $row->barang_nama)),
                    "satuan_nama" => trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $row->satuan_nama ?? '')),
                    "jenisbarang_nama" => trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $row->jenisbarang_nama ?? '')),
                );

                if ($request->get('param') == 'tambah') {
                    return '
                        <div class="g-2">
                            <a class="btn btn-primary btn-sm" href="javascript:void(0)" onclick=pilihBarang(' . json_encode($array) . ')>Pilih</a>
                        </div>';
                } else {
                    return '
                        <div class="g-2">
                            <a class="btn btn-success btn-sm" href="javascript:void(0)" onclick=pilihBarangU(' . json_encode($array) . ')>Pilih</a>
                        </div>';
                }
            })

            ->rawColumns(['action', 'img', 'jenisbarang', 'satuan', 'merk', 'currency', 'totalstok'])
            ->make(true);
    }
}
   public function proses_tambah(Request $request)
    {
        $img = "image.png";
        if ($request->hasFile('foto')) {
            $image = $request->file('foto');
            $img = $image->hashName();
            $image->move(public_path('assets/default/barang'), $img);
        }
        BarangModel::create([
            'barang_gambar' => $img, 'jenisbarang_id' => $request->jenisbarang,
            'satuan_id' => $request->satuan, 'merk_id' => $request->merk,
            'barang_kode' => $request->kode, 'barang_nama' => $request->nama,
            'barang_slug' => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->nama))),
            'barang_harga' => $request->harga, 'barang_stok' => 0,
        ]);
        return response()->json(['success' => 'Berhasil']);
    }

 public function proses_ubah(Request $request)
    {
        $barang = BarangModel::findOrFail($request->idbarangU);

        if ($request->hasFile('fotoU')) {
            $image = $request->file('fotoU');
            $nama_file = $image->hashName();
            $destinationPath = public_path('assets/default/barang');

            if ($barang->barang_gambar && $barang->barang_gambar != 'image.png') {
                $old = $destinationPath . '/' . $barang->barang_gambar;
                if (file_exists($old)) { unlink($old); }
            }
            
            $image->move($destinationPath, $nama_file);
            $barang->barang_gambar = $nama_file;
        }

        // KODE BARU: 'barang_stok' DIBUANG dari fungsi update ini
        $barang->update([
            'jenisbarang_id' => $request->jenisbarangU,
            'satuan_id'      => $request->satuanU,
            'merk_id'        => $request->merkU,
            'barang_kode'    => $request->kodeU,
            'barang_nama'    => $request->namaU,
            'barang_harga'   => $request->hargaU,
        ]);

        return response()->json(['success' => 'Berhasil']);
    }
 public function proses_hapus(Request $request, $id)
{
    $barang = BarangModel::findOrFail($id); // ← ambil dari URL parameter

    if ($barang->barang_gambar && $barang->barang_gambar != 'image.png') {
        $path = public_path('assets/default/barang/' . $barang->barang_gambar);
        if (file_exists($path)) {
            unlink($path);
        }
    }
    
    $barang->delete();
    return response()->json(['success' => 'Berhasil']);
}
}

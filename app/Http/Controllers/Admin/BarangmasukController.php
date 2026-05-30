<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AksesModel;
use App\Models\Admin\BarangmasukModel;
use App\Models\Admin\BarangModel;
use App\Models\Admin\CustomerModel;
use App\Models\Admin\BarangRuanganModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;  
use Yajra\DataTables\DataTables;


class BarangmasukController extends Controller
{
    public function index()
    {
        $data["title"] = "Barang Masuk";
        $data["hakTambah"] = AksesModel::leftJoin('tbl_submenu', 'tbl_submenu.submenu_id', '=', 'tbl_akses.submenu_id')->where(array('tbl_akses.role_id' => Session::get('user')->role_id, 'tbl_submenu.submenu_judul' => 'Barang Masuk', 'tbl_akses.akses_type' => 'create'))->count();
        $data["customer"] = CustomerModel::orderBy('customer_id', 'DESC')->get();
        return view('Admin.BarangMasuk.index', $data);
    }

    public function show(Request $request)
    {
        if ($request->ajax()) {
            $data = BarangmasukModel::leftJoin('tbl_barang', 'tbl_barang.barang_kode', '=', 'tbl_barangmasuk.barang_kode')->leftJoin('tbl_customer', 'tbl_customer.customer_id', '=', 'tbl_barangmasuk.customer_id')->orderBy('bm_id', 'DESC')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tgl', function ($row) {
                    $tgl = $row->bm_tanggal == '' ? '-' : Carbon::parse($row->bm_tanggal)->translatedFormat('d F Y');

                    return $tgl;
                })
                ->addColumn('customer', function ($row) {
                    $customer = $row->customer_id == '' ? '-' : $row->customer_nama;

                    return $customer;
                })
                ->addColumn('barang', function ($row) {
                    $barang = $row->barang_id == '' ? '-' : $row->barang_nama;

                    return $barang;
                })
                 ->addColumn('harga_satuan', function ($row) {
                  return 'Rp ' . number_format($row->barang_harga);
                })
                 ->addColumn('total_harga', function ($row) {
                return 'Rp ' . number_format($row->total_harga);       
                })

                ->addColumn('action', function ($row) {
                    $array = array(
                        "bm_id" => $row->bm_id,
                        "bm_kode" => $row->bm_kode,
                        "barang_kode" => $row->barang_kode,
                        "customer_id" => $row->customer_id,
                        "bm_tanggal" => $row->bm_tanggal,
                        "bm_jumlah" => $row->bm_jumlah
                    );
                    $button = '';
                    $hakEdit = AksesModel::leftJoin('tbl_submenu', 'tbl_submenu.submenu_id', '=', 'tbl_akses.submenu_id')->where(array('tbl_akses.role_id' => Session::get('user')->role_id, 'tbl_submenu.submenu_judul' => 'Barang Masuk', 'tbl_akses.akses_type' => 'update'))->count();
                    $hakDelete = AksesModel::leftJoin('tbl_submenu', 'tbl_submenu.submenu_id', '=', 'tbl_akses.submenu_id')->where(array('tbl_akses.role_id' => Session::get('user')->role_id, 'tbl_submenu.submenu_judul' => 'Barang Masuk', 'tbl_akses.akses_type' => 'delete'))->count();
                    if ($hakEdit > 0 && $hakDelete > 0) {
                        $button .= '
                        <div class="g-2">
                        <a class="btn modal-effect text-primary btn-sm" data-bs-effect="effect-super-scaled" data-bs-toggle="modal" href="#Umodaldemo8" data-bs-toggle="tooltip" data-bs-original-title="Edit" onclick=update(' . json_encode($array) . ')><span class="fe fe-edit text-success fs-14"></span></a>
                        <a class="btn modal-effect text-danger btn-sm" data-bs-effect="effect-super-scaled" data-bs-toggle="modal" href="#Hmodaldemo8" onclick=hapus(' . json_encode($array) . ')><span class="fe fe-trash-2 fs-14"></span></a>
                        </div>
                        ';
                    } else if ($hakEdit > 0 && $hakDelete == 0) {
                        $button .= '
                        <div class="g-2">
                            <a class="btn modal-effect text-primary btn-sm" data-bs-effect="effect-super-scaled" data-bs-toggle="modal" href="#Umodaldemo8" data-bs-toggle="tooltip" data-bs-original-title="Edit" onclick=update(' . json_encode($array) . ')><span class="fe fe-edit text-success fs-14"></span></a>
                        </div>
                        ';
                    } else if ($hakEdit == 0 && $hakDelete > 0) {
                        $button .= '
                        <div class="g-2">
                        <a class="btn modal-effect text-danger btn-sm" data-bs-effect="effect-super-scaled" data-bs-toggle="modal" href="#Hmodaldemo8" onclick=hapus(' . json_encode($array) . ')><span class="fe fe-trash-2 fs-14"></span></a>
                        </div>
                        ';
                    } else {
                        $button .= '-';
                    }
                    return $button;
                })
                ->rawColumns(['action', 'tgl', 'customer', 'barang'])->make(true);
        }
    }

    public function proses_tambah(Request $request)
    {
    
    // ambil data barang
BarangModel::where('barang_kode', $request->barang)
    ->increment('barang_stok', $request->jml);
  
    $barang = BarangModel::where('barang_kode', $request->barang)->first();
    

    // hitung total harga
    $total_harga = $request->jml * $barang->barang_harga;
        //insert data
        BarangmasukModel::create([
            'bm_tanggal' => $request->tglmasuk,
            'bm_kode' => $request->bmkode,
            'barang_kode' => $request->barang,
            'customer_id'   => $request->customer,
            'bm_jumlah'   => $request->jml,
            'total_harga'  => $total_harga,
        ]);

        // UPDATE STOK PER RUANGAN

$stok = BarangRuanganModel::where('barang_kode', $request->barang)
    ->where('customer_id', $request->customer)
    ->first();  

if ($stok) {
    $stok->increment('stok', $request->jml);
} else {
    BarangRuanganModel::create([
        'barang_kode' => $request->barang,
        'customer_id' => $request->customer,
        'stok' => $request->jml
    ]);
}

        return response()->json(['success' => 'Berhasil']);
    }




  public function proses_ubah(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                // 1. Ambil data transaksi lama sebelum diubah
                $barangmasuk = BarangmasukModel::findOrFail($id);
                
                // 2. Ambil data barang untuk hitung total harga
                $barang = BarangModel::where('barang_kode', $request->barang)->first();
                if (!$barang) {
                    throw new \Exception("Barang tidak ditemukan!");
                }

                $jumlahLama = $barangmasuk->bm_jumlah;
                $jumlahBaru = $request->jml;
                
                $ruanganLama = $barangmasuk->customer_id;
                $ruanganBaru = $request->customer;

                // ==========================================
                // KONDISI A: JIKA RUANGAN/CUSTOMER DIGANTI
                // ==========================================
                if ($ruanganLama != $ruanganBaru) {
                    
                    // a. Kurangi stok di ruangan LAMA (karena barang dipindahkan)
                    $stokRuanganLama = DB::table('tbl_barang_ruangan')
                        ->where('barang_kode', $barangmasuk->barang_kode)
                        ->where('customer_id', $ruanganLama)
                        ->value('stok');

                    if ($stokRuanganLama < $jumlahLama) {
                        throw new \Exception("Gagal ubah! Stok di ruangan lama sudah terpakai dan tidak mencukupi untuk ditarik kembali.");
                    }

                    DB::table('tbl_barang_ruangan')
                        ->where('barang_kode', $barangmasuk->barang_kode)
                        ->where('customer_id', $ruanganLama)
                        ->decrement('stok', $jumlahLama);

                    // b. Tambah stok ke ruangan BARU
                    $stokRuanganBaru = DB::table('tbl_barang_ruangan')
                        ->where('barang_kode', $request->barang)
                        ->where('customer_id', $ruanganBaru)
                        ->first();

                    if ($stokRuanganBaru) {
                        DB::table('tbl_barang_ruangan')
                            ->where('barang_kode', $request->barang)
                            ->where('customer_id', $ruanganBaru)
                            ->increment('stok', $jumlahBaru);
                    } else {
                        DB::table('tbl_barang_ruangan')->insert([
                            'barang_kode' => $request->barang,
                            'customer_id' => $ruanganBaru,
                            'stok' => $jumlahBaru
                        ]);
                    }

                    // c. Sesuaikan Stok di Gudang Utama (tbl_barang) jika jumlahnya juga berbeda
                    $selisihGudang = $jumlahBaru - $jumlahLama;
                    if ($selisihGudang > 0) {
                        BarangModel::where('barang_kode', $request->barang)->increment('barang_stok', $selisihGudang);
                    } elseif ($selisihGudang < 0) {
                        BarangModel::where('barang_kode', $request->barang)->decrement('barang_stok', abs($selisihGudang));
                    }

                // ==========================================
                // KONDISI B: JIKA RUANGAN TETAP SAMA (CUMA JUMLAH BERUBAH)
                // ==========================================
                } else {
                    $selisih = $jumlahBaru - $jumlahLama;

                    if ($selisih > 0) {
                        BarangModel::where('barang_kode', $request->barang)->increment('barang_stok', $selisih);
                        DB::table('tbl_barang_ruangan')
                            ->where('barang_kode', $request->barang)
                            ->where('customer_id', $request->customer)
                            ->increment('stok', $selisih);

                    } elseif ($selisih < 0) {
                        $nilaiSelisih = abs($selisih);

                        $stokRuangan = DB::table('tbl_barang_ruangan')
                            ->where('barang_kode', $request->barang)
                            ->where('customer_id', $request->customer)
                            ->value('stok');

                        if ($stokRuangan < $nilaiSelisih) {
                            throw new \Exception("Gagal ubah! Stok di ruangan saat ini tidak mencukupi.");
                        }

                        BarangModel::where('barang_kode', $request->barang)->decrement('barang_stok', $nilaiSelisih);
                        DB::table('tbl_barang_ruangan')
                            ->where('barang_kode', $request->barang)
                            ->where('customer_id', $request->customer)
                            ->decrement('stok', $nilaiSelisih);
                    }
                }

                // 3. Update data transaksi barang masuk
                $total_harga = $jumlahBaru * $barang->barang_harga;
                $barangmasuk->update([
                    'bm_tanggal'   => $request->tglmasuk,
                    'barang_kode'  => $request->barang,
                    'customer_id'  => $request->customer,
                    'bm_jumlah'    => $jumlahBaru,
                    'total_harga'  => $total_harga,
                ]);
            });

            return response()->json(['success' => 'Berhasil']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function proses_hapus(Request $request, BarangmasukModel $barangmasuk)
{
    // Cek pakai bk_tujuan karena customer_id di tbl_barangkeluar masih NULL
    $adaKeluar = DB::table('tbl_barangkeluar')
        ->where('barang_kode', $barangmasuk->barang_kode)
        ->where('bk_tujuan', $barangmasuk->customer_id) // ← pakai bk_tujuan
        ->exists();

    if ($adaKeluar) {
        return response()->json([
            'success' => false,
            'message' => 'Tidak bisa dihapus! Masih ada transaksi barang keluar yang terkait. Hapus dulu transaksi barang keluar sebelum menghapus barang masuk.'
        ], 422);
    }

    DB::transaction(function () use ($barangmasuk) {
        BarangModel::where('barang_kode', $barangmasuk->barang_kode)
            ->decrement('barang_stok', $barangmasuk->bm_jumlah);

        DB::table('tbl_barang_ruangan')
            ->where('barang_kode', $barangmasuk->barang_kode)
            ->where('customer_id', $barangmasuk->customer_id)
            ->decrement('stok', $barangmasuk->bm_jumlah);

        $barangmasuk->delete();
    });

    return response()->json(['success' => true, 'message' => 'Berhasil dihapus']);
}

}

<?php

namespace App\Http\Controllers\Admin;

use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\PeminjamanModel;
use App\Models\Admin\BarangModel;
use App\Models\Admin\CustomerModel;
use App\Models\Admin\PeminjamanDetailModel;
use App\Models\Admin\BarangRuanganModel;
use Yajra\DataTables\Facades\DataTables;

class PeminjamanController extends Controller
{
    // ===============================
    // INDEX
    // ===============================
    public function index()
    {
        $title = 'Peminjaman';
      
        $customer = CustomerModel::all();

        return view('Admin.Peminjaman.index', compact('title', 'customer'));
    }

   // ===============================
    // DATATABLE
    // ===============================
 public function get()
{
    $pinjam = PeminjamanModel::with(['customer', 'details.barang'])->latest();

    return DataTables::of($pinjam)
        ->addIndexColumn()
        ->editColumn('pinjam_tanggal', function ($row) {
            return date('d F Y', strtotime($row->pinjam_tanggal));
        })
        ->addColumn('pinjam_nama', function($row){
        return $row->pinjam_nama; // Mengambil nama orang langsung
    })
        ->addColumn('customer', function ($row) {
            return $row->customer->customer_nama ?? '-';
        })
        ->addColumn('tanggal_dikembalikan', function ($row) {
            if ($row->pinjam_tanggal_kembali === null) {
                return '<span class="badge bg-warning">Belum dikembalikan</span>';
            }

            $tgl = date('d F Y', strtotime($row->pinjam_tanggal_kembali));
            
            if ($row->pinjam_keterangan && str_contains(strtolower($row->pinjam_keterangan), 'kurang')) {
                return $tgl . ' <br><small class="text-danger fw-bold">(Kembali Sebagian)</small>';
            }
            
            return $tgl;
        })
        ->addColumn('barang', function ($row) {
            return $row->details->map(function ($d) {
                return $d->barang 
                    ? $d->barang->barang_nama 
                    : '<span class="text-danger">Barang tidak ditemukan</span>';
            })->implode('<br>');
        })
        ->addColumn('jumlah', function ($row) {
    return $row->details->map(function ($d) use ($row) { // PENTING: Harus ada use ($row)
        // Jika masih dipinjam (total), tampilkan 0 atau sisa yang sudah kembali
        // Jika statusnya sudah pernah dicicil, tampilkan angka aslinya
        $kembali = ($d->jumlah_kembali ?? 0);
        return "{$kembali} / {$d->jumlah}";
    })->implode('<br>');
})
        ->editColumn('pinjam_status', function ($row) {
    if ($row->pinjam_status === 'dipinjam') {
        // Jika status dipinjam tapi sudah ada barang yang masuk (keterangan tidak kosong)
        if ($row->pinjam_keterangan && $row->pinjam_keterangan != 'Kembali Lengkap') {
            return '<span class="badge bg-warning text-dark">Kembali Sebagian</span>';
        }
        return '<span class="badge bg-info">Dipinjam</span>';
    }
    return '<span class="badge bg-success">Kembali Lengkap</span>';
})
        ->addColumn('action', function ($row) {
            $btn = '';
            if ($row->pinjam_status === 'dipinjam') {
                $btn .= '<button class="btn btn-success btn-sm" onclick="kembalikan('.$row->pinjam_id.')">Kembalikan</button> ';
            }
            $btn .= '<button class="btn btn-danger btn-sm" onclick=\'hapus('.json_encode($row).')\'>Hapus</button>';
            return $btn;
        })
        ->rawColumns(['barang', 'jumlah', 'action', 'tanggal_dikembalikan', 'pinjam_status'])
        ->make(true);
}
    // ===============================
    // 🔥 BARANG BERDASARKAN RUANGAN
    // ===============================
 public function getBarangByRuangan(Request $request, $customer_id)
{
    if ($request->ajax()) {
        $data = \DB::table('tbl_barang_ruangan')
            ->join('tbl_barang', 'tbl_barang.barang_kode', '=', 'tbl_barang_ruangan.barang_kode')
            ->leftJoin('tbl_jenisbarang', 'tbl_jenisbarang.jenisbarang_id', '=', 'tbl_barang.jenisbarang_id')
            ->leftJoin('tbl_satuan', 'tbl_satuan.satuan_id', '=', 'tbl_barang.satuan_id')
            ->leftJoin('tbl_merk', 'tbl_merk.merk_id', '=', 'tbl_barang.merk_id')
            ->where('tbl_barang_ruangan.customer_id', $customer_id) // Filter berdasarkan ID Ruangan dari URL
            ->where('tbl_barang_ruangan.stok', '>', 0)
            ->select(
                'tbl_barang.*',
                'tbl_barang_ruangan.stok as barang_stok',
                'tbl_jenisbarang.jenisbarang_nama',
                'tbl_satuan.satuan_nama',
                'tbl_merk.merk_nama'
            )
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('img', function ($row) {
                return ($row->barang_gambar == "image.png") 
                    ? '<span class="avatar avatar-lg cover-image" style="background: url('.url('/assets/default/barang').'/'.$row->barang_gambar.') center center;"></span>'
                    : '<span class="avatar avatar-lg cover-image" style="background: url('.asset('storage/barang/'.$row->barang_gambar).') center center;"></span>';
            })
            // Tambahkan kolom lain seperti di BarangController kamu sebelumnya...
            ->addColumn('action', function ($row) {
                $array = [
                    "barang_kode" => $row->barang_kode,
                    "barang_nama" => str_replace(' ', '_', $row->barang_nama),
                    "satuan_nama" => str_replace(' ', '_', $row->satuan_nama ?? ''),
                    "jenisbarang_nama" => str_replace(' ', '_', $row->jenisbarang_nama ?? ''),
                ];
                return '<button class="btn btn-primary btn-sm" onclick=\'pilihBarang('.json_encode($array).')\'>Pilih</button>';
            })
            ->rawColumns(['action', 'img'])
            ->make(true);
    }
}
public function getDetail($id)
{
    // Mengambil data peminjaman beserta detail barangnya
    $data = PeminjamanModel::with('details.barang')->findOrFail($id);
    return response()->json($data);
}
    // ===============================
    // SIMPAN PEMINJAMAN
    // ===============================
  public function store(Request $request)
{
    try {
        DB::transaction(function () use ($request) {

            $pinjam = PeminjamanModel::create([
                'pinjam_kode' => $request->pinjam_kode ?? 'PMJ-' . time(),
                'pinjam_nama' => $request->pinjam_nama,
                'customer_id' => $request->customer,
                'pinjam_tanggal' => $request->tanggal,
                'pinjam_jatuh_tempo' => $request->jatuh_tempo,
                'pinjam_tanggal_kembali' => null,
                'pinjam_status' => 'dipinjam',
            ]);

            foreach ($request->barang as $i => $barang_kode) {

                $stok = BarangRuanganModel::where('barang_kode', $barang_kode)
                    ->where('customer_id', $request->customer)
                    ->lockForUpdate() // 🔒 penting
                    ->first();

                if (!$stok) {
                    throw new \Exception('Barang tidak ditemukan di ruangan ini');
                }

                if ($stok->stok < $request->jumlah[$i]) {
                    throw new \Exception(
                        "Stok {$stok->barang->barang_nama} tidak mencukupi. Sisa stok: {$stok->stok}"
                    );
                }

                PeminjamanDetailModel::create([
                    'pinjam_id' => $pinjam->pinjam_id,
                    'barang_kode' => $barang_kode,
                    'jumlah' => $request->jumlah[$i],
                ]);

                $stok->decrement('stok', $request->jumlah[$i]);
               

            }
        });

        return back()->with([
            'status' => 'success',
            'msg' => 'Peminjaman berhasil disimpan'
        ]);

    } catch (\Exception $e) {
        return back()->with([
            'status' => 'error',
            'msg' => $e->getMessage()
        ])->withInput();
    }
}


    // ===============================
    // HAPUS
    // ===============================
  // ===============================================================
    // HAPUS (Sudah Aman: Hanya mengembalikan SISA barang yang di luar)
    // ===============================================================
    public function delete(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                // Ambil data peminjaman beserta semua detail barangnya
                $p = PeminjamanModel::with('details')->findOrFail($request->idpeminjaman);

                // Jika statusnya belum lunas (belum 'dikembalikan'), hitung sisa yang masih di luar
                if ($p->pinjam_status !== 'dikembalikan') {
                    foreach ($p->details as $d) {
                        $sudah_kembali = $d->jumlah_kembali ?? 0;
                        $sisa_di_luar = $d->jumlah - $sudah_kembali;

                        // Hanya kembalikan sisa barang yang benar-benar belum pulang ke ruangan
                        if ($sisa_di_luar > 0) {
                            BarangRuanganModel::where('barang_kode', $d->barang_kode)
                                ->where('customer_id', $p->customer_id)
                                ->increment('stok', $sisa_di_luar);
                        }
                    }
                }

                // Setelah stok ruangan aman disesuaikan, baru hapus datanya
                $p->delete();
            });

            return redirect()->back()->with('success', 'Data peminjaman berhasil dihapus dan sisa stok dikembalikan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    // ===============================================================
    // KEMBALIKAN / CICIL (Sudah Aman: Proteksi input minus & form kosong)
    // ===============================================================
    public function kembalikan(Request $request, $id)
    {
        try {
            $result = DB::transaction(function () use ($request, $id) {
                $p = PeminjamanModel::with('details.barang')->findOrFail($id);
                $pesan_keterangan = "";
                $semua_lunas = true; 
                
                $inputs = $request->jumlah_kembali; 
                $ada_input_valid = false; // Flag penanda apakah admin beneran isi angka > 0

                foreach ($p->details as $index => $d) {
                    $qty_input_sekarang = isset($inputs[$index]) ? (int)$inputs[$index] : 0;
                    
                    // 1. VALIDASI: Tolak jika admin iseng masukkan angka minus
                    if ($qty_input_sekarang < 0) {
                        throw new \Exception("Jumlah pengembalian untuk {$d->barang->barang_nama} tidak boleh angka minus!");
                    }

                    if ($qty_input_sekarang > 0) {
                        $ada_input_valid = true;
                    }

                    // Hitung total akumulasi (cicilan lama + input baru detik ini)
                    $total_kembali_kumulatif = ($d->jumlah_kembali ?? 0) + $qty_input_sekarang;

                    // 2. VALIDASI: Tolak jika total cicilan melebihi jumlah yang dipinjam di awal
                    if ($total_kembali_kumulatif > $d->jumlah) {
                        $sisa_seharusnya = $d->jumlah - ($d->jumlah_kembali ?? 0);
                        throw new \Exception("Input untuk {$d->barang->barang_nama} melebihi sisa pinjaman! (Maksimal sisa: {$sisa_seharusnya})");
                    }

                    // Update akumulasi jumlah yang kembali di tabel detail peminjaman
                    DB::table('tbl_peminjaman_detail')
                        ->where('detail_id', $d->detail_id)
                        ->update(['jumlah_kembali' => $total_kembali_kumulatif]);

                    // Update Stok Ruangan (Hanya bertambah sebesar yang diinput sekarang)
                    if ($qty_input_sekarang > 0) {
                        BarangRuanganModel::where('barang_kode', $d->barang_kode)
                            ->where('customer_id', $p->customer_id)
                            ->increment('stok', $qty_input_sekarang);
                    }
                    
                    // Hitung sisa hutang barang untuk teks keterangan
                    $sisa = $d->jumlah - $total_kembali_kumulatif;
                    if ($sisa > 0) {
                        $pesan_keterangan .= "{$d->barang->barang_nama} kurang {$sisa}. ";
                        $semua_lunas = false;
                    }
                }

                // 3. VALIDASI: Jika admin klik simpan tapi semua input diisi 0, batalkan proses
                if (!$ada_input_valid) {
                    throw new \Exception("Gagal proses! Kamu belum mengisi jumlah barang yang dikembalikan.");
                }

                // Update data header utama peminjaman
                $p->update([
                    'pinjam_status' => $semua_lunas ? 'dikembalikan' : 'dipinjam',
                    'pinjam_tanggal_kembali' => $semua_lunas ? now()->toDateString() : $p->pinjam_tanggal_kembali,
                    'pinjam_keterangan' => $pesan_keterangan ?: 'Kembali Lengkap'
                ]);

                return [
                    'status' => 'success', 
                    'msg' => $semua_lunas ? 'Semua barang telah kembali lengkap' : 'Berhasil mencicil pengembalian'
                ];
            });
            
            return response()->json($result);

        } catch (\Exception $e) {
            // Mengembalikan error 400 supaya AJAX / SweetAlert di frontend kamu bisa menangkap pesan errornya
            return response()->json(['status' => 'error', 'msg' => $e->getMessage()], 400);
        }
    }
}
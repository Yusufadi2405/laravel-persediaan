<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\PeminjamanModel;
use App\Models\Admin\WebModel;
use App\Models\Admin\CustomerModel; //
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use PDF;

class LapPeminjamanController extends Controller
{
    public function index()
    {
        // Mengambil data ruangan (customer) untuk dropdown filter
        $data["ruangan"] = CustomerModel::orderBy('customer_nama', 'ASC')->get();
        $data["title"] = "Laporan Peminjaman";
        
        return view('Admin.Laporan.Peminjaman.index', $data);
    }

    public function show(Request $request)
{
    if ($request->ajax()) {
        $query = PeminjamanModel::with(['customer', 'details.barang'])
                 ->orderBy('pinjam_id', 'DESC');

        if ($request->tglawal && $request->tglakhir) {
            $query->whereBetween('pinjam_tanggal', [$request->tglawal, $request->tglakhir]);
        }

        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->status) {
            $query->where('pinjam_status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('tgl', function ($row) {
                return \Carbon\Carbon::parse($row->pinjam_tanggal)->translatedFormat('d F Y');
            })
            // KOLOM 1: NAMA PEMINJAM (Orangnya)
            ->addColumn('peminjam_nama', function ($row) {
                return $row->pinjam_nama ?? '-';
            })
            // KOLOM 2: RUANGAN
            ->addColumn('customer', function ($row) {
                return $row->customer->customer_nama ?? '-';
            })
            ->addColumn('barang', function ($row) {
                // MENGHAPUS bullet point agar lebih bersih
                return $row->details->map(function ($d) {
                    return $d->barang->barang_nama ?? 'Barang Dihapus';
                })->implode('<br>');
            })
            ->addColumn('jumlah', function ($row) {
                return $row->details->map(function ($d) {
                    return $d->jumlah;
                })->implode('<br>');
            })
            ->addColumn('status', function ($row) {
                if ($row->pinjam_status == 'dipinjam') {
                    return '<span class="badge bg-warning text-white">Dipinjam</span>';
                }
                return '<span class="badge bg-success text-white">Dikembalikan</span>';
            })
            ->addColumn('tgl_kembali', function ($row) {
                return $row->pinjam_tanggal_kembali
                    ? \Carbon\Carbon::parse($row->pinjam_tanggal_kembali)->translatedFormat('d F Y')
                    : '<span class="text-muted"><i>Belum Kembali</i></span>';
            })
            ->rawColumns(['barang', 'jumlah', 'status', 'tgl_kembali'])
            ->make(true);
    }
}

    public function print(Request $request)
    {
        $data = $this->getPrintData($request);
        return view('Admin.Laporan.Peminjaman.print', $data);
    }

    public function pdf(Request $request)
    {
        $data = $this->getPrintData($request);
        $pdf = PDF::loadView('Admin.Laporan.Peminjaman.pdf', $data);
        return $pdf->download('laporan-peminjaman.pdf');
    }

    private function getPrintData($request)
{
    // Tambahkan orderBy agar urutan data terbaru ada di paling atas (sinkron dengan web)
    $query = PeminjamanModel::with(['customer', 'details.barang'])
             ->orderBy('pinjam_tanggal', 'DESC') // Urut berdasarkan tanggal pinjam terbaru
             ->orderBy('pinjam_id', 'DESC');      // Dan ID terbaru

    if ($request->tglawal && $request->tglakhir) {
        $query->whereBetween('pinjam_tanggal', [$request->tglawal, $request->tglakhir]);
    }
    if ($request->customer_id) {
        $query->where('customer_id', $request->customer_id);
    }
    if ($request->status) {
        $query->where('pinjam_status', $request->status);
    }

    return [
        'data'    => $query->get(),
        'web'     => WebModel::first(),
        'tglawal' => $request->tglawal,
        'tglakhir'=> $request->tglakhir,
        'title'   => 'Laporan Peminjaman'
    ];
}
}
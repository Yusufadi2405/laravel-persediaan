<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\BarangkeluarModel;
use App\Models\Admin\WebModel;
use App\Models\Admin\CustomerModel; 
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use PDF;

class LapBarangKeluarController extends Controller
{
    public function index()
    {
        $data["title"] = "Lap Barang Keluar";
        // Ambil data ruangan untuk dropdown
        $data["ruangan"] = CustomerModel::orderBy('customer_nama', 'ASC')->get();
        return view('Admin.Laporan.BarangKeluar.index', $data);
    }

    public function show(Request $request)
    {
        if ($request->ajax()) {
            // FIX: leftJoin diarahkan ke bk_tujuan, bukan customer_id
            $query = \DB::table('tbl_barangkeluar')
                ->join('tbl_barang', 'tbl_barang.barang_kode', '=', 'tbl_barangkeluar.barang_kode')
                ->leftJoin('tbl_customer', 'tbl_customer.customer_id', '=', 'tbl_barangkeluar.bk_tujuan');

            // Filter Tanggal
            if ($request->tglawal && $request->tglakhir) {
                $query->whereBetween('tbl_barangkeluar.bk_tanggal', [$request->tglawal, $request->tglakhir]);
            }

            // Filter Ruangan (FIX: ganti customer_id menjadi bk_tujuan)
            if ($request->ruangan) {
                $query->where('tbl_barangkeluar.bk_tujuan', $request->ruangan);
            }

            $data = $query->select(
                'tbl_barangkeluar.*', 
                'tbl_barang.barang_nama', 
                'tbl_customer.customer_nama'
            )->orderBy('tbl_barangkeluar.bk_tanggal', 'ASC')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('tgl', function ($row) {
                    return $row->bk_tanggal ? \Carbon\Carbon::parse($row->bk_tanggal)->translatedFormat('d F Y') : '-';
                })
                ->addColumn('barang', function ($row) {
                    return $row->barang_nama ?? '-';
                })
                ->addColumn('ruangan_nama', function ($row) { 
                    return $row->customer_nama ?? '-';
                })
                ->rawColumns(['tgl', 'barang', 'ruangan_nama'])
                ->make(true);
        }
    }

    public function print(Request $request)
    {
        // FIX: leftJoin diarahkan ke bk_tujuan
        $query = \DB::table('tbl_barangkeluar')
                ->join('tbl_barang', 'tbl_barang.barang_kode', '=', 'tbl_barangkeluar.barang_kode')
                ->leftJoin('tbl_customer', 'tbl_customer.customer_id', '=', 'tbl_barangkeluar.bk_tujuan');

        if ($request->tglawal) {
            $query->whereBetween('bk_tanggal', [$request->tglawal, $request->tglakhir]);
        }
        
        // FIX: ganti customer_id menjadi bk_tujuan
        if ($request->ruangan) {
            $query->where('tbl_barangkeluar.bk_tujuan', $request->ruangan);
        }

        $data['data'] = $query->select('tbl_barangkeluar.*', 'tbl_barang.barang_nama', 'tbl_customer.customer_nama')
            ->orderBy('bk_tanggal', 'ASC')->get();
            
        $data["title"] = "Print Laporan";
        $data['web'] = WebModel::first();
        $data['tglawal'] = $request->tglawal;
        $data['tglakhir'] = $request->tglakhir;
        return view('Admin.Laporan.BarangKeluar.print', $data);
    }

    public function pdf(Request $request)
    {
        // FIX: leftJoin diarahkan ke bk_tujuan
        $query = \DB::table('tbl_barangkeluar')
                ->join('tbl_barang', 'tbl_barang.barang_kode', '=', 'tbl_barangkeluar.barang_kode')
                ->leftJoin('tbl_customer', 'tbl_customer.customer_id', '=', 'tbl_barangkeluar.bk_tujuan');

        if ($request->tglawal) {
            $query->whereBetween('bk_tanggal', [$request->tglawal, $request->tglakhir]);
        }
        
        // FIX: ganti customer_id menjadi bk_tujuan
        if ($request->ruangan) {
            $query->where('tbl_barangkeluar.bk_tujuan', $request->ruangan);
        }

        $data['data'] = $query->select('tbl_barangkeluar.*', 'tbl_barang.barang_nama', 'tbl_customer.customer_nama')
            ->orderBy('bk_tanggal', 'ASC')->get();
            
        $data["title"] = "PDF Laporan";
        $data['web'] = WebModel::first();
        $data['tglawal'] = $request->tglawal;
        $data['tglakhir'] = $request->tglakhir;
        
        $pdf = PDF::loadView('Admin.Laporan.BarangKeluar.pdf', $data);
        return $pdf->download('laporan-barang-keluar.pdf');
    }
}
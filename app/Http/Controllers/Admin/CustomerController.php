<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\AksesModel;
use App\Models\Admin\CustomerModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function index()
    {
        $data["title"] = "Customer";
        $data["hakTambah"] = AksesModel::leftJoin('tbl_menu', 'tbl_menu.menu_id', '=', 'tbl_akses.menu_id')
            ->where([
                'tbl_akses.role_id' => Session::get('user')->role_id,
                'tbl_menu.menu_judul' => 'Customer',
                'tbl_akses.akses_type' => 'create'
            ])
            ->count();

        return view('Admin.Customer.index', $data);
    }

    public function show(Request $request)
    {
        if ($request->ajax()) {
            $data = CustomerModel::orderBy('customer_id', 'DESC')->get();

            // Pindahkan pengecekan hak akses ke luar loop agar performa database lebih cepat
            $roleId = Session::get('user')->role_id;
            
            $hakEdit = AksesModel::leftJoin('tbl_menu', 'tbl_menu.menu_id', '=', 'tbl_akses.menu_id')
                ->where([
                    'tbl_akses.role_id' => $roleId,
                    'tbl_menu.menu_judul' => 'Customer',
                    'tbl_akses.akses_type' => 'update'
                ])->count();

            $hakDelete = AksesModel::leftJoin('tbl_menu', 'tbl_menu.menu_id', '=', 'tbl_akses.menu_id')
                ->where([
                    'tbl_akses.role_id' => $roleId,
                    'tbl_menu.menu_judul' => 'Customer',
                    'tbl_akses.akses_type' => 'delete'
                ])->count();

            return DataTables::of($data)
                ->addIndexColumn()

                ->addColumn('notelp', function ($row) {
                    return $row->customer_notelp ?: '-';
                })

                ->addColumn('alamat', function ($row) {
                    return $row->customer_alamat ?: '-';
                })

                ->addColumn('action', function ($row) use ($hakEdit, $hakDelete) {
                    // Kirim data asli tanpa preg_replace agar spasi tidak berubah jadi underscore (_)
                    $array = [
                        "customer_id"     => $row->customer_id,
                        "customer_nama"   => $row->customer_nama,
                        "customer_notelp" => $row->customer_notelp,
                        "customer_alamat" => $row->customer_alamat,
                    ];

                    $button = '';

                    if ($hakEdit > 0 && $hakDelete > 0) {
                        $button .= '
                        <div class="g-2">
                            <a class="btn modal-effect text-primary btn-sm"
                               data-bs-effect="effect-super-scaled"
                               data-bs-toggle="modal"
                               href="#Umodaldemo8"
                               onclick="update('.htmlspecialchars(json_encode($array)).')">
                                 <span class="fe fe-edit text-success fs-14"></span>
                            </a>
                            <a class="btn modal-effect text-danger btn-sm"
                               data-bs-effect="effect-super-scaled"
                               data-bs-toggle="modal"
                               href="#Hmodaldemo8"
                               onclick="hapus('.htmlspecialchars(json_encode($array)).')">
                                 <span class="fe fe-trash-2 fs-14"></span>
                            </a>
                        </div>';
                    } elseif ($hakEdit > 0) {
                        $button .= '
                        <div class="g-2">
                            <a class="btn modal-effect text-primary btn-sm"
                               data-bs-effect="effect-super-scaled"
                               data-bs-toggle="modal"
                               href="#Umodaldemo8"
                               onclick="update('.htmlspecialchars(json_encode($array)).')">
                                 <span class="fe fe-edit text-success fs-14"></span>
                            </a>
                        </div>';
                    } elseif ($hakDelete > 0) {
                        $button .= '
                        <div class="g-2">
                            <a class="btn modal-effect text-danger btn-sm"
                               data-bs-effect="effect-super-scaled"
                               data-bs-toggle="modal"
                               href="#Hmodaldemo8"
                               onclick="hapus('.htmlspecialchars(json_encode($array)).')">
                                 <span class="fe fe-trash-2 fs-14"></span>
                            </a>
                        </div>';
                    } else {
                        $button .= '-';
                    }

                    return $button;
                })

                ->rawColumns(['action', 'notelp', 'alamat'])
                ->make(true);
        }
    }

    // ================= TAMBAH =================
    public function proses_tambah(Request $request)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->customer)));

        CustomerModel::create([
            'customer_nama'   => $request->customer,
            'customer_slug'   => $slug,
            'customer_notelp' => $request->notelp,
            'customer_alamat' => $request->alamat,
        ]);

        return response()->json(['success' => 'Berhasil']);
    }

    // ================= UBAH =================
    public function proses_ubah(Request $request, $id)
    {
        $customer = CustomerModel::findOrFail($id);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->customer)));

        $customer->update([
            'customer_nama'   => $request->customer,
            'customer_slug'   => $slug,
            'customer_notelp' => $request->notelp,
            'customer_alamat' => $request->alamat,
        ]);

        return response()->json(['success' => 'Berhasil']);
    }

    // ================= HAPUS =================
    public function proses_hapus(Request $request, $id)
    {
        $customer = CustomerModel::findOrFail($id);
        $customer->delete();
        
        return response()->json(['success' => 'Berhasil']);
    }
}
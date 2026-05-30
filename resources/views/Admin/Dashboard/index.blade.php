@extends('Master.Layouts.app', ['title' => $title])

@section('content')
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <div>
        <ol class="breadcrumb">
            <li class="breadcrumb-item text-gray">Admin</li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </div>
</div>

{{-- ================================================ --}}
{{-- BARIS WIDGET 1: DATA TRANSAKSI UTAMA --}}
{{-- ================================================ --}}
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-3">
        <div class="card bg-info img-card box-info-shadow">
            <div class="card-body">
                <div class="d-flex">
                    <div class="text-white">
                        <h2 class="mb-0 number-font">{{ $barang }}</h2>
                        <p class="text-white mb-0">Total Barang</p>
                    </div>
                    <div class="ms-auto"><i class="fe fe-package text-white fs-40 me-2 mt-2"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-3">
        <div class="card bg-success img-card box-success-shadow">
            <div class="card-body">
                <div class="d-flex">
                    <div class="text-white">
                        <h2 class="mb-0 number-font">{{ $bm }}</h2>
                        <p class="text-white mb-0">Barang Masuk</p>
                    </div>
                    <div class="ms-auto"><i class="fe fe-arrow-down-circle text-white fs-40 me-2 mt-2"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-3">
        <div class="card bg-danger img-card box-danger-shadow">
            <div class="card-body">
                <div class="d-flex">
                    <div class="text-white">
                        <h2 class="mb-0 number-font">{{ $bk }}</h2>
                        <p class="text-white mb-0">Barang Keluar</p>
                    </div>
                    <div class="ms-auto"><i class="fe fe-arrow-up-circle text-white fs-40 me-2 mt-2"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-3">
        <div class="card bg-purple img-card box-purple-shadow">
            <div class="card-body">
                <div class="d-flex">
                    <div class="text-white">
                        <h2 class="mb-0 number-font">{{ $customer }}</h2>
                        <p class="text-white mb-0">Ruangan</p>
                    </div>
                    <div class="ms-auto"><i class="fe fe-home text-white fs-40 me-2 mt-2"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================================================ --}}
{{-- BARIS WIDGET 2: DATA MASTER CADANGAN (FLEKSIBEL) --}}
{{-- Menggunakan komentar Blade agar tersembunyi dengan aman --}}
{{-- ================================================ --}}
{{-- 
<div class="row">
    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-3">
        <div class="card bg-primary img-card box-primary-shadow">
            <div class="card-body">
                <div class="d-flex">
                    <div class="text-white">
                        <h2 class="mb-0 number-font">{{ $jenis ?? 0 }}</h2>
                        <p class="text-white mb-0">Jenis Barang</p>
                    </div>
                    <div class="ms-auto"><i class="fe fe-folder text-white fs-40 me-2 mt-2"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-3">
        <div class="card bg-secondary img-card box-secondary-shadow">
            <div class="card-body">
                <div class="d-flex">
                    <div class="text-white">
                        <h2 class="mb-0 number-font">{{ $satuan ?? 0 }}</h2>
                        <p class="text-white mb-0">Satuan Barang</p>
                    </div>
                    <div class="ms-auto"><i class="fe fe-grid text-white fs-40 me-2 mt-2"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-3">
        <div class="card bg-warning img-card box-warning-shadow">
            <div class="card-body">
                <div class="d-flex">
                    <div class="text-white">
                        <h2 class="mb-0 number-font">{{ $merk ?? 0 }}</h2>
                        <p class="text-white mb-0">Merk Barang</p>
                    </div>
                    <div class="ms-auto"><i class="fe fe-bookmark text-white fs-40 me-2 mt-2"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-3">
        <div class="card bg-dark img-card box-dark-shadow">
            <div class="card-body">
                <div class="d-flex">
                    <div class="text-white">
                        <h2 class="mb-0 number-font">{{ $user ?? 0 }}</h2>
                        <p class="text-white mb-0">User Sistem</p>
                    </div>
                    <div class="ms-auto"><i class="fe fe-users text-white fs-40 me-2 mt-2"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>
--}}

{{-- ================================================ --}}
{{-- BARIS BAWAH: DUA TABEL TERPISAH (KANAN - KIRI) --}}
{{-- ================================================ --}}
<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header bg-success-transparent">
                <h3 class="card-title text-success font-weight-bold">5 Barang Masuk Terbaru</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="1%">No</th>
                                <th>Nama Barang</th>
                                <th>Ruangan / Asal</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transaksi_masuk as $i => $m)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $m->barang_nama ?? '-' }}</td>
                                <td>{{ $m->ruangan ?? '-' }}</td>
                                <td>{{ $m->tanggal ? \Carbon\Carbon::parse($m->tanggal)->format('d/m/Y') : '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Belum ada barang masuk</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-header bg-danger-transparent">
                <h3 class="card-title text-danger font-weight-bold">5 Barang Keluar Terbaru</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="1%">No</th>
                                <th>Nama Barang</th>
                                <th>Ruangan / Tujuan</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transaksi_keluar as $i => $k)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $k->barang_nama ?? '-' }}</td>
                                <td>{{ $k->ruangan ?? '-' }}</td>
                                <td>{{ $k->tanggal ? \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y') : '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Belum ada barang keluar</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('Master.Layouts.app', ['title' => $title])

@section('content')
<div class="page-header">
    <h1 class="page-title">Laporan Peminjaman</h1>
    <div>
        <ol class="breadcrumb">
            <li class="breadcrumb-item text-gray">Laporan</li>
            <li class="breadcrumb-item active" aria-current="page">Peminjaman</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header justify-content-between">
                <h3 class="card-title">Data</h3>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-12">
                        <label class="fw-bold">Filter Laporan</label>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="tglawal" class="form-control datepicker-date" placeholder="Tanggal Awal">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="tglakhir" class="form-control datepicker-date" placeholder="Tanggal Akhir">
                    </div>
                    <div class="col-md-3">
                        <select name="customer_id" class="form-control form-select">
                            <option value="">Pilih Ruangan</option>
                            @foreach($ruangan as $r)
                                <option value="{{ $r->customer_id }}">{{ $r->customer_nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-control form-select">
                            <option value="">Semua Status</option>
                            <option value="dipinjam">Dipinjam</option>
                            <option value="dikembalikan">Kembali</option>
                        </select>
                    </div>
                    <div class="col-12 mt-2">
                        <button class="btn btn-success-light" onclick="filter()"><i class="fe fe-filter"></i> Filter</button>
                        <button class="btn btn-secondary-light" onclick="resetFilter()"><i class="fe fe-refresh-ccw"></i> Reset</button>
                        <button class="btn btn-primary-light" onclick="printData()"><i class="fe fe-printer"></i> Print</button>
                        <button class="btn btn-danger-light" onclick="pdfData()"><i class="fa fa-file-pdf-o"></i> PDF</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="table-1" class="table table-bordered text-nowrap border-bottom">
                        <thead>
                            <tr>
                                <th width="1%">No</th>
                                <th>Tanggal Pinjam</th>
                                <th>Kode</th>
                                <th>Peminjam</th>
                                <th>Ruangan</th>
                                <th>Barang</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Tanggal Kembali</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let table;
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    $(document).ready(function() { getData(); });

    function getData() {
        table = $('#table-1').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('lap-peminjaman.get') }}",
                data: function(d) {
                    d.tglawal     = $('input[name="tglawal"]').val();
                    d.tglakhir    = $('input[name="tglakhir"]').val();
                    d.customer_id = $('select[name="customer_id"]').val();
                    d.status      = $('select[name="status"]').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'tgl', name: 'pinjam_tanggal' },
                { data: 'pinjam_kode', name: 'pinjam_kode' },
                { data: 'peminjam_nama', name: 'pinjam_nama' },
                { data: 'customer', name: 'customer.customer_nama' },
                { data: 'barang', name: 'barang', orderable: false },
                { data: 'jumlah', name: 'jumlah', orderable: false },
                { data: 'status', name: 'pinjam_status' },
                { data: 'tgl_kembali', name: 'pinjam_tanggal_kembali' },
            ],
        });
    }

    function filter() { table.ajax.reload(); }

    function resetFilter() {
        $('input[name="tglawal"], input[name="tglakhir"]').val('');
        $('select[name="customer_id"], select[name="status"]').val('');
        table.ajax.reload();
    }

    function getParam() {
        return "?tglawal="     + $('input[name="tglawal"]').val() +
               "&tglakhir="    + $('input[name="tglakhir"]').val() +
               "&customer_id=" + $('select[name="customer_id"]').val() +
               "&status="      + $('select[name="status"]').val();
    }

    function printData() {
        var tglawal  = $('input[name="tglawal"]').val();
        var tglakhir = $('input[name="tglakhir"]').val();
        var customer = $('select[name="customer_id"]').val();
        var status   = $('select[name="status"]').val();

        if (tglawal != '' || tglakhir != '' || customer != '' || status != '') {
            window.open("{{ route('lap-peminjaman.print') }}" + getParam(), '_blank');
        } else {
            Swal.fire({
                title: 'Yakin Print Semua Data?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#09ad95',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yakin',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open("{{ route('lap-peminjaman.print') }}", '_blank');
                }
            });
        }
    }

    function pdfData() {
        var tglawal  = $('input[name="tglawal"]').val();
        var tglakhir = $('input[name="tglakhir"]').val();
        var customer = $('select[name="customer_id"]').val();
        var status   = $('select[name="status"]').val();

        if (tglawal != '' || tglakhir != '' || customer != '' || status != '') {
            window.open("{{ route('lap-peminjaman.pdf') }}" + getParam(), '_blank');
        } else {
            Swal.fire({
                title: 'Yakin Export PDF Semua Data?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#09ad95',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yakin',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open("{{ route('lap-peminjaman.pdf') }}", '_blank');
                }
            });
        }
    }
</script>
@endsection
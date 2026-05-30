@extends('Master.Layouts.app', ['title' => $title])

@section('content')
<div class="page-header">
    <h1 class="page-title">Laporan Stok Barang</h1>
    <div>
        <ol class="breadcrumb">
            <li class="breadcrumb-item text-gray">Laporan</li>
            <li class="breadcrumb-item active" aria-current="page">Stok Barang</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header justify-content-between">
                <h3 class="card-title">Data Stok</h3>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-12"><label class="fw-bold">Filter Laporan</label></div>
                    <div class="col-md-3">
                        <input type="text" name="tglawal" class="form-control datepicker-date" placeholder="Tanggal Awal">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="tglakhir" class="form-control datepicker-date" placeholder="Tanggal Akhir">
                    </div>
                    <div class="col-md-3">
                        <select name="customer_id" class="form-control form-select">
                            <option value="">Semua Ruangan</option>
                            @foreach ($customer as $c)
                                <option value="{{ $c->customer_id }}">{{ $c->customer_nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mt-3">
                        <div class="btn-list">
                            <button class="btn btn-success-light" onclick="filter()"><i class="fe fe-filter"></i> Filter</button>
                            <button class="btn btn-secondary-light" onclick="reset()"><i class="fe fe-refresh-ccw"></i> Reset</button>
                            <button class="btn btn-primary-light" onclick="printData()"><i class="fe fe-printer"></i> Print</button>
                            <button class="btn btn-danger-light" onclick="pdfData()"><i class="fa fa-file-pdf-o"></i> PDF</button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="table-1" class="table table-bordered text-nowrap border-bottom">
                        <thead>
                            <tr>
                                <th width="1%">No</th>
                                <th>Kode Barang</th>
                                <th>Barang</th>
                                <th>Ruangan</th>
                                <th>Masuk (+)</th>
                                <th>Keluar (-)</th>
                                <th>Total Stok</th>
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
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    $(document).ready(function() { getData(); });

    function getData() {
        table = $('#table-1').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('lap-sb.getlap-sb') }}",
                data: function(d) {
                    d.tglawal     = $('input[name="tglawal"]').val();
                    d.tglakhir    = $('input[name="tglakhir"]').val();
                    d.customer_id = $('select[name="customer_id"]').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', searchable: false },
                { data: 'barang_kode', name: 'barang_kode' },
                { data: 'barang_nama', name: 'barang_nama' },
                { data: 'customer_nama', name: 'customer_nama' },
                { data: 'jmlmasuk', name: 'jmlmasuk', orderable: false },
                { data: 'jmlkeluar', name: 'jmlkeluar', orderable: false },
                { data: 'totalstok', name: 'totalstok', orderable: false },
            ],
        });
    }

    function filter() { table.ajax.reload(null, false); }

    function reset() {
        $('input[name="tglawal"], input[name="tglakhir"]').val('');
        $('select[name="customer_id"]').val('');
        table.ajax.reload(null, false);
    }

    function getParam() {
        return "?tglawal="     + $('input[name="tglawal"]').val() +
               "&tglakhir="    + $('input[name="tglakhir"]').val() +
               "&customer_id=" + $('select[name="customer_id"]').val();
    }

    // ✅ Route benar lap-sb, bukan lap-peminjaman
    function printData() {
        window.open("{{ route('lap-sb.print') }}" + getParam(), '_blank');
    }

    function pdfData() {
        window.open("{{ route('lap-sb.pdf') }}" + getParam(), '_blank');
    }
</script>
@endsection
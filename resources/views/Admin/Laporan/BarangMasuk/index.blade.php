@extends('Master.Layouts.app', ['title' => $title])

@section('content')
<div class="page-header">
    <h1 class="page-title">Laporan Barang Masuk</h1>
    <div>
        <ol class="breadcrumb">
            <li class="breadcrumb-item text-gray">Laporan</li>
            <li class="breadcrumb-item active" aria-current="page">Barang Masuk</li>
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
                        <label class="fw-bold">Filter Tanggal</label>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="tglawal" class="form-control datepicker-date" placeholder="Tanggal Awal">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="tglakhir" class="form-control datepicker-date" placeholder="Tanggal Akhir">
                    </div>
                    <div class="col-md-3">
                        <select name="ruangan" class="form-control form-select">
                            <option value="">Pilih Ruangan</option>
                            @foreach($ruangan as $r)
                                <option value="{{ $r->customer_id }}">{{ $r->customer_nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mt-2">
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
                                <th>Tanggal Masuk</th>
                                <th>Kode Barang Masuk</th>
                                <th>Kode Barang</th>
                                <th>Ruangan</th>
                                <th>Barang</th>
                                <th>Jumlah Masuk</th>
                                <th>Harga Satuan</th>
                                <th>Total Harga</th>
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
            scrollX: true,
            pageLength: 10,
            order: [],
            ajax: {
                url: "{{ route('lap-bm.getlap-bm') }}",
                data: function(d) {
                    d.tglawal  = $('input[name="tglawal"]').val();
                    d.tglakhir = $('input[name="tglakhir"]').val();
                    d.ruangan  = $('select[name="ruangan"]').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', searchable: false },
                { data: 'tgl', name: 'bm_tanggal' },
                { data: 'bm_kode', name: 'bm_kode' },
                { data: 'barang_kode', name: 'barang_kode' },
                { data: 'customer', name: 'customer_nama' },
                { data: 'barang', name: 'barang_nama' },
                { data: 'bm_jumlah', name: 'bm_jumlah' },
                { data: 'harga_satuan', name: 'barang_harga' },
                { data: 'total_harga', name: 'total_harga' },
            ],
        });
    }

    function filter() {
        var tglawal  = $('input[name="tglawal"]').val();
        var tglakhir = $('input[name="tglakhir"]').val();
        if (tglawal != '' && tglakhir != '') {
            table.ajax.reload(null, false);
        } else {
            Swal.fire({ icon: 'warning', title: 'Perhatian!', text: 'Isi dulu Form Filter Tanggal!' });
        }
    }

    function resetFilter() {
        $('input[name="tglawal"]').val('');
        $('input[name="tglakhir"]').val('');
        $('select[name="ruangan"]').val('');
        table.ajax.reload(null, false);
    }

    function getParam() {
        return "?tglawal=" + $('input[name="tglawal"]').val() +
               "&tglakhir=" + $('input[name="tglakhir"]').val() +
               "&ruangan=" + $('select[name="ruangan"]').val();
    }

    function printData() {
        var tglawal  = $('input[name="tglawal"]').val();
        var tglakhir = $('input[name="tglakhir"]').val();
        if (tglawal != '' && tglakhir != '') {
            window.open("{{ route('lap-bm.print') }}" + getParam(), '_blank');
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
                    window.open("{{ route('lap-bm.print') }}", '_blank');
                }
            });
        }
    }

    function pdfData() {
        var tglawal  = $('input[name="tglawal"]').val();
        var tglakhir = $('input[name="tglakhir"]').val();
        if (tglawal != '' && tglakhir != '') {
            window.open("{{ route('lap-bm.pdf') }}" + getParam(), '_blank');
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
                    window.open("{{ route('lap-bm.pdf') }}", '_blank');
                }
            });
        }
    }
</script>
@endsection
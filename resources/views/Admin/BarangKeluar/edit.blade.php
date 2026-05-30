<!-- MODAL EDIT -->
<div class="modal fade" data-bs-backdrop="static" id="Umodaldemo8">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Ubah Barang Keluar</h6>
                <button aria-label="Close" onclick="resetU()" class="btn-close" data-bs-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <input type="hidden" name="idbkU">
                        <div class="form-group">
                            <label class="form-label">Kode Barang Keluar <span class="text-danger">*</span></label>
                            <input type="text" name="bkkodeU" readonly class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Keluar <span class="text-danger">*</span></label>
                            <input type="text" name="tglkeluarU" class="form-control datepicker-date">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ruangan</label>
                            <!-- GANTI input text jadi select -->
                            <select name="tujuanU" class="form-control">
                                <option value="">Pilih Ruangan</option>
                                @foreach ($customer as $c)
                                    <option value="{{ $c->customer_id }}">{{ $c->customer_nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <!-- TAMBAH field keterangan -->
                            <label class="form-label">Keterangan <span class="text-danger">*</span></label>
                            <textarea name="keteranganU" class="form-control" rows="3" placeholder="Alasan barang keluar..."></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kode Barang <span class="text-danger me-1">*</span>
                                <input type="hidden" id="statusU" value="true">
                                <div class="spinner-border spinner-border-sm d-none" id="loaderkdU" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" autocomplete="off" name="kdbarangU">
                                <button class="btn btn-primary-light" onclick="searchBarangU()" type="button"><i class="fe fe-search"></i></button>
                                <button class="btn btn-success-light" onclick="modalBarangU()" type="button"><i class="fe fe-box"></i></button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Nama Barang</label>
                            <input type="text" class="form-control" id="nmbarangU" readonly>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Satuan</label>
                                    <input type="text" class="form-control" id="satuanU" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jenis</label>
                                    <input type="text" class="form-control" id="jenisU" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jumlah Keluar <span class="text-danger">*</span></label>
                            <input type="text" name="jmlU" class="form-control"
                                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1').replace(/^0[^.]/, '0');">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success d-none" id="btnLoaderU" type="button" disabled="">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Loading...
                </button>
                <a href="javascript:void(0)" onclick="checkFormU()" id="btnSimpanU" class="btn btn-success">
                    Simpan Perubahan <i class="fe fe-check"></i>
                </a>
                <a href="javascript:void(0)" class="btn btn-light" onclick="resetU()" data-bs-dismiss="modal">
                    Batal <i class="fe fe-x"></i>
                </a>
            </div>
        </div>
    </div>
</div>

@section('formEditJS')
<script>
    $('input[name="kdbarangU"]').keypress(function(event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if (keycode == '13') {
            getbarangbyidU($('input[name="kdbarangU"]').val());
        }
    });

function modalBarangU() {
    let ruangan = $("select[name='tujuanU']").val(); // ← ambil ruangan dari form edit
    $('input[name="param"]').val('ubah');
    resetValidU();
    table2.ajax.reload();
    $('#Umodaldemo8').modal('hide');
    setTimeout(function() {
        $('#modalBarang').modal('show');
    }, 400);
}

    function searchBarangU() {
        getbarangbyidU($('input[name="kdbarangU"]').val());
        resetValidU();
    }

    function getbarangbyidU(id) {
        $("#loaderkdU").removeClass('d-none');
        $.ajax({
            type: 'GET',
            url: "{{ url('admin/barang/getbarang') }}/" + id,
            dataType: 'json',
            success: function(data) {
                $("#loaderkdU").addClass('d-none');
                if (data.length > 0) {
                    $("#statusU").val("true");
                    $("#nmbarangU").val(data[0].barang_nama);
                    $("#satuanU").val(data[0].satuan_nama);
                    $("#jenisU").val(data[0].jenisbarang_nama);
                } else {
                    $("#statusU").val("false");
                    $("#nmbarangU").val('');
                    $("#satuanU").val('');
                    $("#jenisU").val('');
                }
            }
        });
    }

    function checkFormU() {
        const tglkeluar   = $("input[name='tglkeluarU']").val();
        const status      = $("#statusU").val();
        const kdbarang    = $("input[name='kdbarangU']").val();
        const jml         = $("input[name='jmlU']").val();
        const keterangan  = $("textarea[name='keteranganU']").val();

        setLoadingU(true);
        resetValidU();

        if (tglkeluar == "") {
            validasi('Tanggal Keluar wajib diisi!', 'warning');
            $("input[name='tglkeluarU']").addClass('is-invalid');
            setLoadingU(false);
            return false;
        } else if (status == "false" || kdbarang == '') {
            validasi('Barang wajib dipilih!', 'warning');
            $("input[name='kdbarangU']").addClass('is-invalid');
            setLoadingU(false);
            return false;
        } else if (jml == "" || jml == "0") {
            validasi('Jumlah Keluar wajib diisi!', 'warning');
            $("input[name='jmlU']").addClass('is-invalid');
            setLoadingU(false);
            return false;
        } else if (keterangan == "") {
            validasi('Keterangan wajib diisi!', 'warning');
            $("textarea[name='keteranganU']").addClass('is-invalid');
            setLoadingU(false);
            return false;
        } else {
            submitFormU();
        }
    }

    function submitFormU() {
        const id = $("input[name='idbkU']").val();
        $.ajax({
            type: 'POST',
            url: "{{ url('admin/barang-keluar/proses_ubah') }}/" + id,
            data: {
                bkkode     : $("input[name='bkkodeU']").val(),
                tglkeluar  : $("input[name='tglkeluarU']").val(),
                barang     : $("input[name='kdbarangU']").val(),
                tujuan     : $("select[name='tujuanU']").val(),
                jml        : $("input[name='jmlU']").val(),
                keterangan : $("textarea[name='keteranganU']").val()
            },
            success: function(data) {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil diubah!",
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    $('#Umodaldemo8').modal('hide');
                    table.ajax.reload(null, false);
                    resetU();
                });
            },
            error: function() {
                Swal.fire({ icon: "error", title: "Gagal menyimpan data!" });
                setLoadingU(false);
            }
        });
    }

    function resetValidU() {
        $("input[name='tglkeluarU']").removeClass('is-invalid');
        $("input[name='kdbarangU']").removeClass('is-invalid');
        $("select[name='tujuanU']").removeClass('is-invalid');
        $("input[name='jmlU']").removeClass('is-invalid');
        $("textarea[name='keteranganU']").removeClass('is-invalid');
    }

    function resetU() {
        resetValidU();
        $("input[name='idbkU']").val('');
        $("input[name='bkkodeU']").val('');
        $("input[name='tglkeluarU']").val('');
        $("input[name='kdbarangU']").val('');
        $("select[name='tujuanU']").val('');
        $("textarea[name='keteranganU']").val('');
        $("input[name='jmlU']").val('0');
        $("#nmbarangU").val('');
        $("#satuanU").val('');
        $("#jenisU").val('');
        $("#statusU").val('false');
        setLoadingU(false);
    }

    function setLoadingU(bool) {
        if (bool == true) {
            $('#btnLoaderU').removeClass('d-none');
            $('#btnSimpanU').addClass('d-none');
        } else {
            $('#btnSimpanU').removeClass('d-none');
            $('#btnLoaderU').addClass('d-none');
        }
    }
</script>
@endsection
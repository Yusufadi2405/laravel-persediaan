<!-- MODAL EDIT CUSTOMER -->
<div class="modal fade" data-bs-backdrop="static" id="Umodaldemo8">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Ubah Ruangan</h6>
                <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="idcustomerU">

                <div class="form-group">
                    <label class="form-label">Nama Ruangan <span class="text-danger">*</span></label>
                    <input type="text" name="customerU" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Kode Ruangan</label>
                    <input type="text" name="notelpU" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Keterangan</label>
                    <textarea name="alamatU" class="form-control" rows="3"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success d-none" id="btnLoaderU" disabled>
                    <span class="spinner-border spinner-border-sm"></span> Loading...
                </button>
                <button class="btn btn-success" id="btnSimpanU" onclick="checkFormU()">Simpan Perubahan</button>
                <button class="btn btn-light" data-bs-dismiss="modal" onclick="resetU()">Batal</button>
            </div>
        </div>
    </div>
</div>

@section('formEditJS')
<script>
function update(data) {
    $("input[name='idcustomerU']").val(data.customer_id);
    $("input[name='customerU']").val(data.customer_nama.replace(/_/g, ' '));
    $("input[name='notelpU']").val(data.customer_notelp);
    $("textarea[name='alamatU']").val(data.customer_alamat.replace(/_/g, ' '));
}

function checkFormU() {
    let nama = $("input[name='customerU']").val();

    setLoadingU(true);
    resetValidU();

    if (nama === '') {
        validasi('Nama Customer wajib diisi', 'warning');
        $("input[name='customerU']").addClass('is-invalid');
        setLoadingU(false);
        return;
    }

    submitFormU();
}

function submitFormU() {
    const id = $("input[name='idcustomerU']").val();

    $.ajax({
        type: 'POST',
        url: "{{ url('admin/customer/proses_ubah') }}/" + id,
        data: {
            customer: $("input[name='customerU']").val(),
            notelp: $("input[name='notelpU']").val(),
            alamat: $("textarea[name='alamatU']").val(),
        },
        success: function () {
            swal({ title: "Ruangan berhasil diubah", type: "success" });
            $('#Umodaldemo8').modal('hide');
            table.ajax.reload(null, false);
            resetU();
        },
        error: function () {
            swal({ title: "Gagal menyimpan data", type: "error" });
            setLoadingU(false);
        }
    });
}

function resetValidU() {
    $("input, textarea").removeClass('is-invalid');
}

function resetU() {
    resetValidU();
    $("input[name='idcustomerU']").val('');
    $("input[name='customerU']").val('');
    $("input[name='notelpU']").val('');
    $("textarea[name='alamatU']").val('');
    setLoadingU(false);
}

function setLoadingU(state) {
    if (state) {
        $('#btnLoaderU').removeClass('d-none');
        $('#btnSimpanU').addClass('d-none');
    } else {
        $('#btnSimpanU').removeClass('d-none');
        $('#btnLoaderU').addClass('d-none');
    }
}
</script>
@endsection
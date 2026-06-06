<!-- MODAL EDIT -->
<div class="modal fade" data-bs-backdrop="static" id="Umodaldemo8">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">Ubah Barang</h6>
                <button onclick="resetU()" aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="idbarangU">
                <div class="row">
                    <div class="col-md-7">
                        <div class="form-group">
                            <label class="form-label">Kode Barang <span class="text-danger">*</span></label>
                            <input type="text" name="kodeU" readonly class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                            <input type="text" name="namaU" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jenis Barang</label>
                            <select name="jenisbarangU" class="form-control">
                                <option value="">-- Pilih --</option>
                                @foreach ($jenisbarang as $jb)
                                    <option value="{{ $jb->jenisbarang_id }}">{{ $jb->jenisbarang_nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Satuan Barang</label>
                            <select name="satuanU" class="form-control">
                                <option value="">-- Pilih --</option>
                                @foreach ($satuan as $s)
                                    <option value="{{ $s->satuan_id }}">{{ $s->satuan_nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Merk Barang</label>
                            <select name="merkU" class="form-control">
                                <option value="">-- Pilih --</option>
                                @foreach ($merk as $m)
                                    <option value="{{ $m->merk_id }}">{{ $m->merk_nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Stok <span class="text-danger">*</span></label>
                            <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1').replace(/^0[^.]/, '0');" name="stokU" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Harga <span class="text-danger">*</span></label>
                            <input type="text" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1').replace(/^0[^.]/, '0');" name="hargaU" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label class="form-label">Foto</label>
                            <center>
                                <img src="{{ url('/assets/default/barang/image.png') }}" width="80%" alt="foto-barang" id="outputImgU">
                            </center>
                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-outline-primary w-50"
                                    onclick="document.getElementById('GetFileU').removeAttribute('capture'); document.getElementById('GetFileU').click();">
                                    <i class="fe fe-upload"></i> Upload
                                </button>
                                <button type="button" class="btn btn-outline-success w-50"
                                    onclick="document.getElementById('GetFileU').setAttribute('capture','environment'); document.getElementById('GetFileU').click();">
                                    <i class="fe fe-camera"></i> Kamera
                                </button>
                            </div>
                            <input class="d-none" id="GetFileU" name="photoU" type="file" onchange="VerifyFileNameAndFileSizeU()" accept=".png,.jpeg,.jpg,.svg">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success d-none" id="btnLoaderU" type="button" disabled>
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
    function checkFormU() {
        const kode = $("input[name='kodeU']").val();
        const nama = $("input[name='namaU']").val();
        const harga = $("input[name='hargaU']").val();
        const stok = $("input[name='stokU']").val();
        setLoadingU(true);
        resetValidU();
        if (kode == "") {
            validasi('Kode Barang wajib di isi!', 'warning');
            $("input[name='kodeU']").addClass('is-invalid');
            setLoadingU(false);
            return false;
        } else if (nama == "") {
            validasi('Nama Barang wajib di isi!', 'warning');
            $("input[name='namaU']").addClass('is-invalid');
            setLoadingU(false);
            return false;
        } else if (harga == "") {
            validasi('Harga Barang wajib di isi!', 'warning');
            $("input[name='hargaU']").addClass('is-invalid');
            setLoadingU(false);
            return false;
        } else if (stok == "") {
            validasi('Stok Awal wajib di isi!', 'warning');
            $("input[name='stokU']").addClass('is-invalid');
            setLoadingU(false);
            return false;
        } else {
            submitFormU();
        }
    }

    function submitFormU() {
        const foto = $('#GetFileU')[0].files;
        var fd = new FormData();
        fd.append('idbarangU', $("input[name='idbarangU']").val());
        fd.append('fotoU', foto[0]);
        fd.append('kodeU', $("input[name='kodeU']").val());
        fd.append('namaU', $("input[name='namaU']").val());
        fd.append('jenisbarangU', $("select[name='jenisbarangU']").val());
        fd.append('satuanU', $("select[name='satuanU']").val());
        fd.append('merkU', $("select[name='merkU']").val());
        fd.append('hargaU', $("input[name='hargaU']").val());
        fd.append('stokU', $("input[name='stokU']").val());

        $.ajax({
            type: 'POST',
            url: "{{ route('barang.proses_ubah') }}",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            processData: false,
            contentType: false,
            dataType: 'json',
            data: fd,
            success: function(data) {
                setLoadingU(false);
                $('#Umodaldemo8').modal('hide');
                Swal.fire({ icon: 'success', title: 'Berhasil diubah!', timer: 2000, showConfirmButton: false });
                table.ajax.reload(null, false);
                resetU();
            },
            error: function(xhr) {
                setLoadingU(false);
                Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan saat mengubah data.' });
            }
        });
    }

    function resetValidU() {
        $("input[name='kodeU'], input[name='namaU'], input[name='hargaU'], input[name='stokU']").removeClass('is-invalid');
        $("select[name='jenisbarangU'], select[name='satuanU'], select[name='merkU']").removeClass('is-invalid');
    }

    function resetU() {
        resetValidU();
        $("input[name='idbarangU'], input[name='kodeU'], input[name='namaU'], input[name='hargaU'], input[name='stokU']").val('');
        $("select[name='jenisbarangU'], select[name='satuanU'], select[name='merkU']").val('');
        $("#outputImgU").attr("src", "{{ url('/assets/default/barang/image.png') }}");
        $("#GetFileU").val('');
        setLoadingU(false);
    }

    function setLoadingU(bool) {
        if (bool) {
            $('#btnLoaderU').removeClass('d-none');
            $('#btnSimpanU').addClass('d-none');
        } else {
            $('#btnSimpanU').removeClass('d-none');
            $('#btnLoaderU').addClass('d-none');
        }
    }

    function fileIsValidU(fileName) {
        var ext = fileName.match(/\.([^\.]+)$/)[1].toLowerCase();
        return ['png','jpeg','jpg','svg'].includes(ext);
    }

    function VerifyFileNameAndFileSizeU() {
        var file = document.getElementById('GetFileU').files[0];
        if (!file) return false;
        if (!fileIsValidU(file.name)) {
            validasi('Format bukan gambar!', 'warning');
            document.getElementById('GetFileU').value = null;
            return false;
        }
        if ((file.size / (1024 * 1024)) > 3) {
            validasi('Ukuran Maximum 3 MB', 'warning');
            document.getElementById('GetFileU').value = null;
            return false;
        }
        document.getElementById('outputImgU').src = window.URL.createObjectURL(file);
        return true;
    }
</script>
@endsection
<!-- resources/views/Admin/Peminjaman/edit.blade.php -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('peminjaman.update') }}" method="POST">
            @csrf
            <input type="hidden" name="idpeminjamanU">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Peminjaman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="pinjam_kodeU" class="form-label">Kode Peminjaman</label>
                        <input type="text" class="form-control" name="pinjam_kodeU" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="customerU" class="form-label">Peminjam</label>
                        <select class="form-select" name="customerU" required>
                            <option value="">-- Pilih Peminjam --</option>
                            @foreach($customer as $c)
                            <option value="{{ $c->customer_id }}">{{ $c->customer_nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="barangU" class="form-label">Barang</label>
                        <select class="form-select" name="barangU" required>
                            <option value="">-- Pilih Barang --</option>
                            @foreach($barang as $b)
                            <option value="{{ $b->barang_id }}">{{ $b->barang_nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="jumlahU" class="form-label">Jumlah</label>
                        <input type="number" class="form-control" name="jumlahU" required>
                    </div>
                    <div class="mb-3">
                        <label for="tgl_pinjamU" class="form-label">Tanggal Pinjam</label>
                        <input type="date" class="form-control" name="tgl_pinjamU" required>
                    </div>
                    <div class="mb-3">
                        <label for="tgl_kembaliU" class="form-label">Tanggal Kembali</label>
                        <input type="date" class="form-control" name="tgl_kembaliU">
                    </div>
                    <div class="mb-3">
                        <label for="statusU" class="form-label">Status</label>
                        <select class="form-select" name="statusU" required>
                            <option value="dipinjam">Dipinjam</option>
                            <option value="dikembalikan">Dikembalikan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>

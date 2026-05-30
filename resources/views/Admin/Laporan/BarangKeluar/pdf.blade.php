<!DOCTYPE html>
<html lang="id">
<?php use Carbon\Carbon; ?>
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        /* Reset default margin & padding */
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        /* Set margin kertas murni nol, biar jarak diatur penuh oleh wrapper */
        @page { 
            size: A4 portrait;
            margin: 0; 
        }
        
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 11pt; 
            line-height: 1.3;
            color: #000; 
            background: #fff;
        }

        /* KUNCI LEBAR HALAMAN AGAR TABEL TIDAK MENTOK KANAN KIRI */
        .pdf-wrapper {
            width: 180mm; 
            margin: 15mm auto; 
        }

        /* KOP SURAT */
        .kop-surat { 
            border-bottom: 2px solid #000; 
            padding-bottom: 5px; 
            margin-bottom: 15px; 
            text-align: center;
        }
        .kop-surat h2 { 
            font-size: 14pt; 
            font-weight: bold; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            margin: 0;
        }
        .kop-surat p { 
            font-size: 9pt; 
            color: #333; 
            margin-top: 2px; 
            font-family: Arial, Helvetica, sans-serif;
        }

        /* JUDUL LAPORAN */
        .judul-laporan { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .judul-laporan h3 { 
            font-size: 12pt; 
            font-weight: bold; 
            text-transform: uppercase; 
            text-decoration: underline;
            margin: 0;
        }
        .judul-laporan p { 
            font-size: 10pt; 
            margin-top: 4px;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* INFO METADATA */
        .meta-table {
            width: 100%;
            border: none;
            margin-bottom: 5px;
        }
        .meta-table td {
            border: none;
            padding: 0;
            font-size: 9pt;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* DATA TABLE */
        table.data-table { 
            border-collapse: collapse; 
            width: 100%; 
            margin-top: 2px; 
            margin-bottom: 25px;
        }
        table.data-table th { 
            background-color: #f2f2f2; 
            color: #000; 
            padding: 6px 4px; 
            font-size: 9pt; 
            font-weight: bold;
            text-align: center; 
            border: 1px solid #000; 
            text-transform: uppercase;
        }
        table.data-table td { 
            border: 1px solid #000; 
            padding: 6px 5px; 
            font-size: 9pt; 
            vertical-align: top;
        }
        
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .bg-alt { background-color: #f9f9f9; }

        /* FOOTER TANDA TANGAN */
        .footer-ttd-table {
            width: 100%;
            margin-top: 15px;
            border: none;
            page-break-inside: avoid;
        }
        .footer-ttd-table td {
            border: none;
            padding: 0;
        }
        .footer-ttd { 
            float: right;
            width: 220px;
            text-align: center; 
            font-size: 10pt;
        }
        .footer-ttd .jabatan {
            font-weight: bold;
            margin-top: 3px;
        }
        .ttd-space {
            height: 55px; 
        }
    </style>
</head>
<body>

    <div class="pdf-wrapper">

        <div class="kop-surat">
            <h2>{{ $web->web_nama }}</h2>
            <p>{{ $web->web_alamat ?? 'Jalan Balai Desa Banjarsari RT 01 RW 01 Kode Pos : 54451' }} &nbsp;|&nbsp; Telp: {{ $web->web_telp ?? '-' }}</p>
        </div>

        <div class="judul-laporan">
            <h3>LAPORAN BARANG KELUAR</h3>
            <p>
                Periode: 
                @if(empty($tglawal))
                    <strong>Semua Tanggal</strong>
                @else
                    <strong>{{ Carbon::parse($tglawal)->translatedFormat('d F Y') }}</strong> s/d <strong>{{ Carbon::parse($tglakhir)->translatedFormat('d F Y') }}</strong>
                @endif
            </p>
        </div>

        <table class="meta-table">
            <tr>
                
                <td style="text-align: right;">Dicetak: {{ Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="4%">NO</th>
                    <th width="14%">TANGGAL KELUAR</th>
                    <th width="16%">KODE BRG KELUAR</th>
                    <th width="14%">KODE BARANG</th>
                    <th class="text-left">NAMA BARANG</th>
                    <th width="8%">JUMLAH</th>
                    <th width="13%">RUANGAN</th>
                    <th width="13%">KETERANGAN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $d)
                <tr class="{{ $index % 2 == 0 ? '' : 'bg-alt' }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $d->bk_tanggal ? Carbon::parse($d->bk_tanggal)->translatedFormat('d/m/Y') : '-' }}</td>
                    <td class="text-center">{{ $d->bk_kode }}</td>
                    <td class="text-center">{{ $d->barang_kode }}</td>
                    <td class="text-left">{{ $d->barang_nama ?? 'Data Barang Dihapus' }}</td>
                    <td class="text-center">{{ $d->bk_jumlah }}</td>
                    <td class="text-center">{{ $d->customer_nama ?? '-' }}</td>
                    <td class="text-left">{{ $d->bk_keterangan ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center; color:#555; padding: 15px; font-style: italic;">
                        Tidak ada data barang keluar yang ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <table class="footer-ttd-table">
            <tr>
                <td style="width: 65%;"></td>
                <td style="width: 35%;">
                    <div class="footer-ttd">
                        <p>{{ $web->web_kota ?? 'Banjarsari' }}, {{ Carbon::now()->translatedFormat('d F Y') }}</p>
                        <p class="jabatan">Petugas Inventaris</p>
                        <div class="ttd-space"></div>
                        <p>...................................................</p>
                    </div>
                </td>
            </tr>
        </table>

    </div>

</body>
</html>
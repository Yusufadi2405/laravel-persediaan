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

        /* PEMBUNGKUS UTAMA: Mengunci lebar konten statis di dalam standar cetak A4 */
        .pdf-wrapper {
            width: 180mm; 
            margin: 15mm auto; 
        }

        /* HEADER / KOP SURAT */
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

        /* METADATA INFO */
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

        /* DATA TABLE STYLING */
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
        table.data-table tfoot td {
            font-weight: bold;
            background-color: #f2f2f2;
        }

        /* UTILITY UTK ALIGNMENT */
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }

        /* FOOTER PENANDATANGANAN */
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
            <h3>LAPORAN STOK BARANG</h3>
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
                <td style="text-align: left;">Ruangan: <strong>{{ isset($customer) && $customer ? $customer->customer_nama : 'Semua Ruangan' }}</strong></td>
                <td style="text-align: right;">Dicetak: {{ Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="4%">NO</th>
                    <th width="15%">KODE BARANG</th>
                    <th class="text-left">NAMA BARANG</th>
                    <th width="23%" class="text-left">RUANGAN</th>
                    <th width="10%">MASUK (+)</th>
                    <th width="10%">KELUAR (-)</th>
                    <th width="10%">STOK</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                    $totalMasuk = 0;
                    $totalKeluar = 0;
                @endphp
                @forelse($data as $d)
                @php
                    $totalMasuk  += $d->jml_masuk;
                    $totalKeluar += $d->jml_keluar;
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="text-center">{{ $d->barang_kode }}</td>
                    <td class="text-left">{{ $d->barang_nama }}</td>
                    <td class="text-left">{{ isset($customer) && $customer ? $customer->customer_nama : 'Semua Ruangan' }}</td>
                    <td class="text-center">{{ $d->jml_masuk }}</td>
                    <td class="text-center">{{ $d->jml_keluar }}</td>
                    <td class="text-center">
                        {{ $d->total_stok }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; color:#555; padding: 15px; font-style: italic;">
                        Tidak ada data stok barang yang ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(count($data) > 0)
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right" style="padding-right: 10px;">TOTAL</td>
                    <td class="text-center">{{ $totalMasuk }}</td>
                    <td class="text-center">{{ $totalKeluar }}</td>
                    <td class="text-center">
                        {{ $totalMasuk - $totalKeluar }}
                    </td>
                </tr>
            </tfoot>
            @endif
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
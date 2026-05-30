<!DOCTYPE html>
<html lang="id">
<?php use Carbon\Carbon; ?>
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 11pt; 
            line-height: 1.3;
            color: #000; 
            background-color: #f5f5f5;
            padding: 20px;
        }

        .document-container {
            background: #fff;
            width: 210mm; 
            margin: 0 auto;
            padding: 15mm;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .no-print { 
            width: 210mm;
            margin: 0 auto 15px auto; 
            display: block;
        }
        .btn {
            padding: 8px 20px;
            font-family: Arial, sans-serif;
            font-size: 10pt;
            font-weight: bold;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-print { background: #1a1a1a; color: #fff; }
        .btn-close { background: #777; color: #fff; margin-left: 8px; }

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
        }
        .kop-surat p { 
            font-size: 9pt; 
            color: #333; 
            margin-top: 2px; 
            font-family: Arial, Helvetica, sans-serif;
        }

        .judul-laporan { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .judul-laporan h3 { 
            font-size: 12pt; 
            font-weight: bold; 
            text-transform: uppercase; 
            text-decoration: underline;
        }
        .judul-laporan p { 
            font-size: 10pt; 
            margin-top: 4px;
            font-family: Arial, Helvetica, sans-serif;
        }

        .meta-info { 
            display: block;
            width: 100%;
            font-size: 9pt; 
            margin-bottom: 5px; 
            font-family: Arial, Helvetica, sans-serif;
        }
        .meta-left { float: left; }
        .meta-right { float: right; text-align: right; }
        .clear { clear: both; }

        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin-top: 2px; 
            margin-bottom: 25px;
        }
        table th { 
            background-color: #f2f2f2; 
            color: #000; 
            padding: 6px 4px; 
            font-size: 9pt; 
            font-weight: bold;
            text-align: center; 
            border: 1px solid #000; 
            text-transform: uppercase;
        }
        table td { 
            border: 1px solid #000; 
            padding: 6px 5px; 
            font-size: 9pt; 
            vertical-align: top;
        }
        
        .text-center { text-align: center; }
        .text-left { text-align: left; }

        .footer-ttd { 
            margin-top: 15px; 
            float: right;
            width: 220px;
            text-align: center; 
            font-size: 10pt;
            page-break-inside: avoid;
        }
        .footer-ttd .jabatan {
            font-weight: bold;
            margin-top: 3px;
        }

        @media print {
            /* TRIK PENTING: Menyembunyikan Header & Footer Bawaan Browser */
            @page {
                size: A4 portrait; 
                margin: 12mm; /* Mengatur ulang batas margin cetak */
            }
            
            /* Menghentikan pencetakan teks URL dan Judul Dokumen di ujung kertas */
            html, body {
                margin: 0;
                padding: 0;
                background: #fff; 
            }
            
            .document-container {
                width: 100% !important;
                padding: 0 !important;
                box-shadow: none;
                background: transparent;
            }
            .no-print { 
                display: none !important; 
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn btn-print">🖨️ Cetak Laporan</button>
        <button onclick="window.close()" class="btn btn-close">✖ Tutup Halaman</button>
    </div>

    <div class="document-container">
        
        <div class="kop-surat">
            <h2>{{ $web->web_nama }}</h2>
            <p>{{ $web->web_alamat ?? 'Jalan Balai Desa Banjarsari RT 01 RW 01 Kode Pos : 54451' }} &nbsp;|&nbsp; Telp: {{ $web->web_telp ?? '-' }}</p>
        </div>

        <div class="judul-laporan">
            <h3>LAPORAN PEMINJAMAN BARANG</h3>
            <p>
                Periode: 
                @if(empty($tglawal))
                    <strong>Semua Tanggal</strong>
                @else
                    <strong>{{ Carbon::parse($tglawal)->translatedFormat('d F Y') }}</strong> s/d <strong>{{ Carbon::parse($tglakhir)->translatedFormat('d F Y') }}</strong>
                @endif
            </p>
        </div>

        <div class="meta-info">
            <div class="meta-left">
                 <strong></strong>
            </div>
            <div class="meta-right">
                Dicetak: {{ Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
            </div>
            <div class="clear"></div>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="4%">NO</th>
                    <th width="11%">KODE</th>
                    <th width="13%">PEMINJAM</th>
                    <th width="13%">RUANGAN</th>
                    <th width="11%">TGL PINJAM</th>
                    <th width="11%">TGL KEMBALI</th>
                    <th class="text-left">BARANG</th>
                    <th width="6%">JML</th>
                    <th width="11%">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $d)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $d->pinjam_kode }}</td>
                    <td class="text-left">{{ $d->pinjam_nama ?? '-' }}</td>
                    <td class="text-left">{{ $d->customer->customer_nama ?? '-' }}</td>
                    <td class="text-center">{{ Carbon::parse($d->pinjam_tanggal)->translatedFormat('d/m/Y') }}</td>
                    <td class="text-center">{{ $d->pinjam_tanggal_kembali ? Carbon::parse($d->pinjam_tanggal_kembali)->translatedFormat('d/m/Y') : 'Belum Kembali' }}</td>
                    <td class="text-left">
                        @foreach($d->details as $det)
                            - {{ $det->barang->barang_nama ?? 'Barang Dihapus' }}<br>
                        @endforeach
                    </td>
                    <td class="text-center">
                        @foreach($d->details as $det)
                            {{ $det->jumlah }}<br>
                        @endforeach
                    </td>
                    <td class="text-center" style="text-transform: uppercase; font-weight: bold;">{{ $d->pinjam_status }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center; color:#555; padding: 15px; font-style: italic;">
                        Tidak ada data peminjaman barang yang ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer-ttd">
            <p>{{ $web->web_kota ?? 'Banjarsari' }}, {{ Carbon::now()->translatedFormat('d F Y') }}</p>
            <p class="jabatan">Petugas Inventaris</p>
            <div class="clear"></div>
            
            <p style="margin-top: 65px;">...................................................</p>
        </div>
        <div class="clear"></div>

    </div>

</body>
</html>
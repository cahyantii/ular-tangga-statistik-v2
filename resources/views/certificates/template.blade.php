<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
        }

        .bingkai {
            border: 6px solid #047857;
            padding: 10px;
            margin: 20px;
        }

        .bingkai-dalam {
            border: 2px solid #047857;
            padding: 50px 60px;
            text-align: center;
        }

        .kop {
            font-size: 14px;
            letter-spacing: 4px;
            color: #64748b;
            text-transform: uppercase;
        }

        .judul {
            font-size: 30px;
            font-weight: bold;
            color: #047857;
            margin: 20px 0 10px;
        }

        .diberikan-kepada {
            font-size: 13px;
            color: #64748b;
            margin-top: 30px;
        }

        .nama {
            font-size: 26px;
            font-weight: bold;
            color: #0f172a;
            margin: 10px 0 20px;
            border-bottom: 1px solid #cbd5e1;
            display: inline-block;
            padding: 0 30px 8px;
        }

        .deskripsi {
            font-size: 13px;
            color: #334155;
            line-height: 1.6;
            margin: 0 auto;
            width: 80%;
        }

        .footer-table {
            width: 100%;
            margin-top: 50px;
        }

        .footer-table td {
            font-size: 11px;
            color: #64748b;
            vertical-align: top;
        }

        .footer-table .kanan {
            text-align: right;
        }

        .label {
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="bingkai">
        <div class="bingkai-dalam">
            <p class="kop">Ular Tangga Statistik Indonesia</p>
            <p class="judul">{{ $certificate->judul }}</p>

            <p class="diberikan-kepada">Dengan bangga diberikan kepada</p>
            <p class="nama">{{ $user->name }}</p>

            <p class="deskripsi">
                Atas keberhasilan menyelesaikan seluruh kategori materi statistik dasar
                dengan akurasi keseluruhan {{ number_format($akurasi, 2) }}% melalui permainan
                edukatif Ular Tangga Statistik.
            </p>

            <table class="footer-table">
                <tr>
                    <td>
                        <span class="label">Nomor Sertifikat</span><br>
                        {{ $certificate->nomor_sertifikat }}
                    </td>
                    <td class="kanan">
                        <span class="label">Tanggal Terbit</span><br>
                        {{ $certificate->issued_at->translatedFormat('d F Y') }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top: 10px;">
                        <span class="label">Kode Verifikasi</span><br>
                        {{ $certificate->verification_code }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>

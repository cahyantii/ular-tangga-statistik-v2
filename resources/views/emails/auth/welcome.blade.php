<x-emails.layout>
    <p style="margin:0 0 4px; font-size:13px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:#0F4CBA;">
        Selamat Bergabung
    </p>
    <h1 style="margin:0 0 12px; font-size:22px; font-weight:800; color:#0F172A;">
        Halo, {{ $user->name }}! 🎉
    </h1>
    <span style="display:inline-block; margin:0 0 20px; padding:4px 10px; background-color:#DCFCE7; color:#15803D; font-size:11px; font-weight:700; border-radius:999px;">
        ✓ Akun Berhasil Dibuat
    </span>

    @if ($isOauth)
        <p style="margin:0 0 20px; font-size:14px; line-height:1.7; color:#475569;">
            Akunmu berhasil dibuat menggunakan akun <strong>{{ $providerLabel }}</strong>. Sekarang kamu sudah dapat
            menggunakan seluruh fitur <strong>Ular Tangga Statistik Indonesia</strong>.
        </p>

        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin:0 0 24px; border-collapse:separate; border-spacing:0 6px;">
            <tr>
                <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; color:#64748B; width:40%;">Login dilakukan menggunakan</td>
                <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; font-weight:700; color:#0F172A;">{{ $providerLabel }}</td>
            </tr>
            <tr>
                <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; color:#64748B;">Waktu login</td>
                <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; font-weight:700; color:#0F172A;">{{ $occurredAt->translatedFormat('d F Y') }}, {{ $occurredAt->format('H:i') }} WIB</td>
            </tr>
        </table>
    @else
        <p style="margin:0 0 20px; font-size:14px; line-height:1.7; color:#475569;">
            Selamat datang di <strong>Ular Tangga Statistik Indonesia</strong>. Akunmu berhasil dibuat menggunakan
            alamat email.
        </p>

        <p style="margin:0 0 10px; font-size:14px; line-height:1.7; color:#475569;">
            Sekarang kamu dapat:
        </p>
        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
            <tr><td style="padding:4px 0; font-size:14px; color:#334155;">🎲&nbsp; Bermain Ular Tangga Statistik</td></tr>
            <tr><td style="padding:4px 0; font-size:14px; color:#334155;">📚&nbsp; Belajar materi statistik</td></tr>
            <tr><td style="padding:4px 0; font-size:14px; color:#334155;">🏆&nbsp; Mengumpulkan achievement</td></tr>
            <tr><td style="padding:4px 0; font-size:14px; color:#334155;">📜&nbsp; Mendapatkan sertifikat</td></tr>
        </table>

        <p style="margin:0 0 24px; font-size:14px; line-height:1.7; color:#475569;">
            Terima kasih telah bergabung bersama kami.
        </p>
    @endif

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto;">
        <tr>
            <td align="center" style="border-radius:14px; background-color:#0F4CBA;">
                <a
                    href="{{ $dashboardUrl }}"
                    target="_blank"
                    style="display:inline-block; padding:14px 32px; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:14px;"
                >
                    Masuk ke Dashboard
                </a>
            </td>
        </tr>
    </table>
</x-emails.layout>

<x-emails.layout>
    <p style="margin:0 0 4px; font-size:13px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:#0F4CBA;">
        Verifikasi Email
    </p>
    <h1 style="margin:0 0 16px; font-size:22px; font-weight:800; color:#0F172A;">
        Halo, {{ $user->name }}! 👋
    </h1>
    <p style="margin:0 0 24px; font-size:14px; line-height:1.7; color:#475569;">
        Terima kasih sudah mendaftar di <strong>Ular Tangga Statistik</strong>. Sebelum mulai bermain dan belajar
        statistik, silakan verifikasi alamat email kamu terlebih dahulu dengan menekan tombol di bawah ini.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 24px;">
        <tr>
            <td align="center" style="border-radius:14px; background-color:#0F4CBA;">
                <a
                    href="{{ $verificationUrl }}"
                    target="_blank"
                    style="display:inline-block; padding:14px 32px; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:14px;"
                >
                    Verifikasi Email Saya
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 8px; font-size:13px; line-height:1.6; color:#94A3B8;">
        Link verifikasi ini berlaku selama {{ (int) config('auth.verification.expire', 60) }} menit. Jika tombol di
        atas tidak berfungsi, salin dan tempel link berikut ke browser kamu:
    </p>
    <p style="margin:0 0 24px; font-size:12px; line-height:1.6; color:#0F4CBA; word-break:break-all;">
        {{ $verificationUrl }}
    </p>

    <p style="margin:0; font-size:13px; line-height:1.6; color:#94A3B8;">
        Jika kamu tidak merasa mendaftar akun ini, abaikan saja email ini.
    </p>
</x-emails.layout>

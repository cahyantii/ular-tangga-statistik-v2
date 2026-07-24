<x-emails.layout>
    <p style="margin:0 0 4px; font-size:13px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:#0F4CBA;">
        Reset Password
    </p>
    <h1 style="margin:0 0 16px; font-size:22px; font-weight:800; color:#0F172A;">
        Halo, {{ $user->name }}!
    </h1>
    <p style="margin:0 0 24px; font-size:14px; line-height:1.7; color:#475569;">
        Kami menerima permintaan untuk mengatur ulang password akun <strong>Ular Tangga Statistik</strong> kamu.
        Tekan tombol di bawah ini untuk membuat password baru.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 24px;">
        <tr>
            <td align="center" style="border-radius:14px; background-color:#0F4CBA;">
                <a
                    href="{{ $resetUrl }}"
                    target="_blank"
                    style="display:inline-block; padding:14px 32px; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:14px;"
                >
                    Reset Password Saya
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 8px; font-size:13px; line-height:1.6; color:#94A3B8;">
        Link ini berlaku selama {{ (int) config('auth.passwords.users.expire', 60) }} menit. Jika tombol di atas
        tidak berfungsi, salin dan tempel link berikut ke browser kamu:
    </p>
    <p style="margin:0 0 24px; font-size:12px; line-height:1.6; color:#0F4CBA; word-break:break-all;">
        {{ $resetUrl }}
    </p>

    <p style="margin:0; font-size:13px; line-height:1.6; color:#94A3B8;">
        Jika kamu tidak meminta reset password, abaikan saja email ini - password kamu tidak akan berubah.
    </p>
</x-emails.layout>

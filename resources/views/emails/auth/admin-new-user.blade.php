<x-emails.layout>
    <p style="margin:0 0 4px; font-size:13px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:#0F4CBA;">
        Notifikasi Admin
    </p>
    <h1 style="margin:0 0 12px; font-size:22px; font-weight:800; color:#0F172A;">
        Halo, Admin
    </h1>
    <span style="display:inline-block; margin:0 0 20px; padding:4px 10px; background-color:#DCFCE7; color:#15803D; font-size:11px; font-weight:700; border-radius:999px;">
        ✓ Pengguna Baru
    </span>

    <p style="margin:0 0 24px; font-size:14px; line-height:1.7; color:#475569;">
        Terdapat pengguna baru yang telah berhasil bergabung ke dalam aplikasi.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin:0 0 24px; border-collapse:separate; border-spacing:0 6px;">
        <tr>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; color:#64748B; width:40%;">Nama</td>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; font-weight:700; color:#0F172A;">{{ $newUser->name }}</td>
        </tr>
        <tr>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; color:#64748B;">Email</td>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; font-weight:700; color:#0F172A;">{{ $newUser->email }}</td>
        </tr>
        <tr>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; color:#64748B;">Metode Registrasi</td>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; font-weight:700; color:#0F172A;">{{ $providerLabel }}</td>
        </tr>
        <tr>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; color:#64748B;">Tanggal Registrasi</td>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; font-weight:700; color:#0F172A;">{{ $occurredAt->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; color:#64748B;">Jam</td>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; font-weight:700; color:#0F172A;">{{ $occurredAt->format('H:i') }} WIB</td>
        </tr>
        <tr>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; color:#64748B;">Total Pengguna Saat Ini</td>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; font-weight:700; color:#0F172A;">{{ number_format($totalUsers, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto;">
        <tr>
            <td align="center" style="border-radius:14px; background-color:#0F4CBA;">
                <a
                    href="{{ $dashboardUrl }}"
                    target="_blank"
                    style="display:inline-block; padding:14px 32px; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:14px;"
                >
                    Lihat Dashboard Admin
                </a>
            </td>
        </tr>
    </table>
</x-emails.layout>

<x-emails.layout>
    <p style="margin:0 0 4px; font-size:13px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:#0F4CBA;">
        Notifikasi Admin
    </p>
    <h1 style="margin:0 0 12px; font-size:22px; font-weight:800; color:#0F172A;">
        Halo, Admin
    </h1>
    <span style="display:inline-block; margin:0 0 20px; padding:4px 10px; background-color:#DCFCE7; color:#15803D; font-size:11px; font-weight:700; border-radius:999px;">
        ✓ Login Berhasil
    </span>

    <p style="margin:0 0 24px; font-size:14px; line-height:1.7; color:#475569;">
        Terdapat aktivitas login baru.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin:0 0 24px; border-collapse:separate; border-spacing:0 6px;">
        <tr>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; color:#64748B; width:40%;">Nama</td>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; font-weight:700; color:#0F172A;">{{ $user->name }}</td>
        </tr>
        <tr>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; color:#64748B;">Email</td>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; font-weight:700; color:#0F172A;">{{ $user->email }}</td>
        </tr>
        <tr>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; color:#64748B;">Metode Login</td>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; font-weight:700; color:#0F172A;">{{ $providerLabel }}</td>
        </tr>
        <tr>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; color:#64748B;">Tanggal</td>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; font-weight:700; color:#0F172A;">{{ $occurredAt->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; color:#64748B;">Jam</td>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; font-weight:700; color:#0F172A;">{{ $occurredAt->format('H:i') }} WIB</td>
        </tr>
        <tr>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; color:#64748B;">IP Address</td>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; font-weight:700; color:#0F172A;">{{ $ipAddress }}</td>
        </tr>
        <tr>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; color:#64748B;">Browser</td>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; font-weight:700; color:#0F172A;">{{ $browserName }}</td>
        </tr>
        <tr>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; color:#64748B;">Status</td>
            <td style="padding:10px 14px; background-color:#F6FAFF; border-radius:12px; font-size:13px; font-weight:700; color:#15803D;">Login Berhasil</td>
        </tr>
    </table>
</x-emails.layout>

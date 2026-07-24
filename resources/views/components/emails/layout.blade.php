<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
</head>
<body style="margin:0; padding:0; background-color:#F6FAFF; font-family: 'Segoe UI', Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F6FAFF; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background-color:#ffffff; border-radius:20px; overflow:hidden; box-shadow:0 20px 45px -15px rgba(15,23,42,0.15);">
                    <tr>
                        <td align="center" style="background-color:#0F4CBA; padding:32px 24px;">
                            <img
                                src="{{ asset('images/brand/logo-baruuuu.png') }}"
                                alt="Logo Ular Tangga Statistik"
                                width="56"
                                height="56"
                                style="display:block; border-radius:12px; background-color:#ffffff; padding:6px;"
                            >
                            <p style="margin:14px 0 0; font-size:16px; font-weight:700; color:#ffffff;">Ular Tangga Statistik</p>
                            <p style="margin:2px 0 0; font-size:12px; color:#D5E3F9;">Belajar Statistik, Asyik &amp; Seru!</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px 32px;">
                            {{ $slot }}
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:20px 24px 28px; border-top:1px solid #EEF2F9;">
                            <p style="margin:0; font-size:12px; color:#94A3B8;">
                                &copy; {{ now()->year }} Ular Tangga Statistik Indonesia. Dibuat untuk literasi statistik masyarakat.
                            </p>
                            <p style="margin:6px 0 0; font-size:12px; color:#94A3B8;">
                                Email ini dikirim otomatis, mohon tidak membalas email ini.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

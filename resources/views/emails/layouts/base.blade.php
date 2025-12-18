{{-- resources/views/emails/layouts/base.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('subject', config('app.name'))</title>
</head>
<body style="margin:0;padding:0;font-family:Arial,Helvetica,sans-serif;background-color:#f3f4f6;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;">
    <tr>
        <td align="center" style="padding:20px 10px;">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;">
                <tr>
                    <td style="background:#6366f1;color:#ffffff;padding:16px 24px;font-size:18px;font-weight:bold;">
                        {{ config('app.name', 'Agency Portal') }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px;">
                        @yield('content')
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 24px;border-top:1px solid #e5e7eb;font-size:12px;color:#6b7280;">
                        This email was sent by {{ config('app.name') }}.  
                        If you have any questions, reply to this email or contact us at {{ \App\Models\Setting::get('contact_email') }}.
                    </td>
                </tr>
            </table>
            <p style="font-size:11px;color:#9ca3af;margin-top:10px;">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </td>
    </tr>
</table>
</body>
</html>
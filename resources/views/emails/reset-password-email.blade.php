{{--
    Pixel Studio — Password Reset Email
    ------------------------------------
    Table-based layout, all styles inline, no external CSS/JS.
    Designed for Laravel: drop into resources/views/emails/reset-password.blade.php
    and render from a custom Notification's toMail(), e.g.:

        return (new MailMessage)
            ->subject('Reset Your Password')
            ->view('emails.reset-password', [
                'url'  => $url,
                'user' => $notifiable,
            ]);

    Variables used below:
        $url                    — the signed password-reset link
        $user->name (nullable)  — recipient's name (falls back gracefully)
        config('app.name')      — "Pixel Studio"

    ICON NOTE: inline SVG AND base64 data-URI <img> tags are both
    stripped by Gmail and most webmail clients — only real, externally
    hosted image files render reliably across clients. So every icon
    below is a plain <img> tag pointing at asset('images/email/...')
    under public/images/. You need these four files to exist:
        public/images/logo.png            (your existing app logo)
        public/images/email/lock.png
        public/images/email/shield.png
        public/images/email/clock.png
    The three under public/images/email/ are provided alongside this
    template — copy them into that folder. Because these are now real
    HTTP(S) URLs, most email clients (Gmail included) will show a
    "display images" prompt on first open unless the recipient's client
    trusts your sending domain — this is normal and expected for any
    image-based email, not a bug in this template.

    NOTE: this top block is written as a Blade comment on purpose,
    instead of a plain HTML comment. Blade still parses double-curly
    expressions inside an ordinary HTML comment, since it has no way
    to know that text is "just documentation" rather than real code.
    A Blade comment is skipped entirely at compile time, so it is the
    only safe place to write illustrative code snippets. One more
    gotcha discovered the hard way: a Blade comment closes at the
    first occurrence of its own closing delimiter, so this note
    itself must never spell that delimiter out literally, or the
    comment ends early and everything after it gets compiled as real
    Blade. Keep any future documentation in here as prose only.
--}}
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>Reset Your Password</title>
<!--[if mso]>
<noscript>
<xml>
<o:OfficeDocumentSettings>
<o:PixelsPerInch>96</o:PixelsPerInch>
</o:OfficeDocumentSettings>
</xml>
</noscript>
<![endif]-->
<style>
    /* Only used where email clients allow a <style> block (Apple Mail, most webmail).
       Everything critical is also inlined below as a fallback. */
    body, table, td, a { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
    table, td { mso-table-lspace:0pt; mso-table-rspace:0pt; }
    img { -ms-interpolation-mode:bicubic; border:0; height:auto; line-height:100%; outline:none; text-decoration:none; }
    body { margin:0; padding:0; width:100% !important; height:100% !important; background-color:#eef1f6; }
    a { color:#4b3fd6; }

    @media screen and (max-width: 600px){
        .email-wrapper{ width:100% !important; }
        .fluid-padding{ padding-left:20px !important; padding-right:20px !important; }
        .stack-column{ display:block !important; width:100% !important; }
        .mobile-hide{ display:none !important; }
        .cta-button{ width:100% !important; }
        .cta-button a{ display:block !important; }
        .h1-mobile{ font-size:22px !important; }
    }
</style>
</head>
<body style="margin:0; padding:0; background-color:#eef1f6; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">

    <!-- Preheader (hidden preview text) -->
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all;">
        A request was made to reset your Pixel Studio password. This link expires in 60 minutes.
    </div>
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all;">
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
    </div>

    <!-- Outer wrapper: sets the page background -->
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef1f6;">
        <tr>
            <td align="center" style="padding:40px 16px;">

                <!-- Email content column -->
                <table role="presentation" class="email-wrapper" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:600px;">

                    <!-- ===================== HEADER ===================== -->
                    <tr>
                        <td align="center" style="padding:0 0 28px 0;">
                            <!-- Logo mark: hexagon, matches the in-app brand -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding-bottom:10px;">
                                        <img src="{{ asset('images/logo.png') }}" width="34" height="34" alt="{{ config('app.name', 'Pixel Studio') }}" style="display:block; border:0; outline:none;">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:17px; font-weight:700; color:#12181f; letter-spacing:0.2px; padding-bottom:4px;">
                                        {{ config('app.name', 'Pixel Studio') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:11.5px; font-weight:500; color:#8a93a3; letter-spacing:0.6px; text-transform:uppercase;">
                                        3D Printing Management System
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ===================== MAIN CARD ===================== -->
                    <tr>
                        <td>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 1px 2px rgba(15,23,42,0.04);">

                                <!-- subtle top accent line -->
                                <tr>
                                    <td style="height:4px; line-height:4px; font-size:0; background:linear-gradient(90deg,#12a3b8,#6c5ce7); background-color:#12a3b8;">&nbsp;</td>
                                </tr>

                                <tr>
                                    <td class="fluid-padding" style="padding:44px 48px 8px 48px;">

                                        <!-- Icon: elegant key/lock mark -->
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                            <tr>
                                                <td align="center" style="padding-bottom:22px;">
                                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="64" height="64" style="width:64px; height:64px; background-color:#f2f5fa; border-radius:16px;">
                                                        <tr>
                                                            <td align="center" valign="middle" style="width:64px; height:64px;">
                                                                <img src="{{ asset('images/email/lock.png') }}" width="26" height="26" alt="" style="display:block; border:0; outline:none;">
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Heading -->
                                        <h1 class="h1-mobile" style="margin:0 0 14px 0; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:24px; line-height:1.3; font-weight:700; color:#12181f; text-align:center;">
                                            Reset Your Password
                                        </h1>

                                        <!-- Greeting + supporting text -->
                                        <p style="margin:0 0 6px 0; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:15px; line-height:1.6; color:#4b5565; text-align:center;">
                                            @if(!empty($user->name))
                                                Hi {{ $user->name }},
                                            @else
                                                Hello,
                                            @endif
                                        </p>
                                        <p style="margin:0 0 32px 0; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:15px; line-height:1.6; color:#4b5565; text-align:center;">
                                            We received a request to reset the password for your {{ config('app.name', 'Pixel Studio') }} account. Click the button below to choose a new one.
                                        </p>

                                        <!-- CTA Button -->
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                            <tr>
                                                <td align="center" style="padding-bottom:32px;">
                                                    <table role="presentation" class="cta-button" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td align="center" style="border-radius:10px; background-color:#12181f;">
                                                                <!--[if mso]>
                                                                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $url }}" style="height:50px;v-text-anchor:middle;width:220px;" arcsize="18%" fillcolor="#12181f" stroke="f">
                                                                <center style="color:#ffffff;font-family:sans-serif;font-size:15px;font-weight:bold;">Reset Password</center>
                                                                </v:roundrect>
                                                                <![endif]-->
                                                                <!--[if !mso]><!-->
                                                                <a href="{{ $url }}" target="_blank" style="display:inline-block; padding:15px 42px; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:15px; font-weight:600; color:#ffffff; text-decoration:none; border-radius:10px; background-color:#12181f;">
                                                                    Reset Password
                                                                </a>
                                                                <!--<![endif]-->
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Expiration notice -->
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                            <tr>
                                                <td align="center" style="padding-bottom:28px;">
                                                    <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td valign="middle" style="padding-right:7px;">
                                                                <img src="{{ asset('images/email/clock.png') }}" width="14" height="14" alt="" style="display:block; border:0; outline:none;">
                                                            </td>
                                                            <td valign="middle" style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:13px; color:#8a93a3;">
                                                                For your security, this link will expire in 60 minutes.
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Divider -->
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="border-top:1px solid #edf0f5; font-size:0; line-height:0;">&nbsp;</td>
                                            </tr>
                                        </table>

                                        <!-- Security notice -->
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:28px;">
                                            <tr>
                                                <td valign="top" width="28" style="padding-right:12px; padding-top:1px;">
                                                    <img src="{{ asset('images/email/shield.png') }}" width="18" height="18" alt="" style="display:block; border:0; outline:none;">
                                                </td>
                                                <td style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:13.5px; line-height:1.6; color:#5b6472;">
                                                    If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged.
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Fallback link -->
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:28px;">
                                            <tr>
                                                <td style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:13px; line-height:1.6; color:#8a93a3;">
                                                    Having trouble with the button? Copy and paste this link into your browser:
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding-top:10px;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f6f8fb; border:1px solid #edf0f5; border-radius:8px;">
                                                        <tr>
                                                            <td style="padding:12px 14px; font-family:'SFMono-Regular',Consolas,'Liberation Mono',Menlo,monospace; font-size:12px; line-height:1.5; color:#4b5565; word-break:break-all;">
                                                                <a href="{{ $url }}" style="color:#4b5565; text-decoration:none;">{{ $url }}</a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>

                                <!-- bottom breathing room inside card -->
                                <tr>
                                    <td style="height:40px; line-height:40px; font-size:0;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- ===================== FOOTER ===================== -->
                    <tr>
                        <td align="center" style="padding:32px 24px 8px 24px;">
                            <p style="margin:0 0 6px 0; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:12.5px; line-height:1.6; color:#9aa3b0;">
                                &copy; {{ date('Y') }} {{ config('app.name', 'Pixel Studio') }}. All rights reserved.
                            </p>
                            <p style="margin:0 0 16px 0; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:12px; letter-spacing:0.4px; color:#b4bac4; text-transform:uppercase;">
                                3D Printing &bull; Design &bull; Innovation
                            </p>
                            <p style="margin:0; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; font-size:12.5px; color:#b4bac4;">
                                <a href="{{ url('/privacy') }}" style="color:#8a93a3; text-decoration:underline;">Privacy Policy</a>
                                &nbsp;&middot;&nbsp;
                                <a href="{{ url('/support') }}" style="color:#8a93a3; text-decoration:underline;">Support</a>
                            </p>
                        </td>
                    </tr>

                </table>
                <!-- /Email content column -->

            </td>
        </tr>
    </table>
    <!-- /Outer wrapper -->

</body>
</html>
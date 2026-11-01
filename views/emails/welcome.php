<?php /** @var string $username */ /** @var string $loginUrl */ ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Welcome to ObscuraX</title>
</head>
<body style="margin:0;padding:0;background:#08060d;font-family:Helvetica,Arial,sans-serif;color:#e9e4f2;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#08060d;padding:32px 12px;">
<tr><td align="center">
<table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#110d1a;border:1px solid #2a2138;border-radius:16px;overflow:hidden;">
<tr><td style="padding:28px 32px 12px;">
<span style="font-family:Georgia,'Times New Roman',serif;font-size:22px;letter-spacing:4px;color:#f2edfb;">OBSCURA<span style="color:#a78bfa;">X</span></span>
</td></tr>
<tr><td style="padding:8px 32px 0;">
<h1 style="margin:0 0 16px;font-family:Georgia,'Times New Roman',serif;font-weight:500;font-size:26px;color:#ffffff;">Welcome to the archive</h1>
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#bdb5cc;">Hello <?= e($username) ?>,</p>
<p style="margin:0 0 22px;font-size:15px;line-height:1.7;color:#bdb5cc;">Your email is verified and your account is active. You can now like articles, comment on investigations and keep a personal record of everything you have read.</p>
<p style="margin:0 0 26px;"><a href="<?= e($loginUrl) ?>" style="display:inline-block;padding:13px 26px;border-radius:999px;background:linear-gradient(110deg,#7c3aed,#a855f7);color:#ffffff;font-size:14px;font-weight:bold;text-decoration:none;">Sign in to ObscuraX</a></p>
<p style="margin:0;font-size:12px;line-height:1.7;color:#8f87a1;">Three desks are waiting: the occult, sexual health and education, and ethical hacking. Start anywhere.</p>
</td></tr>
<tr><td style="padding:22px 32px 28px;border-top:1px solid #251d33;">
<p style="margin:0;font-size:11px;line-height:1.7;color:#6f6880;">ObscuraX — Explore the Unknown. Discover Beyond the Ordinary.</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>

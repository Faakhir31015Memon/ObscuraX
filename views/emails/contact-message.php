<?php /** @var string $name */ /** @var string $email */ /** @var string $subject */ /** @var string $body */ /** @var string $ip */ ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>New contact message: <?= e($subject) ?></title>
</head>
<body style="margin:0;padding:0;background:#08060d;font-family:Helvetica,Arial,sans-serif;color:#e9e4f2;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#08060d;padding:32px 12px;">
<tr><td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#110d1a;border:1px solid #2a2138;border-radius:16px;overflow:hidden;">
<tr><td style="padding:26px 30px;border-bottom:1px solid #251d33;">
<span style="font-family:Georgia,'Times New Roman',serif;font-size:20px;letter-spacing:4px;color:#f2edfb;">OBSCURA<span style="color:#a78bfa;">X</span></span>
<span style="float:right;font-size:11px;letter-spacing:2px;text-transform:uppercase;color:#8f87a1;padding-top:8px;">Contact form</span>
</td></tr>
<tr><td style="padding:26px 30px 0;">
<h1 style="margin:0 0 18px;font-family:Georgia,'Times New Roman',serif;font-weight:500;font-size:22px;color:#ffffff;"><?= e($subject) ?></h1>
<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;font-size:13px;color:#bdb5cc;">
<tr><td style="padding:4px 0;width:90px;color:#8f87a1;">From</td><td style="padding:4px 0;"><?= e($name) ?> &lt;<?= e($email) ?>&gt;</td></tr>
<tr><td style="padding:4px 0;color:#8f87a1;">IP address</td><td style="padding:4px 0;"><?= e($ip) ?></td></tr>
</table>
</td></tr>
<tr><td style="padding:22px 30px 28px;">
<div style="padding:16px 18px;border-radius:12px;border:1px solid #2a2138;background:#0b0910;font-size:14px;line-height:1.75;color:#d8d1e4;white-space:pre-wrap;"><?= e($body) ?></div>
</td></tr>
<tr><td style="padding:18px 30px 24px;border-top:1px solid #251d33;">
<p style="margin:0;font-size:11px;line-height:1.7;color:#6f6880;">Reply directly to this message to reach the sender at <?= e($email) ?>.</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>

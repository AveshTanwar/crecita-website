<?php
/* ================================================
   Crecita Contact Form Handler
   - Sends notification to reach@crecita-it.com
   - Sends auto-reply confirmation to customer
   ================================================ */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://crecita-it.com');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

/* ---- Collect & sanitise ---- */
function clean($val) {
    return htmlspecialchars(strip_tags(trim($val ?? '')));
}

$firstName = clean($_POST['firstName']);
$lastName  = clean($_POST['lastName']);
$email     = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$company   = clean($_POST['company']);
$phone     = clean($_POST['phone']);
$service   = clean($_POST['service']);
$message   = clean($_POST['message']);

/* ---- Validate ---- */
if (!$firstName || !$lastName || !$email || !$service || !$message) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Required fields missing']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid email address']);
    exit;
}

$fullName    = "$firstName $lastName";
$toAddress   = 'reach@crecita-it.com';
$fromAddress = 'noreply@crecita-it.com';

/* ================================================
   1. NOTIFICATION EMAIL → Crecita team
   ================================================ */
$notifySubject = "New Enquiry: $fullName — $service";

$notifyHtml = '<!DOCTYPE html>
<html><head><meta charset="UTF-8"><style>
  body { font-family: Arial, sans-serif; color: #0f172a; background: #f8fafc; margin:0; padding:0; }
  .wrap { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; }
  .header { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); padding: 28px 32px; color: white; }
  .header h1 { margin:0; font-size:20px; font-weight:700; }
  .header p  { margin:6px 0 0; font-size:14px; opacity:0.85; }
  .body   { padding: 28px 32px; }
  .field  { margin-bottom: 18px; }
  .label  { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; margin-bottom:4px; }
  .value  { font-size:15px; color:#0f172a; line-height:1.5; }
  .msg    { background:#f8fafc; border-left:3px solid #3b82f6; padding:14px 16px; border-radius:0 8px 8px 0; font-size:15px; line-height:1.65; color:#475569; }
  .footer { background:#f1f5f9; padding:16px 32px; font-size:12px; color:#94a3b8; text-align:center; }
  .badge  { display:inline-block; background:rgba(59,130,246,0.1); color:#3b82f6; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:600; }
</style></head><body>
<div class="wrap">
  <div class="header">
    <h1>New Contact Form Submission</h1>
    <p>Someone just reached out via crecita-it.com</p>
  </div>
  <div class="body">
    <div class="field"><div class="label">Name</div><div class="value">' . $fullName . '</div></div>
    <div class="field"><div class="label">Email</div><div class="value"><a href="mailto:' . $email . '" style="color:#3b82f6;">' . $email . '</a></div></div>' .
    ($phone   ? '<div class="field"><div class="label">Phone</div><div class="value">' . $phone . '</div></div>' : '') .
    ($company ? '<div class="field"><div class="label">Company</div><div class="value">' . $company . '</div></div>' : '') . '
    <div class="field"><div class="label">Service of Interest</div><div class="value"><span class="badge">' . $service . '</span></div></div>
    <div class="field"><div class="label">Message</div><div class="msg">' . nl2br($message) . '</div></div>
  </div>
  <div class="footer">Crecita Contact Form &nbsp;|&nbsp; crecita-it.com</div>
</div>
</body></html>';

$notifyHeaders  = "MIME-Version: 1.0\r\n";
$notifyHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
$notifyHeaders .= "From: Crecita Website <$fromAddress>\r\n";
$notifyHeaders .= "Reply-To: $fullName <$email>\r\n";

$sent = mail($toAddress, $notifySubject, $notifyHtml, $notifyHeaders);

if (!$sent) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Mail delivery failed']);
    exit;
}

/* ================================================
   2. AUTO-REPLY EMAIL → Customer
   ================================================ */
$replySubject = "We received your message — Crecita";

$replyHtml = '<!DOCTYPE html>
<html><head><meta charset="UTF-8"><style>
  body { font-family: Arial, sans-serif; color: #0f172a; background: #f8fafc; margin:0; padding:0; }
  .wrap { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; }
  .header { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); padding: 36px 32px; color: white; text-align:center; }
  .header h1 { margin:0 0 8px; font-size:24px; font-weight:700; }
  .header p  { margin:0; font-size:15px; opacity:0.85; }
  .body   { padding: 32px; }
  .body p { font-size:15px; color:#475569; line-height:1.7; margin:0 0 16px; }
  .summary { background:#f8fafc; border-radius:10px; padding:20px 24px; margin:24px 0; border:1px solid #e2e8f0; }
  .summary p { margin:0 0 6px; font-size:14px; color:#475569; }
  .summary p:last-child { margin-bottom:0; }
  .summary strong { color:#0f172a; }
  .cta { text-align:center; margin:28px 0 8px; }
  .cta a { display:inline-block; background:#3b82f6; color:white; padding:13px 28px; border-radius:8px; text-decoration:none; font-weight:600; font-size:15px; }
  .divider { border:none; border-top:1px solid #e2e8f0; margin:24px 0; }
  .footer { text-align:center; padding:20px 32px 28px; font-size:13px; color:#94a3b8; }
  .footer a { color:#3b82f6; text-decoration:none; }
</style></head><body>
<div class="wrap">
  <div class="header">
    <h1>We\'ve got your message!</h1>
    <p>Thanks for reaching out, ' . $firstName . '. We\'ll be in touch soon.</p>
  </div>
  <div class="body">
    <p>Hi ' . $firstName . ',</p>
    <p>Thanks for contacting Crecita. We\'ve received your enquiry and one of our team members will get back to you <strong>within 24 hours</strong> with a thoughtful response.</p>
    <div class="summary">
      <p><strong>Service:</strong> ' . $service . '</p>
      <p><strong>Your message:</strong></p>
      <p>' . nl2br($message) . '</p>
    </div>
    <p>In the meantime, feel free to explore our services or reach out to us directly:</p>
    <div class="cta">
      <a href="https://crecita-it.com/services.html">Explore Our Services</a>
    </div>
    <hr class="divider">
    <p style="font-size:14px; color:#94a3b8; margin:0;">If you have anything to add or a question before we respond, just reply to this email — it goes straight to our team.</p>
  </div>
  <div class="footer">
    <strong style="color:#0f172a;">Crecita</strong><br>
    <a href="mailto:reach@crecita-it.com">reach@crecita-it.com</a> &nbsp;|&nbsp; +91 9096 451 662<br>
    <a href="https://crecita-it.com">crecita-it.com</a>
  </div>
</div>
</body></html>';

$replyHeaders  = "MIME-Version: 1.0\r\n";
$replyHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
$replyHeaders .= "From: Crecita <reach@crecita-it.com>\r\n";
$replyHeaders .= "Reply-To: Crecita <reach@crecita-it.com>\r\n";

mail($email, $replySubject, $replyHtml, $replyHeaders);

echo json_encode(['ok' => true]);
?>

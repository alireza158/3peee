<?php
require_once __DIR__ . '/../includes/auth.php';
secure_session_start();
if (current_admin()) { redirect('/admin/dashboard.php'); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die();
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    if (login_admin($email, $password)) { redirect('/admin/dashboard.php'); }
    $error = 'ایمیل یا رمز عبور نادرست است.';
}
?>
<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>ورود ادمین | 3pe</title><link href="/assets/css/bootstrap.rtl.min.css" rel="stylesheet"><style>@font-face{font-family:Vazirmatn;src:url('/assets/fonts/Vazirmatn.woff2') format('woff2')}body{font-family:Vazirmatn,Tahoma,sans-serif;min-height:100vh;background:radial-gradient(900px 500px at 80% -10%,rgba(91,33,182,.18),transparent 60%),linear-gradient(135deg,#f8fafc,#fff);display:grid;place-items:center}.login-card{width:min(440px,92vw);background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:28px;box-shadow:0 25px 70px rgba(15,23,42,.11);padding:34px}.btn-brand{border:0;color:#fff!important;background:linear-gradient(90deg,#5b21b6,#0284c7)}</style></head><body><main class="login-card"><div class="text-center mb-4"><img src="/assets/logo.png" alt="3pe" style="width:70px"><h1 class="h4 fw-bold mt-3">ورود به پنل مدیریت</h1><p class="text-muted mb-0">مدیریت محتوای لندینگ دوره</p></div><?php if($error): ?><div class="alert alert-danger rounded-4"><?= e($error) ?></div><?php endif; ?><form method="post"><?= csrf_field() ?><div class="mb-3"><label class="form-label fw-bold">ایمیل</label><input class="form-control form-control-lg rounded-4" type="email" name="email" value="admin@3pe.ir" required></div><div class="mb-4"><label class="form-label fw-bold">رمز عبور</label><input class="form-control form-control-lg rounded-4" type="password" name="password" required></div><button class="btn btn-brand btn-lg w-100 rounded-4">ورود</button></form></main></body></html>

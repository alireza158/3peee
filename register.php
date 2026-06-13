<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$ageRanges = ['زیر ۱۵ سال', '۱۵ تا ۱۸ سال', '۱۸ تا ۲۵ سال', '۲۵ تا ۳۵ سال', 'بالای ۳۵ سال'];
$currentStatuses = ['دانش‌آموز', 'دانشجو', 'شاغل', 'صاحب کسب‌وکار', 'فریلنسر', 'در حال یادگیری مهارت جدید', 'سایر'];
$webLevels = ['هیچ آشنایی ندارم', 'کمی درباره‌اش شنیده‌ام', 'چند آموزش دیده‌ام ولی پروژه نساخته‌ام', 'یک یا چند پروژه ساده ساخته‌ام', 'تجربه کاری یا پروژه واقعی دارم'];
$skillOptions = ['HTML', 'CSS', 'JavaScript', 'PHP', 'Laravel', 'WordPress', 'Git / GitHub', 'ChatGPT و هوش مصنوعی', 'هیچ‌کدام'];
$goalOptions = ['ورود به بازار کار', 'کسب درآمد از طراحی سایت', 'ساخت سایت برای خودم یا کسب‌وکارم', 'ساخت پروژه و وب‌اپ', 'مهاجرت یا تقویت رزومه', 'علاقه شخصی'];
$aiUsageOptions = ['بله، زیاد', 'بله، گاهی', 'فقط اسمش را شنیده‌ام', 'خیر، اصلاً استفاده نکرده‌ام'];
$learningTypes = ['حضوری', 'آنلاین زنده', 'ویدیوهای ضبط‌شده', 'ترکیبی', 'فرقی ندارد'];
$contactOptions = ['تماس تلفنی', 'پیامک', 'واتساپ', 'تلگرام', 'دایرکت اینستاگرام'];

$settings = read_json('settings.json', []);
if (!is_array($settings)) {
    $settings = [];
}
function registration_setting(array $settings, string $key, string $default): string
{
    $value = trim((string)($settings[$key] ?? ''));
    return $value !== '' ? $value : $default;
}
$registrationSuccess = [
    'title' => registration_setting($settings, 'registration_success_title', 'ثبت‌نامت با موفقیت انجام شد 🎉'),
    'description' => registration_setting($settings, 'registration_success_description', 'برای دریافت آموزش رایگان روی دکمه زیر کلیک کن.'),
    'button_text' => registration_setting($settings, 'registration_success_button_text', 'دریافت آموزش رایگان'),
    'redirect_url' => registration_setting($settings, 'registration_success_redirect_url', '#'),
    'auto_redirect_enabled' => !empty($settings['registration_auto_redirect_enabled']),
    'auto_redirect_seconds' => max(1, (int)($settings['registration_auto_redirect_seconds'] ?? 3)),
];

$errors = [];
$success = false;
$storageError = null;
$form = [
    'name' => '', 'mobile' => '', 'city' => '', 'age_range' => '', 'current_status' => '',
    'web_level' => '', 'skills' => [], 'goals' => [], 'ai_usage' => '', 'learning_type' => '',
    'preferred_contact' => '', 'message' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $errors[] = 'درخواست نامعتبر است. لطفاً صفحه را دوباره بارگذاری کنید.';
    }

    $form['name'] = clean_text($_POST['name'] ?? '', 120);
    $form['mobile'] = normalize_iran_mobile((string) ($_POST['mobile'] ?? ''));
    $form['city'] = clean_text($_POST['city'] ?? '', 80);
    $form['age_range'] = in_array($_POST['age_range'] ?? '', $ageRanges, true) ? (string) $_POST['age_range'] : '';
    $form['current_status'] = in_array($_POST['current_status'] ?? '', $currentStatuses, true) ? (string) $_POST['current_status'] : '';
    $form['web_level'] = in_array($_POST['web_level'] ?? '', $webLevels, true) ? (string) $_POST['web_level'] : '';
    $form['skills'] = array_values(array_intersect((array) ($_POST['skills'] ?? []), $skillOptions));
    $form['goals'] = array_values(array_intersect((array) ($_POST['goals'] ?? []), $goalOptions));
    $form['ai_usage'] = in_array($_POST['ai_usage'] ?? '', $aiUsageOptions, true) ? (string) $_POST['ai_usage'] : '';
    $form['learning_type'] = in_array($_POST['learning_type'] ?? '', $learningTypes, true) ? (string) $_POST['learning_type'] : '';
    $form['preferred_contact'] = in_array($_POST['preferred_contact'] ?? '', $contactOptions, true) ? (string) $_POST['preferred_contact'] : '';
    $form['message'] = clean_multiline($_POST['message'] ?? '', 1500);

    if ($form['name'] === '') {
        $errors[] = 'نام و نام خانوادگی را وارد کنید.';
    }
    if ($form['mobile'] === '') {
        $errors[] = 'شماره موبایل را وارد کنید.';
    } elseif (!is_valid_iran_mobile($form['mobile'])) {
        $errors[] = 'شماره موبایل باید با فرمت معتبر ایران مثل 09123456789 باشد.';
    }
    foreach (['current_status' => 'وضعیت فعلی', 'web_level' => 'سطح آشنایی با طراحی سایت', 'ai_usage' => 'میزان استفاده از هوش مصنوعی', 'learning_type' => 'ترجیح نوع آموزش', 'preferred_contact' => 'بهترین روش ارتباط'] as $key => $label) {
        if ($form[$key] === '') {
            $errors[] = $label . ' را انتخاب کنید.';
        }
    }
    if (!$form['skills']) {
        $errors[] = 'حداقل یک گزینه از مهارت‌ها را انتخاب کنید.';
    }
    if (!$form['goals']) {
        $errors[] = 'حداقل یک هدف آموزشی را انتخاب کنید.';
    }

    if (!$errors) {
        $now = now_string();
        $record = $form + [
            'id' => 0,
            'admin_status' => 'جدید',
            'admin_note' => '',
            'created_at' => $now,
            'updated_at' => $now,
            'ip_address' => clean_text($_SERVER['REMOTE_ADDR'] ?? '', 80),
            'user_agent' => clean_text($_SERVER['HTTP_USER_AGENT'] ?? '', 500),
        ];
        if (append_json_array_record_locked('registrations.json', $record, $storageError)) {
            $success = true;
            $form = ['name' => '', 'mobile' => '', 'city' => '', 'age_range' => '', 'current_status' => '', 'web_level' => '', 'skills' => [], 'goals' => [], 'ai_usage' => '', 'learning_type' => '', 'preferred_contact' => '', 'message' => ''];
        } else {
            $errors[] = $storageError ?: 'ثبت اطلاعات انجام نشد. لطفاً دوباره تلاش کنید.';
        }
    }
}

function checked_card(string $name, string $value, $current): string
{
    $selected = is_array($current) ? in_array($value, $current, true) : $current === $value;
    return $selected ? 'checked' : '';
}
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ثبت نام رایگان دوره آموزشی | 3pe.ir</title>
    <link rel="icon" type="image/png" href="assets/logo.png">
    <link href="assets/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        @font-face{font-family:Vazirmatn;src:url('assets/fonts/Vazirmatn.woff2') format('woff2');font-weight:100 900;font-style:normal;font-display:swap}
        :root{--primary:#4f46e5;--primary2:#7c3aed;--secondary:#0ea5e9;--success:#16a34a;--dark:#0f172a;--muted:#64748b;--line:rgba(226,232,240,.92);--danger:#dc2626}
        *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;font-family:Vazirmatn,Tahoma,sans-serif;font-size:14px;color:var(--dark);min-height:100vh;background:radial-gradient(circle at 82% 8%,rgba(124,58,237,.22),transparent 28%),radial-gradient(circle at 10% 72%,rgba(14,165,233,.18),transparent 30%),linear-gradient(145deg,#f8fafc 0%,#eef2ff 48%,#ecfeff 100%);overflow-x:hidden}body:before{content:"";position:fixed;inset:0;background-image:linear-gradient(rgba(15,23,42,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(15,23,42,.035) 1px,transparent 1px);background-size:38px 38px;mask-image:linear-gradient(to bottom,rgba(0,0,0,.7),transparent);pointer-events:none}a{text-decoration:none}.page-wrapper{position:relative;width:100%;max-width:1040px;margin:0 auto;padding:36px 14px}.form-shell{overflow:hidden;border-radius:30px;background:rgba(255,255,255,.88);border:1px solid rgba(255,255,255,.75);box-shadow:0 28px 80px rgba(15,23,42,.14);backdrop-filter:blur(18px);animation:shellIn .55s ease both}@keyframes shellIn{from{opacity:0;transform:translateY(18px) scale(.985)}to{opacity:1;transform:none}}
        .form-header{position:relative;overflow:hidden;color:#fff;padding:38px 34px;background:radial-gradient(circle at 14% 18%,rgba(56,189,248,.34),transparent 25%),radial-gradient(circle at 86% 10%,rgba(168,85,247,.36),transparent 28%),linear-gradient(135deg,#020617 0%,#111827 44%,#1e1b4b 76%,#312e81 100%)}.form-header:before{content:"";position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.055) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.055) 1px,transparent 1px);background-size:32px 32px;mask-image:linear-gradient(to bottom,rgba(0,0,0,.9),transparent)}.form-header:after{content:"";position:absolute;width:280px;height:280px;border-radius:999px;left:-110px;top:-120px;background:linear-gradient(135deg,rgba(14,165,233,.30),rgba(124,58,237,.18));filter:blur(6px)}.header-content{position:relative;z-index:2}.header-top{display:flex;justify-content:space-between;align-items:center;gap:14px;margin-bottom:24px}.brand{display:flex;align-items:center;gap:11px}.brand-icon{width:48px;height:48px;border-radius:18px;display:grid;place-items:center;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.20);font-size:22px;box-shadow:inset 0 1px 0 rgba(255,255,255,.24)}.brand-name{font-weight:950;font-size:19px}.brand-subtitle{color:#bfdbfe;font-size:12px}.header-tag{display:inline-flex;align-items:center;gap:8px;padding:9px 14px;border-radius:999px;font-size:12px;color:#e0f2fe;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.16);white-space:nowrap}.header-title{max-width:820px;font-size:clamp(1.55rem,3.3vw,2.25rem);line-height:1.8;font-weight:950;margin:0 0 10px;letter-spacing:-.4px}.header-title span{color:#93c5fd}.header-desc{max-width:760px;margin:0;color:#dbeafe;font-size:14px;line-height:2}.header-features{display:flex;flex-wrap:wrap;gap:9px;margin-top:21px}.feature-pill{display:inline-flex;align-items:center;gap:7px;padding:8px 12px;border-radius:999px;background:rgba(15,23,42,.34);border:1px solid rgba(255,255,255,.14);font-size:12px;font-weight:850;color:#e0f2fe}.feature-pill:before{content:"✓";display:grid;place-items:center;width:18px;height:18px;border-radius:50%;background:rgba(125,211,252,.18);color:#7dd3fc}
        .form-body{padding:26px}.alert{border-radius:20px}.success-card{position:relative;overflow:hidden;text-align:center;padding:30px;border-radius:26px;background:radial-gradient(circle at top,rgba(34,197,94,.12),transparent 36%),linear-gradient(180deg,#fff,#f8fafc);border:1px solid #dcfce7;box-shadow:0 18px 48px rgba(15,23,42,.08);animation:popIn .45s ease both}.success-icon{width:70px;height:70px;margin:0 auto 14px;border-radius:24px;display:grid;place-items:center;color:var(--success);background:#dcfce7;font-size:32px;box-shadow:0 14px 30px rgba(22,163,74,.18)}.success-card h2{font-size:clamp(1.25rem,3vw,1.7rem);font-weight:950;line-height:1.8}.success-card p{color:#475569;line-height:2}.success-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:52px;padding:13px 24px;border-radius:17px;color:#fff!important;font-weight:950;background:linear-gradient(135deg,var(--success),var(--secondary));box-shadow:0 16px 36px rgba(14,165,233,.24);transition:.22s ease}.success-btn:hover{transform:translateY(-2px);box-shadow:0 22px 44px rgba(14,165,233,.32)}.redirect-countdown{font-size:12px;color:var(--muted);margin-top:12px}
        .question-card{position:relative;margin-bottom:18px;padding:21px;border-radius:24px;background:rgba(255,255,255,.94);border:1px solid var(--line);box-shadow:0 14px 36px rgba(15,23,42,.055);animation:rise .5s ease both;transition:.22s ease}.question-card:hover{transform:translateY(-2px);border-color:rgba(79,70,229,.25);box-shadow:0 20px 46px rgba(15,23,42,.08)}.question-card:nth-child(2){animation-delay:.04s}.question-card:nth-child(3){animation-delay:.08s}.question-card:nth-child(4){animation-delay:.12s}@keyframes rise{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}@keyframes popIn{from{opacity:0;transform:scale(.96) translateY(12px)}to{opacity:1;transform:none}}.section-badge{display:inline-flex;align-items:center;gap:8px;margin-bottom:12px;padding:7px 11px;border-radius:999px;background:rgba(79,70,229,.09);color:var(--primary);font-weight:900;font-size:12px}.question-title{font-size:1.04rem;font-weight:950;margin-bottom:15px}.form-label{font-size:13px;color:#334155}.form-control,.form-select{border-radius:15px;border:1px solid rgba(15,23,42,.12);padding:.75rem .9rem;font-size:13.5px;background:#f8fafc;transition:.2s ease}.form-control:focus,.form-select:focus{background:#fff;border-color:rgba(79,70,229,.55);box-shadow:0 0 0 .22rem rgba(79,70,229,.11)}textarea.form-control{line-height:1.9}.option-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.option-card{display:block;height:100%;cursor:pointer}.option-card input{position:absolute;opacity:0;pointer-events:none}.option-content{height:100%;min-height:48px;border:1px solid rgba(15,23,42,.10);border-radius:16px;padding:12px 13px;background:#f8fafc;color:#1e293b;font-weight:800;font-size:13px;transition:.2s ease;display:flex;align-items:center;gap:9px}.option-content:before{content:"";width:17px;height:17px;border-radius:50%;border:2px solid #cbd5e1;background:#fff;flex:0 0 17px;transition:.2s ease}.option-card:hover .option-content{border-color:#a5b4fc;background:#f1f5ff;color:#3730a3}.option-card input[type="checkbox"] + .option-content:before{border-radius:6px}.option-card input:checked + .option-content{border-color:rgba(79,70,229,.72);background:linear-gradient(135deg,rgba(79,70,229,.12),rgba(14,165,233,.10));box-shadow:0 12px 24px rgba(79,70,229,.10);color:#312e81}.option-card input:checked + .option-content:before{background:linear-gradient(135deg,var(--primary),var(--secondary));border-color:transparent;box-shadow:inset 0 0 0 4px #fff}.submit-box{padding:22px;border-radius:24px;background:linear-gradient(135deg,rgba(79,70,229,.10),rgba(14,165,233,.10));border:1px solid rgba(79,70,229,.13)}.btn-brand{border:0;color:#fff!important;background:linear-gradient(90deg,var(--primary),var(--secondary));box-shadow:0 18px 36px rgba(79,70,229,.22);font-weight:950;transition:.22s ease}.btn-brand:hover{transform:translateY(-2px);box-shadow:0 24px 48px rgba(79,70,229,.30)}.btn-brand:active{transform:translateY(0) scale(.99)}.small-note{color:var(--muted);line-height:2;font-size:12.5px}@media(max-width:767px){body{font-size:13.5px}.page-wrapper{padding:16px 9px}.form-shell{border-radius:24px}.form-header{padding:28px 18px}.header-top{align-items:flex-start;flex-direction:column}.header-tag{width:100%;justify-content:center}.form-body{padding:16px 12px}.question-card{padding:16px;border-radius:20px;margin-bottom:14px}.option-grid{grid-template-columns:1fr}.option-content{min-height:48px}.submit-box{padding:18px}.btn-brand,.success-btn{width:100%;min-height:52px}.success-card{padding:24px 16px}}@media(prefers-reduced-motion:reduce){*,*:before,*:after{animation:none!important;transition:none!important;scroll-behavior:auto!important}}
    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="form-shell">
        <header class="form-header">
            <div class="header-content">
                <div class="header-top">
                    <a class="brand text-white" href="index.php">
                        <span class="brand-icon">🚀</span>
                        <span><span class="brand-name d-block">3pe.ir</span><span class="brand-subtitle">دوره طراحی سایت با هوش مصنوعی</span></span>
                    </a>
                    <span class="header-tag">✨ دسترسی رایگان بعد از ثبت‌نام</span>
                </div>
                <h1 class="header-title">ثبت‌نام دوره رایگان <span>طراحی سایت با هوش مصنوعی</span></h1>
                <p class="header-desc">اطلاعاتت رو وارد کن تا دسترسی به آموزش رایگان برات فعال بشه و مسیر طراحی سایت با هوش مصنوعی رو از همین امروز شروع کنی.</p>
                <div class="header-features"><span class="feature-pill">کاملاً رایگان</span><span class="feature-pill">مناسب شروع از صفر</span><span class="feature-pill">آموزش کاربردی و پروژه‌محور</span></div>
            </div>
        </header>

        <main class="form-body">
            <?php if ($success): ?>
                <div class="success-card mb-4" id="registrationSuccess" data-auto-redirect="<?= $registrationSuccess['auto_redirect_enabled'] ? '1' : '0' ?>" data-redirect-url="<?= e($registrationSuccess['redirect_url']) ?>" data-redirect-seconds="<?= e($registrationSuccess['auto_redirect_seconds']) ?>">
                    <div class="success-icon" aria-hidden="true">✓</div>
                    <h2 class="mb-2"><?= e($registrationSuccess['title']) ?></h2>
                    <p class="mb-3"><?= e($registrationSuccess['description']) ?></p>
                    <a class="success-btn" href="<?= e($registrationSuccess['redirect_url']) ?>"><?= e($registrationSuccess['button_text']) ?> ↗</a>
                    <div class="redirect-countdown" id="redirectCountdown" hidden></div>
                </div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="alert alert-danger border-0 shadow-sm p-4 mb-4">
                    <div class="fw-black mb-2">لطفاً موارد زیر را اصلاح کنید:</div>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="post" action="register.php" novalidate>
                <?= csrf_field() ?>
                <section class="question-card">
                    <span class="section-badge">بخش ۱: اطلاعات اولیه</span>
                    <h2 class="question-title">اطلاعات تماس و وضعیت فعلی شما</h2>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label fw-bold" for="name">نام و نام خانوادگی <span class="text-danger">*</span></label><input class="form-control" id="name" name="name" value="<?= e($form['name']) ?>" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold" for="mobile">شماره موبایل <span class="text-danger">*</span></label><input class="form-control" id="mobile" name="mobile" inputmode="tel" dir="ltr" value="<?= e($form['mobile']) ?>" placeholder="09123456789" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold" for="city">شهر محل سکونت</label><input class="form-control" id="city" name="city" value="<?= e($form['city']) ?>"></div>
                        <div class="col-md-6"><label class="form-label fw-bold" for="age_range">سن</label><select class="form-select" id="age_range" name="age_range"><option value="">انتخاب کنید…</option><?php foreach ($ageRanges as $option): ?><option value="<?= e($option) ?>" <?= $form['age_range'] === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></div>
                        <div class="col-12"><label class="form-label fw-bold">وضعیت فعلی <span class="text-danger">*</span></label><div class="option-grid"><?php foreach ($currentStatuses as $option): ?><label class="option-card"><input type="radio" name="current_status" value="<?= e($option) ?>" <?= checked_card('current_status', $option, $form['current_status']) ?>><span class="option-content"><?= e($option) ?></span></label><?php endforeach; ?></div></div>
                    </div>
                </section>

                <section class="question-card">
                    <span class="section-badge">بخش ۲: سطح آشنایی با طراحی سایت</span>
                    <h2 class="question-title">اکنون در چه سطحی هستید؟</h2>
                    <div class="mb-4"><label class="form-label fw-bold">سطح آشنایی <span class="text-danger">*</span></label><div class="option-grid"><?php foreach ($webLevels as $option): ?><label class="option-card"><input type="radio" name="web_level" value="<?= e($option) ?>" <?= checked_card('web_level', $option, $form['web_level']) ?>><span class="option-content"><?= e($option) ?></span></label><?php endforeach; ?></div></div>
                    <div><label class="form-label fw-bold">مهارت‌هایی که با آن‌ها آشنایی دارید <span class="text-danger">*</span></label><div class="option-grid"><?php foreach ($skillOptions as $option): ?><label class="option-card"><input type="checkbox" name="skills[]" value="<?= e($option) ?>" <?= checked_card('skills', $option, $form['skills']) ?>><span class="option-content"><?= e($option) ?></span></label><?php endforeach; ?></div></div>
                </section>

                <section class="question-card">
                    <span class="section-badge">بخش ۳: هدف و نیاز کاربر</span>
                    <h2 class="question-title">هدف شما از یادگیری چیست؟</h2>
                    <div class="mb-4"><label class="form-label fw-bold">هدف اصلی از یادگیری طراحی سایت <span class="text-danger">*</span></label><div class="option-grid"><?php foreach ($goalOptions as $option): ?><label class="option-card"><input type="checkbox" name="goals[]" value="<?= e($option) ?>" <?= checked_card('goals', $option, $form['goals']) ?>><span class="option-content"><?= e($option) ?></span></label><?php endforeach; ?></div></div>
                    <div><label class="form-label fw-bold">میزان استفاده از ChatGPT یا ابزارهای هوش مصنوعی <span class="text-danger">*</span></label><div class="option-grid"><?php foreach ($aiUsageOptions as $option): ?><label class="option-card"><input type="radio" name="ai_usage" value="<?= e($option) ?>" <?= checked_card('ai_usage', $option, $form['ai_usage']) ?>><span class="option-content"><?= e($option) ?></span></label><?php endforeach; ?></div></div>
                </section>

                <section class="question-card">
                    <span class="section-badge">بخش ۴: نوع آموزش و ارتباط</span>
                    <h2 class="question-title">بهترین مسیر ارتباط و آموزش برای شما</h2>
                    <div class="mb-4"><label class="form-label fw-bold">ترجیح نوع آموزش <span class="text-danger">*</span></label><div class="option-grid"><?php foreach ($learningTypes as $option): ?><label class="option-card"><input type="radio" name="learning_type" value="<?= e($option) ?>" <?= checked_card('learning_type', $option, $form['learning_type']) ?>><span class="option-content"><?= e($option) ?></span></label><?php endforeach; ?></div></div>
                    <div class="mb-4"><label class="form-label fw-bold">بهترین روش ارتباط <span class="text-danger">*</span></label><div class="option-grid"><?php foreach ($contactOptions as $option): ?><label class="option-card"><input type="radio" name="preferred_contact" value="<?= e($option) ?>" <?= checked_card('preferred_contact', $option, $form['preferred_contact']) ?>><span class="option-content"><?= e($option) ?></span></label><?php endforeach; ?></div></div>
                    <label class="form-label fw-bold" for="message">توضیحات بیشتر</label><textarea class="form-control" id="message" name="message" rows="5" placeholder="اگر نکته‌ای هست که مشاور دوره باید بداند، اینجا بنویسید."><?= e($form['message']) ?></textarea>
                </section>

                <div class="submit-box text-center">
                    <button class="btn btn-brand btn-lg rounded-pill px-5 py-3" type="submit">ثبت و دریافت دوره رایگان</button>
                    <p class="small-note mb-0 mt-3">اطلاعات شما با امنیت در پنل ادمین ثبت می‌شود و فقط برای فعال‌سازی دسترسی آموزش رایگان استفاده خواهد شد.</p>
                </div>
            </form>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const success = document.getElementById('registrationSuccess');
        if (!success) return;
        const autoRedirect = success.dataset.autoRedirect === '1';
        const redirectUrl = success.dataset.redirectUrl || '#';
        let secondsLeft = Number(success.dataset.redirectSeconds || 3);
        const countdown = document.getElementById('redirectCountdown');
        if (autoRedirect && redirectUrl !== '#') {
            countdown.hidden = false;
            countdown.textContent = 'انتقال خودکار تا ' + secondsLeft + ' ثانیه دیگر انجام می‌شود.';
            const timer = setInterval(function () {
                secondsLeft -= 1;
                countdown.textContent = 'انتقال خودکار تا ' + secondsLeft + ' ثانیه دیگر انجام می‌شود.';
                if (secondsLeft <= 0) {
                    clearInterval(timer);
                    window.location.href = redirectUrl;
                }
            }, 1000);
        }
    });
</script>
</body>
</html>

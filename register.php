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
        :root{--primary:#5b21b6;--primary2:#7c3aed;--secondary:#0284c7;--dark:#0f172a;--muted:#64748b;--line:rgba(226,232,240,.92)}
        *{box-sizing:border-box} body{margin:0;font-family:Vazirmatn,Tahoma,sans-serif;color:#0f172a;min-height:100vh;background:radial-gradient(circle at top right,rgba(99,102,241,.18),transparent 32%),radial-gradient(circle at bottom left,rgba(14,165,233,.16),transparent 30%),linear-gradient(180deg,#f8fafc,#eef2ff)}
        a{text-decoration:none}.page-wrapper{width:100%;max-width:1020px;margin:0 auto;padding:42px 16px}.form-shell{overflow:hidden;border-radius:32px;background:rgba(255,255,255,.95);border:1px solid var(--line);box-shadow:0 28px 90px rgba(15,23,42,.12);backdrop-filter:blur(16px)}
        .form-header{position:relative;overflow:hidden;color:#fff;padding:46px 38px;background:radial-gradient(circle at 12% 18%,rgba(56,189,248,.36),transparent 28%),radial-gradient(circle at 88% 12%,rgba(168,85,247,.36),transparent 30%),linear-gradient(135deg,#020617 0%,#111827 38%,#1e1b4b 72%,#312e81 100%)}
        .form-header:before{content:"";position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.055) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.055) 1px,transparent 1px);background-size:34px 34px;mask-image:linear-gradient(to bottom,rgba(0,0,0,.88),transparent)}.header-content{position:relative;z-index:2}.header-top{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:28px}.brand{display:flex;align-items:center;gap:12px}.brand-icon{width:52px;height:52px;border-radius:20px;display:grid;place-items:center;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);font-size:24px}.brand-name{font-weight:950;font-size:21px}.brand-subtitle{color:#bfdbfe;font-size:13px}.header-tag{display:inline-flex;align-items:center;gap:8px;padding:10px 15px;border-radius:999px;font-size:13px;color:#e0f2fe;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.16);white-space:nowrap}.header-title{max-width:800px;font-size:clamp(1.75rem,4vw,2.45rem);line-height:1.75;font-weight:950;margin:0 0 12px}.header-title span{color:#93c5fd}.header-desc{max-width:760px;margin:0;color:#dbeafe;font-size:15px;line-height:2.1}.header-features{display:flex;flex-wrap:wrap;gap:10px;margin-top:24px}.feature-pill{padding:9px 13px;border-radius:999px;background:rgba(255,255,255,.11);border:1px solid rgba(255,255,255,.15);font-size:13px;font-weight:800}
        .form-body{padding:30px}.question-card{position:relative;margin-bottom:22px;padding:24px;border-radius:26px;background:#fff;border:1px solid var(--line);box-shadow:0 16px 42px rgba(15,23,42,.06);animation:rise .45s ease both}.question-card:nth-child(2){animation-delay:.04s}.question-card:nth-child(3){animation-delay:.08s}.question-card:nth-child(4){animation-delay:.12s}@keyframes rise{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}.section-badge{display:inline-flex;align-items:center;gap:8px;margin-bottom:14px;padding:8px 12px;border-radius:999px;background:rgba(91,33,182,.09);color:var(--primary);font-weight:900;font-size:13px}.question-title{font-size:1.18rem;font-weight:950;margin-bottom:18px}.form-control,.form-select{border-radius:16px;border-color:rgba(15,23,42,.12);padding:.85rem 1rem}.form-control:focus,.form-select:focus{border-color:rgba(91,33,182,.45);box-shadow:0 0 0 .25rem rgba(91,33,182,.10)}.option-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.option-card{display:block;height:100%;cursor:pointer}.option-card input{position:absolute;opacity:0;pointer-events:none}.option-content{height:100%;border:1px solid rgba(15,23,42,.10);border-radius:18px;padding:14px 15px;background:#f8fafc;color:#1e293b;font-weight:850;transition:.2s ease;display:flex;align-items:center;gap:10px}.option-content:before{content:"";width:18px;height:18px;border-radius:50%;border:2px solid #cbd5e1;background:#fff;flex:0 0 18px}.option-card input[type="checkbox"] + .option-content:before{border-radius:6px}.option-card input:checked + .option-content{border-color:rgba(91,33,182,.72);background:linear-gradient(135deg,rgba(91,33,182,.12),rgba(2,132,199,.10));box-shadow:0 12px 26px rgba(91,33,182,.10)}.option-card input:checked + .option-content:before{background:linear-gradient(135deg,var(--primary),var(--secondary));border-color:transparent;box-shadow:inset 0 0 0 4px #fff}.submit-box{padding:24px;border-radius:26px;background:linear-gradient(135deg,rgba(91,33,182,.10),rgba(2,132,199,.10));border:1px solid rgba(91,33,182,.13)}.btn-brand{border:0;color:#fff!important;background:linear-gradient(90deg,var(--primary),var(--secondary));box-shadow:0 18px 36px rgba(91,33,182,.22);font-weight:950}.alert{border-radius:20px}.small-note{color:var(--muted);line-height:2}@media(max-width:767px){.page-wrapper{padding:20px 10px}.form-header{padding:32px 20px}.header-top{align-items:flex-start;flex-direction:column}.form-body{padding:18px}.question-card{padding:18px;border-radius:22px}.option-grid{grid-template-columns:1fr}}
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
                    <span class="header-tag">✨ ثبت نام رایگان دوره آموزشی</span>
                </div>
                <h1 class="header-title">فرم ثبت نام رایگان <span>دوره طراحی سایت با هوش مصنوعی</span></h1>
                <p class="header-desc">با پاسخ دادن به چند سؤال کوتاه، سطح فعلی شما و مسیر مناسب آموزشی مشخص می‌شود.</p>
                <div class="header-features"><span class="feature-pill">بدون هزینه</span><span class="feature-pill">مشاوره رایگان</span><span class="feature-pill">مسیر اختصاصی یادگیری</span></div>
            </div>
        </header>

        <main class="form-body">
            <?php if ($success): ?>
                <div class="alert alert-success border-0 shadow-sm p-4 mb-4">
                    <h2 class="h5 fw-black mb-2">ثبت‌نام اولیه شما با موفقیت انجام شد ✅</h2>
                    <p class="mb-0">اطلاعات شما با موفقیت ثبت شد. مشاور دوره به‌زودی با شما تماس می‌گیرد.</p>
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
                    <button class="btn btn-brand btn-lg rounded-pill px-5 py-3" type="submit">ثبت اطلاعات و دریافت مشاوره رایگان</button>
                    <p class="small-note mb-0 mt-3">اطلاعات شما فقط برای پیگیری ثبت‌نام دوره استفاده می‌شود.</p>
                </div>
            </form>
        </main>
    </div>
</div>
</body>
</html>

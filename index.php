<?php
require_once __DIR__ . '/includes/functions.php';
$settings = read_json('settings.json', []);
if (!is_array($settings)) {
    $settings = [];
}
$works = active_items('works.json');
$syllabus = active_items('syllabus.json');
$gifts = active_items('gifts.json');
$teachers = active_items('teachers.json');
$testimonials = active_items('testimonials.json');
$faqs = active_items('faqs.json');
$title = setting($settings, 'site_title', '3pe | دوره طراحی سایت با هوش مصنوعی');
$description = setting($settings, 'site_description', 'دوره طراحی سایت با هوش مصنوعی');
$logo = setting($settings, 'logo', 'assets/logo.png');
$heroImage = setting($settings, 'hero_image', 'assets/images/ai-hero.png');
$telegram = setting($settings, 'telegram_link', '#');
$instagram = setting($settings, 'instagram_link', '#');
$phone = setting($settings, 'phone', '');
$email = setting($settings, 'email', '');
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?></title>
  <meta name="description" content="<?= e($description) ?>">
  <link rel="icon" type="image/png" href="<?= e($logo) ?>">
  <link href="assets/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/swiper-bundle.min.css">
  <link rel="stylesheet" href="assets/css/landing.css">
  <link rel="stylesheet" href="assets/css/portfolio.css">
</head>
<body>
<header class="site-header">
  <div class="progress-line" id="progressLine"></div>
  <nav class="navbar navbar-expand-lg py-3">
    <div class="container">
      <a class="navbar-brand brand" href="#top" aria-label="3pe">
        <img src="<?= e($logo) ?>" alt="3pe logo">
        <span>3pe</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="باز کردن منو">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav mx-auto gap-lg-1 mt-3 mt-lg-0">
          <li class="nav-item"><a class="nav-link" href="#works">نمونه‌کارها</a></li>
          <li class="nav-item"><a class="nav-link" href="#syllabus">سرفصل‌ها</a></li>
          <li class="nav-item"><a class="nav-link" href="#gifts">هدایا</a></li>
          <li class="nav-item"><a class="nav-link" href="#teachers">اساتید</a></li>
          <li class="nav-item"><a class="nav-link" href="#testimonials">نظرات</a></li>
          <li class="nav-item"><a class="nav-link" href="#faq">سوالات</a></li>
        </ul>
        <div class="d-flex gap-2 mt-3 mt-lg-0">
          <button class="btn btn-soft rounded-pill px-3" type="button" data-bs-toggle="modal" data-bs-target="#consultModal">مشاوره</button>
          <a class="btn btn-register-cta rounded-pill px-4" href="register.php">🚀 ثبت نام رایگان دوره آموزشی</a>
        </div>
      </div>
    </div>
  </nav>
</header>

<main id="top">
  <section class="hero">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-6 reveal">
          <span class="section-kicker">✨ دوره پروژه‌محور طراحی سایت با AI</span>
          <h1 class="hero-title mb-4"><?= e(setting($settings, 'hero_title', 'طراحی سایت حرفه‌ای با کمک هوش مصنوعی')) ?></h1>
          <p class="hero-text mb-4"><?= e(setting($settings, 'hero_text', 'سریع‌تر، تمیزتر و پول‌سازتر طراحی سایت یاد بگیر.')) ?></p>
          <div class="d-flex flex-wrap gap-3 mb-4">
            <a class="btn btn-register-cta rounded-pill px-4 py-3" href="register.php">🚀 ثبت نام رایگان دوره آموزشی</a>
            <a class="btn btn-soft rounded-pill px-4 py-3" href="#works">مشاهده نمونه‌کارها</a>
          </div>
          <div class="hero-pills">
            <span class="badge-soft">⚡ خروجی واقعی</span>
            <span class="badge-soft">🤖 ورک‌فلو هوش مصنوعی</span>
            <span class="badge-soft">💼 آماده بازار کار</span>
            <span class="badge-soft">📱 کاملاً ریسپانسیو</span>
          </div>
          <div class="trust-strip d-flex flex-wrap gap-3 align-items-center">
            <span class="mini-stat">+ پروژه قابل ارائه</span>
            <span class="mini-stat">منتورینگ مسیر تمرین</span>
            <span class="mini-stat">ثبت‌نام اولیه رایگان</span>
          </div>
        </div>
        <div class="col-lg-6 reveal">
          <div class="hero-image-card">
            <img class="hero-main-image" src="<?= e($heroImage) ?>" alt="دوره طراحی سایت با هوش مصنوعی">
            <div class="floating-chip">AI + Bootstrap RTL + PHP خام</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section works-section" id="works">
    <div class="container">
      <div class="section-head reveal">
        <span class="section-kicker">🖼️ Showcase حرفه‌ای</span>
        <h2 class="section-title display-6 mb-3">نمونه‌کارهای ساخته‌شده در مسیر دوره</h2>
        <p class="section-lead">چند نمونه از خروجی‌هایی که هنرجوها می‌توانند بعد از یادگیری طراحی سایت با هوش مصنوعی بسازند.</p>
      </div>

      <div class="works-gallery reveal" aria-label="گالری نمونه‌کارها">
        <div class="works-gallery-head">
          <div class="d-flex flex-wrap gap-2">
            <span class="badge-soft">نمونه دانشجویی</span>
            <span class="badge-soft">طراحی ریسپانسیو</span>
            <span class="badge-soft">قابل ارائه به کارفرما</span>
          </div>
          <div class="works-scroll-controls" aria-label="کنترل اسکرول نمونه‌کارها">
            <button class="works-scroll-btn works-prev" type="button" aria-label="نمونه‌کار قبلی">قبلی</button>
            <button class="works-scroll-btn works-next" type="button" aria-label="نمونه‌کار بعدی">بعدی</button>
          </div>
        </div>

        <div class="works-track" tabindex="0" aria-label="نمونه‌کارها؛ برای مشاهده موارد بیشتر به چپ و راست اسکرول کنید">
          <?php foreach ($works as $work):
              $workTitle = (string)($work['title'] ?? 'نمونه‌کار دوره');
              $workSub = (string)($work['subtitle'] ?? '');
              $workDesc = (string)($work['description'] ?? 'طراحی تمیز، مدرن و ریسپانسیو.');
              $workImage = trim((string)($work['image'] ?? ''));
              $workFullImage = trim((string)($work['full_image'] ?? $workImage));
              $workLabel = (string)($work['label'] ?? 'نمونه دانشجویی');
              if ($workImage === '') {
                  continue;
              }
              if ($workFullImage === '') {
                  $workFullImage = $workImage;
              }
          ?>
          <article class="work-item" data-img="<?= e($workImage) ?>" data-full-src="<?= e($workFullImage) ?>" data-title="<?= e($workTitle) ?>" data-sub="<?= e($workSub) ?>">
            <button class="work-thumb" type="button" aria-label="نمایش بزرگ <?= e($workTitle) ?>">
              <span class="work-label"><?= e($workLabel !== '' ? $workLabel : 'نمونه دانشجویی') ?></span>
              <img src="<?= e($workImage) ?>" data-full-src="<?= e($workFullImage) ?>" alt="<?= e($workTitle) ?>" loading="lazy">
            </button>
            <div class="work-info">
              <h3 class="work-title"><?= e($workTitle) ?></h3>
              <p class="work-desc"><?= e($workDesc) ?></p>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="showcase-note text-center reveal">این نمونه‌ها نشان می‌دهند بعد از دوره می‌توانید خروجی واقعی و قابل ارائه بسازید.</div>
    </div>
  </section>

  <section class="section" id="syllabus">
    <div class="container">
      <div class="section-head reveal">
        <span class="section-kicker">🧭 مسیر یادگیری</span>
        <h2 class="section-title display-6 mb-3">از صفر تا ساخت سایت واقعی، قدم‌به‌قدم</h2>
        <p class="section-lead">مسیر دوره طوری طراحی شده که فقط تماشا نکنید؛ خروجی بسازید، خطا بگیرید، اصلاح کنید و در نهایت پروژه قابل ارائه داشته باشید.</p>
      </div>
      <div class="row g-4 learning-path">
        <?php foreach ($syllabus as $item):
            $tags = normalize_tags($item['tags'] ?? []);
            $step = (string)($item['step_number'] ?? '');
            $icon = (string)($item['icon'] ?? '✨');
        ?>
        <div class="col-md-6 reveal">
          <article class="syllabus-card">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
              <div class="step-number"><?= e($step !== '' ? $step : '•') ?></div>
              <div class="step-icon"><?= e($icon !== '' ? $icon : '✨') ?></div>
            </div>
            <h3 class="fw-black h5 mb-3"><?= e($item['title'] ?? 'مرحله دوره') ?></h3>
            <p class="text-muted-custom mb-3"><?= e($item['description'] ?? '') ?></p>
            <div class="d-flex flex-wrap gap-1">
              <?php foreach ($tags as $tag): ?><span class="tag"><?= e($tag) ?></span><?php endforeach; ?>
            </div>
          </article>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section gift-section" id="gifts">
    <div class="container">
      <div class="section-head reveal">
        <span class="section-kicker">🎁 Bonus Pack</span>
        <h2 class="section-title display-6 mb-3">هدایایی که ارزش عملی دوره را چند برابر می‌کنند</h2>
        <p class="section-lead">ابزارهای آماده برای سریع‌تر ساختن پروژه، تحویل حرفه‌ای و شروع همکاری با مشتری.</p>
      </div>
      <div class="row g-4">
        <?php foreach ($gifts as $gift): ?>
        <div class="col-sm-6 col-lg-4 col-xl-3 reveal">
          <article class="gift-card">
            <span class="gift-badge mb-3">هدیه دوره</span>
            <div class="gift-icon"><?= e($gift['icon'] ?? '🎁') ?></div>
            <h3 class="h5 fw-black mb-3"><?= e($gift['title'] ?? 'هدیه دوره') ?></h3>
            <p class="text-muted-custom mb-0"><?= e($gift['description'] ?? '') ?></p>
          </article>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="mini-cta mt-5 reveal d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
          <h3 class="fw-black h4 mb-2">این هدایا کمک می‌کنند سریع‌تر پروژه واقعی بسازی</h3>
          <p class="text-muted-custom mb-0">ثبت‌نام کن تا مسیر شروع، تمرین و خروجی گرفتن برایت روشن‌تر شود.</p>
        </div>
        <a class="btn btn-register-cta rounded-pill px-4 py-3" href="register.php">✨ ثبت نام رایگان دوره آموزشی</a>
      </div>
    </div>
  </section>

  <section class="section" id="teachers">
    <div class="container">
      <div class="section-head reveal">
        <span class="section-kicker">👨‍🏫 تیم آموزشی</span>
        <h2 class="section-title display-6 mb-3">منتورهایی که مسیر پروژه واقعی را ساده‌تر می‌کنند</h2>
        <p class="section-lead">تمرکز آموزش روی اجرای عملی، کیفیت UI، دیباگ و آماده‌سازی نمونه‌کار قابل اعتماد است.</p>
      </div>
      <div class="row g-4 justify-content-center">
        <?php foreach ($teachers as $teacher):
            $tags = normalize_tags($teacher['tags'] ?? []);
            $teacherImage = trim((string)($teacher['image'] ?? ''));
        ?>
        <div class="col-md-6 col-lg-5 reveal">
          <article class="teacher-card">
            <div class="d-flex gap-3 align-items-center mb-4">
              <?php if ($teacherImage !== ''): ?>
                <img class="teacher-photo" src="<?= e($teacherImage) ?>" alt="<?= e($teacher['name'] ?? 'استاد دوره') ?>" loading="lazy">
              <?php else: ?>
                <div class="teacher-avatar" aria-hidden="true">
                  <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="32" cy="22" r="12" fill="#6d28d9"/><path d="M12 55c3-12 11-19 20-19s17 7 20 19" fill="#0284c7"/><path d="M18 54c3-8 8-12 14-12s11 4 14 12" fill="white" fill-opacity=".5"/></svg>
                </div>
              <?php endif; ?>
              <div>
                <h3 class="h5 fw-black mb-1"><?= e($teacher['name'] ?? 'استاد دوره') ?></h3>
                <p class="text-muted-custom mb-2"><?= e($teacher['position'] ?? 'مدرس دوره') ?></p>
                <span class="stat-badge bg-white rounded-pill border"><?= e($teacher['experience'] ?? 'تجربه پروژه واقعی') ?></span>
              </div>
            </div>
            <p class="text-muted-custom"><?= e($teacher['description'] ?? '') ?></p>
            <div class="d-flex flex-wrap gap-1"><?php foreach ($tags as $tag): ?><span class="tag"><?= e($tag) ?></span><?php endforeach; ?></div>
          </article>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section" id="testimonials">
    <div class="container">
      <div class="section-head reveal">
        <span class="section-kicker">⭐ تجربه دانشجویان</span>
        <h2 class="section-title display-6 mb-3">نظر کسانی که مسیر ساخت خروجی واقعی را شروع کرده‌اند</h2>
        <p class="section-lead">Review cardهای کوتاه، شفاف و قابل اعتماد از تجربه یادگیری پروژه‌محور.</p>
      </div>
      <div class="swiper testimonials-swiper reveal">
        <div class="swiper-wrapper">
          <?php foreach ($testimonials as $testimonial):
              $rating = max(0, min(5, (int)($testimonial['rating'] ?? 5)));
              $fallbackName = (string)($testimonial['name'] ?? 'د');
              $letter = (string)($testimonial['avatar_letter'] ?? (function_exists('mb_substr') ? mb_substr($fallbackName, 0, 1) : substr($fallbackName, 0, 1)));
          ?>
          <div class="swiper-slide">
            <article class="testimonial-card">
              <div class="quote-mark">”</div>
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="avatar"><?= e($letter) ?></div>
                <div>
                  <h3 class="h6 fw-black mb-1"><?= e($testimonial['name'] ?? 'دانشجوی دوره') ?></h3>
                  <p class="small text-muted-custom mb-1"><?= e($testimonial['role'] ?? 'دانشجو') ?></p>
                  <div class="stars" aria-label="<?= e($rating) ?> از ۵"><?= str_repeat('★', $rating) . str_repeat('☆', 5 - $rating) ?></div>
                </div>
              </div>
              <p class="mb-0 text-muted-custom">«<?= e($testimonial['comment'] ?? '') ?>»</p>
            </article>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="swiper-pagination"></div>
      </div>
    </div>
  </section>

  <section class="section faq-section" id="faq">
    <div class="container">
      <div class="section-head reveal">
        <span class="section-kicker">❓ سوالات متداول</span>
        <h2 class="section-title display-6 mb-3">قبل از شروع، ابهام‌ها را شفاف کنیم</h2>
        <p class="section-lead">پاسخ کوتاه و کاربردی به سوال‌هایی که معمولاً قبل از ثبت‌نام اولیه مطرح می‌شود.</p>
      </div>
      <div class="faq-shell reveal">
        <div class="accordion" id="faqAccordion">
          <?php foreach ($faqs as $index => $faq):
              $faqId = 'faq-item-' . $index;
          ?>
          <div class="accordion-item">
            <h3 class="accordion-header" id="heading-<?= e($faqId) ?>">
              <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= e($faqId) ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse-<?= e($faqId) ?>">
                <span class="faq-icon">؟</span><?= e($faq['question'] ?? '') ?>
              </button>
            </h3>
            <div id="collapse-<?= e($faqId) ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading-<?= e($faqId) ?>" data-bs-parent="#faqAccordion">
              <div class="accordion-body"><?= e($faq['answer'] ?? '') ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="mini-cta mt-4 reveal text-center">
        <h3 class="fw-black h4 mb-3">هنوز برای شروع مطمئن نیستی؟</h3>
        <div class="d-flex flex-wrap justify-content-center gap-3">
          <a class="btn btn-register-cta rounded-pill px-4 py-3" href="register.php">🚀 ثبت نام رایگان دوره آموزشی</a>
          <button class="btn btn-soft rounded-pill px-4 py-3" type="button" data-bs-toggle="modal" data-bs-target="#consultModal">دریافت مشاوره</button>
        </div>
      </div>
    </div>
  </section>

  <section class="final-cta" id="register-cta">
    <div class="container">
      <div class="final-cta-card reveal">
        <div class="final-cta-inner d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
          <div class="col-lg-7">
            <span class="badge-soft text-white bg-transparent border border-light border-opacity-25 mb-3">شروع رایگان مسیر</span>
            <h2 class="display-6 fw-black mb-3">آماده‌ای اولین سایت حرفه‌ای خودت را با کمک هوش مصنوعی بسازی؟</h2>
            <p class="mb-0 text-white-50">ثبت‌نام اولیه رایگان است. اطلاعاتت را وارد کن تا مشاور دوره مسیر مناسب شروع را بهت پیشنهاد بدهد.</p>
          </div>
          <div class="d-flex flex-column flex-sm-row gap-3">
            <a class="btn btn-register-cta rounded-pill px-4 py-3" href="register.php">🚀 ثبت نام رایگان دوره آموزشی</a>
            <a class="btn btn-soft rounded-pill px-4 py-3" href="#works">مشاهده نمونه‌کارها</a>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<footer class="footer">
  <div class="container d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
    <div>
      <strong><?= e(setting($settings, 'footer_text', '© 3pe | دوره طراحی سایت با هوش مصنوعی')) ?></strong>
      <div class="small mt-2">طراحی مدرن، پروژه‌محور و سازگار با موبایل.</div>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <?php if ($phone !== ''): ?><span class="badge-soft bg-transparent text-white border-light border-opacity-25">☎ <?= e($phone) ?></span><?php endif; ?>
      <?php if ($email !== ''): ?><span class="badge-soft bg-transparent text-white border-light border-opacity-25">✉ <?= e($email) ?></span><?php endif; ?>
      <a class="btn btn-telegram rounded-pill px-3" href="<?= e($telegram) ?>" target="_blank" rel="noopener">تلگرام</a>
      <a class="btn btn-instagram rounded-pill px-3" href="<?= e($instagram) ?>" target="_blank" rel="noopener">اینستاگرام</a>
    </div>
  </div>
</footer>

<div class="work-lightbox" id="workLightbox" aria-hidden="true" role="dialog" aria-label="نمایش بزرگ نمونه‌کار">
  <div class="work-lightbox-backdrop" data-work-lightbox-close></div>
  <button class="work-lightbox-close" type="button" aria-label="بستن">×</button>
  <div class="work-lightbox-stage" id="workLightboxStage">
    <img id="workLightboxImg" class="work-lightbox-img" src="" alt="نمایش نمونه‌کار" draggable="false">
  </div>
</div>

<div class="modal fade consult-modal" id="consultModal" tabindex="-1" aria-labelledby="consultModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-black" id="consultModalLabel">فرم دریافت مشاوره ثبت‌نام</h5>
        <button type="button" class="btn-close ms-0" data-bs-dismiss="modal" aria-label="بستن"></button>
      </div>
      <div class="modal-body p-4">
        <form id="consultForm" class="needs-validation" novalidate action="save_consult.php" method="post">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold" for="fullName">نام و نام خانوادگی</label>
              <input type="text" class="form-control" id="fullName" name="fullName" required>
              <div class="invalid-feedback">نام را وارد کنید.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold" for="phone">شماره تماس</label>
              <input type="tel" class="form-control" id="phone" name="phone" pattern="^(\+98|0)?9\d{9}$" required>
              <div class="invalid-feedback">شماره معتبر وارد کنید.</div>
            </div>
            <div class="col-12">
              <label class="form-label fw-bold" for="level">سطح فعلی</label>
              <select class="form-select" id="level" name="level">
                <option value="">انتخاب کنید…</option>
                <option value="beginner">کاملاً مبتدی</option>
                <option value="basic">آشنایی اولیه با طراحی سایت</option>
                <option value="intermediate">در حال اجرای پروژه</option>
                <option value="advanced">طراح سایت و دنبال رشد درآمد</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-bold" for="message">هدف یا توضیحات</label>
              <textarea class="form-control" id="message" name="message" rows="4"></textarea>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-brand w-100 rounded-4 py-3">ثبت درخواست مشاوره</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="ai-course-popup-overlay" id="aiCoursePopup" aria-hidden="true">
  <div class="ai-course-popup" role="dialog" aria-modal="true" aria-labelledby="aiCoursePopupTitle" aria-describedby="aiCoursePopupText">
    <div class="ai-course-popup-card">
      <button class="ai-course-popup-close" type="button" aria-label="بستن پاپ‌آپ دوره رایگان">×</button>
      <div class="ai-course-popup-shine" aria-hidden="true"></div>
      <div class="ai-course-popup-badge">فرصت رایگان برای شروع طراحی سایت</div>
      <div class="ai-course-popup-icon" aria-hidden="true">🚀</div>
      <h2 class="ai-course-popup-title" id="aiCoursePopupTitle">دوره رایگان طراحی سایت با هوش مصنوعی</h2>
      <p class="ai-course-popup-text" id="aiCoursePopupText">یاد بگیر چطور با کمک هوش مصنوعی، بدون کدنویسی سنگین، سایت واقعی طراحی کنی و سریع‌تر وارد بازار کار طراحی سایت بشی.</p>
      <div class="ai-course-popup-features" aria-label="مزیت‌های دوره">
        <span>مناسب شروع از صفر</span>
        <span>آموزش پروژه‌محور</span>
        <span>کاملاً رایگان</span>
      </div>
      <p class="ai-course-popup-urgency">ظرفیت ثبت‌نام رایگان محدوده</p>
      <a class="ai-course-popup-button" href="https://3pe.ir/register.php">ثبت‌نام رایگان دوره</a>
      <p class="ai-course-popup-note">بعد از کلیک وارد فرم ثبت‌نام پرس‌لاین می‌شوی.</p>
    </div>
  </div>
</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/swiper-bundle.min.js"></script>
<script src="assets/js/sweetalert2.all.min.js"></script>
<script src="assets/js/portfolio.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const yearEl = document.getElementById('year');
  if (yearEl) yearEl.textContent = new Date().getFullYear();

  const progressLine = document.getElementById('progressLine');
  function updateProgress() {
    if (!progressLine) return;
    const d = document.documentElement;
    const total = d.scrollHeight - d.clientHeight;
    const p = total > 0 ? (d.scrollTop / total) * 100 : 0;
    progressLine.style.width = Math.min(100, Math.max(0, p)) + '%';
  }
  updateProgress();
  window.addEventListener('scroll', updateProgress, { passive: true });

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const revealItems = document.querySelectorAll('.reveal');
  if (prefersReducedMotion || !('IntersectionObserver' in window)) {
    revealItems.forEach(el => el.classList.add('is-visible'));
  } else {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    revealItems.forEach(el => observer.observe(el));
  }

  if (window.Swiper) {
    new Swiper('.testimonials-swiper', {
      loop: true,
      spaceBetween: 18,
      grabCursor: true,
      speed: 620,
      autoplay: prefersReducedMotion ? false : { delay: 5200, disableOnInteraction: false },
      pagination: { el: '.testimonials-swiper .swiper-pagination', clickable: true },
      breakpoints: { 0: { slidesPerView: 1 }, 768: { slidesPerView: 2 }, 1200: { slidesPerView: 3 } }
    });
  }


  const form = document.getElementById('consultForm');
  const consultModalEl = document.getElementById('consultModal');
  function showMessage(type, titleText, text) {
    if (window.Swal) {
      Swal.fire({ icon: type, title: titleText, text: text, confirmButtonText: 'باشه' });
    } else {
      alert(titleText + '\n' + text);
    }
  }
  if (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      if (!form.checkValidity()) {
        event.stopPropagation();
        form.classList.add('was-validated');
        return;
      }
      const btn = form.querySelector('button[type="submit"]');
      const old = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = 'در حال ثبت...';
      fetch(form.action, { method: 'POST', body: new FormData(form) })
        .then(r => r.json())
        .then(data => {
          if (!data || !data.ok) {
            showMessage('error', 'خطا', data && data.message ? data.message : 'دوباره تلاش کنید.');
            return;
          }
          showMessage('success', 'ثبت شد ✅', 'درخواست مشاوره شما با موفقیت ثبت شد.');
          form.reset();
          form.classList.remove('was-validated');
          if (window.bootstrap && consultModalEl) (bootstrap.Modal.getInstance(consultModalEl) || new bootstrap.Modal(consultModalEl)).hide();
        })
        .catch(() => showMessage('error', 'مشکل ارتباط با سرور', 'دوباره تلاش کنید.'))
        .finally(() => { btn.disabled = false; btn.innerHTML = old; });
    });
  }
});
</script>
</body>
</html>

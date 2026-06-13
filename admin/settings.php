<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_admin();
$settings = read_json('settings.json', []); if(!is_array($settings)){$settings=[];}
$settings += [
    'registration_success_title' => 'ثبت‌نامت با موفقیت انجام شد 🎉',
    'registration_success_description' => 'برای دریافت آموزش رایگان روی دکمه زیر کلیک کن.',
    'registration_success_button_text' => 'دریافت آموزش رایگان',
    'registration_success_redirect_url' => '#',
    'registration_auto_redirect_enabled' => false,
    'registration_auto_redirect_seconds' => 3,
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die();
    $keys = ['site_title','site_description','hero_title','hero_text','telegram_link','instagram_link','phone','email','footer_text','registration_success_title','registration_success_description','registration_success_button_text','registration_success_redirect_url'];
    foreach ($keys as $key) { $settings[$key] = trim((string)($_POST[$key] ?? '')); }
    $settings['registration_auto_redirect_enabled'] = isset($_POST['registration_auto_redirect_enabled']);
    $settings['registration_auto_redirect_seconds'] = max(1, (int)($_POST['registration_auto_redirect_seconds'] ?? 3));
    $settings['logo'] = handle_image_upload('logo', 'site', (string)($settings['logo'] ?? 'assets/logo.png'));
    $settings['hero_image'] = handle_image_upload('hero_image', 'site', (string)($settings['hero_image'] ?? 'assets/images/ai-hero.png'));
    write_json('settings.json', $settings);
    flash_set('success','تنظیمات ذخیره شد.');
    redirect('/admin/settings.php');
}
$pageTitle='تنظیمات سایت'; include __DIR__ . '/../includes/admin-header.php'; include __DIR__ . '/../includes/admin-sidebar.php';
function input_setting($name,$label,$settings,$type='text'){ ?>
<div class="mb-3"><label class="form-label fw-bold"><?= e($label) ?></label><?php if($type==='textarea'): ?><textarea class="form-control" rows="4" name="<?= e($name) ?>"><?= e($settings[$name] ?? '') ?></textarea><?php else: ?><input class="form-control" type="<?= e($type) ?>" name="<?= e($name) ?>" value="<?= e($settings[$name] ?? '') ?>"><?php endif; ?></div><?php }
?>
<main class="admin-main"><div class="mb-4"><h1 class="h3 fw-black mb-1">تنظیمات سایت</h1><p class="text-muted mb-0">اطلاعات عمومی، Hero و راه‌های ارتباطی سایت را ویرایش کنید.</p></div><?php foreach(flash_get() as $flash): ?><div class="alert alert-<?= e($flash['type']) ?> rounded-4"><?= e($flash['message']) ?></div><?php endforeach; ?><div class="admin-card p-4"><form method="post" enctype="multipart/form-data"><?= csrf_field() ?><div class="row g-3"><div class="col-md-6"><?php input_setting('site_title','عنوان سایت',$settings); ?></div><div class="col-md-6"><?php input_setting('site_description','توضیح کوتاه سایت',$settings); ?></div><div class="col-12"><?php input_setting('hero_title','متن اصلی Hero',$settings,'textarea'); ?></div><div class="col-12"><?php input_setting('hero_text','متن توضیحی Hero',$settings,'textarea'); ?></div><div class="col-md-6"><?php input_setting('telegram_link','لینک تلگرام',$settings,'url'); ?></div><div class="col-md-6"><?php input_setting('instagram_link','لینک اینستاگرام',$settings,'url'); ?></div><div class="col-md-6"><?php input_setting('phone','شماره تماس',$settings); ?></div><div class="col-md-6"><?php input_setting('email','ایمیل',$settings,'email'); ?></div><div class="col-md-6"><label class="form-label fw-bold">لوگو</label><input class="form-control" type="file" name="logo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><div class="small text-muted mt-2"><?= e($settings['logo'] ?? '') ?></div></div><div class="col-md-6"><label class="form-label fw-bold">تصویر Hero</label><input class="form-control" type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><div class="small text-muted mt-2"><?= e($settings['hero_image'] ?? '') ?></div></div><div class="col-12"><hr class="my-2"><h2 class="h5 fw-black mb-1">تنظیمات فرم ثبت‌نام دوره رایگان</h2><p class="text-muted small mb-3">متن پیام موفقیت، لینک مقصد دکمه و هدایت خودکار بعد از ثبت فرم register.php را مدیریت کنید.</p></div><div class="col-md-6"><?php input_setting('registration_success_title','متن پیام موفقیت',$settings); ?></div><div class="col-md-6"><?php input_setting('registration_success_button_text','متن دکمه بعد از ثبت',$settings); ?></div><div class="col-12"><?php input_setting('registration_success_description','توضیح زیر پیام موفقیت',$settings,'textarea'); ?></div><div class="col-md-8"><?php input_setting('registration_success_redirect_url','لینک مقصد بعد از ثبت فرم',$settings,'url'); ?></div><div class="col-md-4"><label class="form-label fw-bold" for="registration_auto_redirect_seconds">زمان هدایت خودکار (ثانیه)</label><input class="form-control" id="registration_auto_redirect_seconds" type="number" min="1" name="registration_auto_redirect_seconds" value="<?= e($settings['registration_auto_redirect_seconds'] ?? 3) ?>"></div><div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="registration_auto_redirect_enabled" name="registration_auto_redirect_enabled" <?= !empty($settings['registration_auto_redirect_enabled']) ? 'checked' : '' ?>><label class="form-check-label fw-bold" for="registration_auto_redirect_enabled">هدایت خودکار کاربر به لینک مقصد بعد از ثبت فعال باشد</label></div></div><div class="col-12"><?php input_setting('footer_text','متن فوتر',$settings,'textarea'); ?></div><div class="col-12"><button class="btn btn-brand rounded-pill px-5 py-2">ذخیره تنظیمات</button></div></div></form></div></main><?php include __DIR__ . '/../includes/admin-footer.php'; ?>

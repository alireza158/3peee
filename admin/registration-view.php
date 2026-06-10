<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$error = null;
$registrations = read_json_array_with_error('registrations.json', $error);
$statuses = registration_statuses();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$registration = null;
foreach ($registrations as $item) {
    if ((int) ($item['id'] ?? 0) === $id) {
        $registration = $item;
        break;
    }
}
if (!$registration) {
    flash_set('danger', 'ثبت‌نام مورد نظر پیدا نشد.');
    redirect('/admin/registrations.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die();
    $status = (string) ($_POST['admin_status'] ?? 'جدید');
    if (!in_array($status, $statuses, true)) {
        $status = 'جدید';
    }
    $note = clean_multiline($_POST['admin_note'] ?? '', 2000);
    foreach ($registrations as &$item) {
        if ((int) ($item['id'] ?? 0) === $id) {
            $item['admin_status'] = $status;
            $item['admin_note'] = $note;
            $item['updated_at'] = now_string();
            $registration = $item;
            break;
        }
    }
    unset($item);
    write_json('registrations.json', array_values($registrations));
    flash_set('success', 'اطلاعات پیگیری ثبت‌نام ذخیره شد.');
    redirect('/admin/registration-view.php?id=' . $id);
}

function detail_row(string $label, $value): void
{
    if (is_array($value)) {
        $value = implode('، ', $value);
    }
    echo '<div class="col-md-6"><div class="p-3 rounded-4 bg-light h-100"><div class="small text-muted mb-1">' . e($label) . '</div><div class="fw-bold">' . nl2br(e((string) $value)) . '</div></div></div>';
}

$pageTitle = 'جزئیات ثبت‌نام دوره';
include __DIR__ . '/../includes/admin-header.php';
include __DIR__ . '/../includes/admin-sidebar.php';
$status = (string)($registration['admin_status'] ?? 'جدید');
?>
<main class="admin-main">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 fw-black mb-1">جزئیات ثبت‌نام دوره</h1><p class="text-muted mb-0">مشاهده کامل اطلاعات فرم و ثبت یادداشت داخلی ادمین.</p></div>
        <a class="btn btn-light rounded-pill px-4" href="/admin/registrations.php">بازگشت به لیست</a>
    </div>
    <?php foreach (flash_get() as $flash): ?><div class="alert alert-<?= e($flash['type']) ?> rounded-4"><?= e($flash['message']) ?></div><?php endforeach; ?>
    <?php if ($error): ?><div class="alert alert-danger rounded-4"><?= e($error) ?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="admin-card p-4 mb-4">
                <h2 class="h5 fw-black mb-3">اطلاعات ثبت‌نام</h2>
                <div class="row g-3">
                    <?php
                    detail_row('نام و نام خانوادگی', $registration['name'] ?? '');
                    detail_row('شماره موبایل', $registration['mobile'] ?? '');
                    detail_row('شهر محل سکونت', $registration['city'] ?? '-');
                    detail_row('سن', $registration['age_range'] ?? '-');
                    detail_row('وضعیت فعلی', $registration['current_status'] ?? '');
                    detail_row('سطح آشنایی با طراحی سایت', $registration['web_level'] ?? '');
                    detail_row('مهارت‌ها', $registration['skills'] ?? []);
                    detail_row('هدف‌ها', $registration['goals'] ?? []);
                    detail_row('استفاده از هوش مصنوعی', $registration['ai_usage'] ?? '');
                    detail_row('نوع آموزش', $registration['learning_type'] ?? '');
                    detail_row('روش ارتباط', $registration['preferred_contact'] ?? '');
                    detail_row('تاریخ ثبت', $registration['created_at'] ?? '');
                    ?>
                    <div class="col-12"><div class="p-3 rounded-4 bg-light"><div class="small text-muted mb-1">پیام کاربر</div><div class="fw-bold"><?= nl2br(e($registration['message'] ?? '-')) ?></div></div></div>
                </div>
            </div>
            <div class="admin-card p-4">
                <h2 class="h5 fw-black mb-3">اطلاعات سیستمی</h2>
                <div class="row g-3">
                    <?php detail_row('IP', $registration['ip_address'] ?? ''); detail_row('User Agent', $registration['user_agent'] ?? ''); detail_row('آخرین به‌روزرسانی', $registration['updated_at'] ?? ''); ?>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="admin-card p-4 position-sticky" style="top:24px">
                <h2 class="h5 fw-black mb-3">پیگیری ادمین</h2>
                <div class="mb-3"><span class="badge rounded-pill <?= e(registration_status_badge_class($status)) ?> p-2"><?= e($status) ?></span></div>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= e($registration['id'] ?? '') ?>">
                    <div class="mb-3"><label class="form-label fw-bold">وضعیت پیگیری</label><select class="form-select" name="admin_status"><?php foreach ($statuses as $option): ?><option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></div>
                    <div class="mb-3"><label class="form-label fw-bold">یادداشت داخلی ادمین</label><textarea class="form-control" name="admin_note" rows="7" placeholder="مثلاً: تماس گرفتم، گفت هفته بعد تصمیم می‌گیرد."><?= e($registration['admin_note'] ?? '') ?></textarea><div class="form-text">این یادداشت فقط در پنل ادمین نمایش داده می‌شود.</div></div>
                    <button class="btn btn-brand w-100 rounded-4 py-2">ذخیره وضعیت و یادداشت</button>
                </form>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/admin-footer.php'; ?>

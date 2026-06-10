<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$error = null;
$registrations = read_json_array_with_error('registrations.json', $error);
$statuses = registration_statuses();
$webLevels = ['هیچ آشنایی ندارم', 'کمی درباره‌اش شنیده‌ام', 'چند آموزش دیده‌ام ولی پروژه نساخته‌ام', 'یک یا چند پروژه ساده ساخته‌ام', 'تجربه کاری یا پروژه واقعی دارم'];
$learningTypes = ['حضوری', 'آنلاین زنده', 'ویدیوهای ضبط‌شده', 'ترکیبی', 'فرقی ندارد'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die();
    $id = (int) ($_POST['id'] ?? 0);
    $status = (string) ($_POST['admin_status'] ?? 'جدید');
    if (!in_array($status, $statuses, true)) {
        $status = 'جدید';
    }
    foreach ($registrations as &$registration) {
        if ((int) ($registration['id'] ?? 0) === $id) {
            $registration['admin_status'] = $status;
            $registration['updated_at'] = now_string();
            break;
        }
    }
    unset($registration);
    write_json('registrations.json', array_values($registrations));
    flash_set('success', 'وضعیت پیگیری ثبت‌نام تغییر کرد.');
    redirect('/admin/registrations.php');
}

$q = trim((string) ($_GET['q'] ?? ''));
$statusFilter = (string) ($_GET['status'] ?? '');
$levelFilter = (string) ($_GET['web_level'] ?? '');
$learningFilter = (string) ($_GET['learning_type'] ?? '');

$filtered = array_values(array_filter($registrations, static function ($item) use ($q, $statusFilter, $levelFilter, $learningFilter) {
    $matches = true;
    if ($q !== '') {
        $haystack = implode(' ', [(string)($item['name'] ?? ''), (string)($item['mobile'] ?? ''), (string)($item['city'] ?? '')]);
        $matches = str_contains($haystack, $q);
    }
    if ($matches && $statusFilter !== '') {
        $matches = (string)($item['admin_status'] ?? 'جدید') === $statusFilter;
    }
    if ($matches && $levelFilter !== '') {
        $matches = (string)($item['web_level'] ?? '') === $levelFilter;
    }
    if ($matches && $learningFilter !== '') {
        $matches = (string)($item['learning_type'] ?? '') === $learningFilter;
    }
    return $matches;
}));

usort($filtered, static fn($a, $b) => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
$totalCount = count($registrations);
$newCount = count(array_filter($registrations, static fn($item) => (string)($item['admin_status'] ?? 'جدید') === 'جدید'));

$pageTitle = 'ثبت‌نام‌های دوره';
include __DIR__ . '/../includes/admin-header.php';
include __DIR__ . '/../includes/admin-sidebar.php';
?>
<main class="admin-main">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-black mb-1">ثبت‌نام‌های دوره</h1>
            <p class="text-muted mb-0">مشاهده، جستجو، فیلتر و مدیریت فرم‌های ثبت نام رایگان دوره آموزشی.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge rounded-pill text-bg-primary p-3">کل ثبت‌نام‌ها: <?= e($totalCount) ?></span>
            <span class="badge rounded-pill text-bg-success p-3">ثبت‌نام‌های جدید: <?= e($newCount) ?></span>
        </div>
    </div>

    <?php foreach (flash_get() as $flash): ?><div class="alert alert-<?= e($flash['type']) ?> rounded-4"><?= e($flash['message']) ?></div><?php endforeach; ?>
    <?php if ($error): ?><div class="alert alert-danger rounded-4"><?= e($error) ?></div><?php endif; ?>

    <div class="admin-card p-4 mb-4">
        <form class="row g-3 align-items-end" method="get">
            <div class="col-md-3"><label class="form-label fw-bold">جستجو</label><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="نام، موبایل یا شهر"></div>
            <div class="col-md-3"><label class="form-label fw-bold">وضعیت پیگیری</label><select class="form-select" name="status"><option value="">همه وضعیت‌ها</option><?php foreach ($statuses as $status): ?><option value="<?= e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label class="form-label fw-bold">سطح آشنایی</label><select class="form-select" name="web_level"><option value="">همه سطح‌ها</option><?php foreach ($webLevels as $level): ?><option value="<?= e($level) ?>" <?= $levelFilter === $level ? 'selected' : '' ?>><?= e($level) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label class="form-label fw-bold">نوع آموزش</label><select class="form-select" name="learning_type"><option value="">همه انواع</option><?php foreach ($learningTypes as $type): ?><option value="<?= e($type) ?>" <?= $learningFilter === $type ? 'selected' : '' ?>><?= e($type) ?></option><?php endforeach; ?></select></div>
            <div class="col-12 d-flex gap-2"><button class="btn btn-brand rounded-pill px-4">اعمال فیلتر</button><a class="btn btn-light rounded-pill px-4" href="/admin/registrations.php">حذف فیلتر</a></div>
        </form>
    </div>

    <div class="admin-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead><tr><th>ردیف</th><th>نام</th><th>موبایل</th><th>شهر</th><th>سطح طراحی سایت</th><th>هدف‌ها</th><th>روش ارتباط</th><th>وضعیت پیگیری</th><th>تاریخ ثبت</th><th class="text-end">عملیات</th></tr></thead>
                <tbody>
                <?php if (!$filtered): ?><tr><td colspan="10" class="text-center text-muted py-5">موردی پیدا نشد.</td></tr><?php endif; ?>
                <?php foreach ($filtered as $index => $item): $status = (string)($item['admin_status'] ?? 'جدید'); ?>
                    <tr>
                        <td><?= e($index + 1) ?></td>
                        <td class="fw-bold"><?= e($item['name'] ?? '') ?></td>
                        <td dir="ltr"><?= e($item['mobile'] ?? '') ?></td>
                        <td><?= e($item['city'] ?? '-') ?></td>
                        <td><?= e($item['web_level'] ?? '') ?></td>
                        <td class="small text-muted"><?= e(implode('، ', (array)($item['goals'] ?? []))) ?></td>
                        <td><?= e($item['preferred_contact'] ?? '') ?></td>
                        <td><span class="badge rounded-pill <?= e(registration_status_badge_class($status)) ?>"><?= e($status) ?></span></td>
                        <td><?= e($item['created_at'] ?? '') ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-info rounded-pill" href="/admin/registration-view.php?id=<?= e($item['id'] ?? '') ?>">مشاهده جزئیات</a>
                            <form class="d-inline-flex gap-1 mt-1" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($item['id'] ?? '') ?>"><select class="form-select form-select-sm" name="admin_status"><?php foreach ($statuses as $option): ?><option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select><button class="btn btn-sm btn-outline-primary rounded-pill">تغییر وضعیت</button></form>
                            <form class="d-inline" method="post" action="/admin/registration-delete.php" onsubmit="return confirm('این ثبت‌نام حذف شود؟')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($item['id'] ?? '') ?>"><button class="btn btn-sm btn-outline-danger rounded-pill mt-1">حذف</button></form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/admin-footer.php'; ?>

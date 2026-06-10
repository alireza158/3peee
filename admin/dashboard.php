<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$leads = read_json('leads.json', []); if (!is_array($leads)) { $leads = []; }
$registrations = read_json('registrations.json', []); if (!is_array($registrations)) { $registrations = []; }
$works = read_json('works.json', []); if (!is_array($works)) { $works = []; }
$testimonials = read_json('testimonials.json', []); if (!is_array($testimonials)) { $testimonials = []; }
$faqs = read_json('faqs.json', []); if (!is_array($faqs)) { $faqs = []; }

usort($registrations, static fn($a, $b) => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
$recentLeads = array_slice(array_reverse($leads), 0, 6);
$recentRegistrations = array_slice($registrations, 0, 5);
$newRegistrations = count(array_filter($registrations, static fn($item) => (string)($item['admin_status'] ?? 'جدید') === 'جدید'));
$enrolledRegistrations = count(array_filter($registrations, static fn($item) => (string)($item['admin_status'] ?? '') === 'ثبت‌نام شد'));

$stats = [
    ['درخواست‌های مشاوره', count($leads), '☎️', 'linear-gradient(135deg,#5b21b6,#7c3aed)'],
    ['ثبت‌نام‌های دوره', count($registrations), '📝', 'linear-gradient(135deg,#0284c7,#38bdf8)'],
    ['ثبت‌نام‌های جدید', $newRegistrations, '✨', 'linear-gradient(135deg,#db2777,#f97316)'],
    ['ثبت‌نام‌شده‌ها', $enrolledRegistrations, '✅', 'linear-gradient(135deg,#0f766e,#22c55e)'],
    ['نمونه‌کارها', count($works), '🖼️', 'linear-gradient(135deg,#4338ca,#06b6d4)'],
    ['نظرات', count($testimonials), '⭐', 'linear-gradient(135deg,#a21caf,#ec4899)'],
    ['سوالات متداول', count($faqs), '❓', 'linear-gradient(135deg,#334155,#64748b)'],
];

$pageTitle = 'داشبورد';
include __DIR__ . '/../includes/admin-header.php';
include __DIR__ . '/../includes/admin-sidebar.php';
?>
<main class="admin-main">
    <div class="mb-4">
        <h1 class="h3 fw-black mb-1">داشبورد</h1>
        <p class="text-muted mb-0">خلاصه وضعیت سایت، درخواست‌های مشاوره و ثبت‌نام‌های دوره.</p>
    </div>
    <?php foreach (flash_get() as $flash): ?><div class="alert alert-<?= e($flash['type']) ?> rounded-4"><?= e($flash['message']) ?></div><?php endforeach; ?>
    <div class="row g-4 mb-4">
        <?php foreach ($stats as $s): ?>
            <div class="col-sm-6 col-xl-3"><div class="admin-card stat-card p-4 text-white" style="background:<?= e($s[3]) ?>"><div class="d-flex justify-content-between align-items-center"><div><div class="small opacity-75"><?= e($s[0]) ?></div><div class="display-6 fw-black"><?= e($s[1]) ?></div></div><div class="fs-1"><?= e($s[2]) ?></div></div></div></div>
        <?php endforeach; ?>
    </div>

    <div class="admin-card p-0 overflow-hidden mb-4">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center gap-3">
            <h2 class="h5 fw-black mb-0">آخرین ثبت‌نام‌های رایگان دوره</h2>
            <a class="btn btn-sm btn-outline-primary rounded-pill" href="/admin/registrations.php">مشاهده همه</a>
        </div>
        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead><tr><th>نام</th><th>موبایل</th><th>سطح</th><th>وضعیت</th><th>تاریخ</th></tr></thead>
                <tbody>
                <?php if (!$recentRegistrations): ?><tr><td colspan="5" class="text-center text-muted py-5">ثبت‌نامی ثبت نشده است.</td></tr><?php endif; ?>
                <?php foreach ($recentRegistrations as $item): $status = (string)($item['admin_status'] ?? 'جدید'); ?>
                    <tr onclick="location.href='/admin/registration-view.php?id=<?= e($item['id'] ?? '') ?>'" style="cursor:pointer">
                        <td class="fw-bold"><?= e($item['name'] ?? '') ?></td>
                        <td dir="ltr"><?= e($item['mobile'] ?? '') ?></td>
                        <td><?= e($item['web_level'] ?? '') ?></td>
                        <td><span class="badge rounded-pill <?= e(registration_status_badge_class($status)) ?>"><?= e($status) ?></span></td>
                        <td><?= e($item['created_at'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card p-0 overflow-hidden">
        <div class="p-4 border-bottom"><h2 class="h5 fw-black mb-0">آخرین درخواست‌های مشاوره</h2></div>
        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead><tr><th>نام</th><th>موبایل</th><th>سطح</th><th>وضعیت</th><th>تاریخ</th></tr></thead>
                <tbody>
                <?php if (!$recentLeads): ?><tr><td colspan="5" class="text-center text-muted py-5">درخواستی ثبت نشده است.</td></tr><?php endif; ?>
                <?php foreach ($recentLeads as $lead): ?>
                    <tr><td class="fw-bold"><?= e($lead['name'] ?? '') ?></td><td><?= e($lead['mobile'] ?? '') ?></td><td><?= e($lead['level'] ?? '') ?></td><td><span class="badge rounded-pill text-bg-primary"><?= e($lead['status'] ?? 'جدید') ?></span></td><td><?= e($lead['created_at'] ?? '') ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/admin-footer.php'; ?>

<?php
$current = basename($_SERVER['PHP_SELF']);
$links = [
    'dashboard.php' => ['🏠','داشبورد'],
    'settings.php' => ['⚙️','تنظیمات سایت'],
    'leads.php' => ['☎️','درخواست‌ها'],
    'works.php' => ['🖼️','نمونه‌کارها'],
    'syllabus.php' => ['📚','سرفصل‌ها'],
    'gifts.php' => ['🎁','هدایا'],
    'teachers.php' => ['👨‍🏫','اساتید'],
    'testimonials.php' => ['⭐','نظرات'],
    'faqs.php' => ['❓','سوالات'],
];
?>
<aside class="admin-sidebar">
    <div class="brand-box">
        <img src="/assets/logo.png" alt="3pe">
        <div><div class="fw-black">پنل مدیریت 3pe</div><div class="small text-muted"><?= e($admin['email'] ?? '') ?></div></div>
    </div>
    <nav class="nav-admin">
        <?php foreach ($links as $href => [$icon,$label]): ?>
            <a class="<?= $current === $href ? 'active' : '' ?>" href="/admin/<?= e($href) ?>"><span><?= e($icon) ?></span><?= e($label) ?></a>
        <?php endforeach; ?>
        <a href="/" target="_blank"><span>🌐</span>مشاهده سایت</a>
        <a href="/admin/logout.php"><span>🚪</span>خروج</a>
    </nav>
</aside>

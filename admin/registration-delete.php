<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('حذف ثبت‌نام فقط با درخواست POST مجاز است.');
}
verify_csrf_or_die();
$id = (int) ($_POST['id'] ?? 0);
$error = null;
$registrations = read_json_array_with_error('registrations.json', $error);
$before = count($registrations);
$registrations = array_values(array_filter($registrations, static fn($item) => (int)($item['id'] ?? 0) !== $id));
if (count($registrations) === $before) {
    flash_set('warning', 'رکوردی برای حذف پیدا نشد.');
} else {
    write_json('registrations.json', $registrations);
    flash_set('success', 'ثبت‌نام انتخاب‌شده حذف شد.');
}
redirect('/admin/registrations.php');

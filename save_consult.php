<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$name = trim((string)($_POST['fullName'] ?? $_POST['name'] ?? ''));
$mobile = trim((string)($_POST['phone'] ?? $_POST['mobile'] ?? ''));
$level = trim((string)($_POST['level'] ?? ''));
$message = trim((string)($_POST['message'] ?? $_POST['note'] ?? ''));

if ($name === '' || mb_strlen($name) < 3 || mb_strlen($name) > 120) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'نام معتبر وارد کنید.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!preg_match('/^(\+98|0)?9\d{9}$/', $mobile)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'شماره تماس معتبر نیست.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$levelLabels = [
    '' => '',
    'beginner' => 'کاملاً مبتدی',
    'basic' => 'آشنایی اولیه با طراحی سایت',
    'intermediate' => 'در حال اجرای پروژه',
    'advanced' => 'طراح سایت و دنبال رشد درآمد',
];
$level = $levelLabels[$level] ?? mb_substr($level, 0, 80);

$leads = read_json('leads.json', []);
if (!is_array($leads)) { $leads = []; }
$leads[] = [
    'id' => make_id(),
    'name' => $name,
    'mobile' => $mobile,
    'level' => $level,
    'message' => mb_substr($message, 0, 1000),
    'status' => 'جدید',
    'created_at' => now_string(),
];

if (!write_json('leads.json', $leads)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'خطا در ذخیره اطلاعات.'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['ok' => true, 'message' => 'درخواست مشاوره با موفقیت ثبت شد.'], JSON_UNESCAPED_UNICODE);

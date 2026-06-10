<?php
// save_consult.php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
  exit;
}

// دریافت داده‌ها
$fullName = trim($_POST['fullName'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$level    = trim($_POST['level'] ?? '');
$note     = trim($_POST['note'] ?? '');

// اعتبارسنجی
if ($fullName === '' || mb_strlen($fullName) < 3) {
  http_response_code(422);
  echo json_encode(['ok' => false, 'message' => 'نام معتبر وارد کنید.']);
  exit;
}

if (!preg_match('/^(\+98|0)?9\d{9}$/', $phone)) {
  http_response_code(422);
  echo json_encode(['ok' => false, 'message' => 'شماره تماس معتبر نیست.']);
  exit;
}

$allowedLevels = ['', 'beginner', 'intermediate', 'advanced'];
if (!in_array($level, $allowedLevels, true)) {
  $level = '';
}

// مسیر CSV
$dir = __DIR__ . '/data';
if (!is_dir($dir)) {
  mkdir($dir, 0755, true);
}

$file = $dir . '/consults.csv';
$isNew = !file_exists($file);

$date = date('Y-m-d H:i:s');
$ip   = $_SERVER['REMOTE_ADDR'] ?? '';
$ua   = $_SERVER['HTTP_USER_AGENT'] ?? '';

// ردیف
$row = [$date, $fullName, $phone, $level, $note, $ip, $ua];

// ذخیره با قفل
$fp = fopen($file, 'a');
if (!$fp) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'message' => 'خطا در باز کردن فایل.']);
  exit;
}

flock($fp, LOCK_EX);

if ($isNew) {
  fputcsv($fp, ['date', 'fullName', 'phone', 'level', 'note', 'ip', 'userAgent']);
}
fputcsv($fp, $row);

flock($fp, LOCK_UN);
fclose($fp);

echo json_encode(['ok' => true, 'message' => 'ثبت شد']);
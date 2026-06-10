<?php
require_once __DIR__ . '/functions.php';

function handle_image_upload(string $input, string $subdir, string $current = ''): string
{
    if (empty($_FILES[$input]) || ($_FILES[$input]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $current;
    }
    $file = $_FILES[$input];
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        flash_set('danger', 'خطا در آپلود تصویر.');
        return $current;
    }
    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        flash_set('danger', 'حجم تصویر باید کمتر از ۲ مگابایت باشد.');
        return $current;
    }
    $tmp = $file['tmp_name'] ?? '';
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($allowed[$mime]) || !is_uploaded_file($tmp)) {
        flash_set('danger', 'فرمت تصویر مجاز نیست.');
        return $current;
    }
    $dir = UPLOAD_DIR . '/' . trim($subdir, '/');
    if (!is_dir($dir)) { mkdir($dir, 0755, true); }
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($tmp, $dest)) {
        flash_set('danger', 'ذخیره تصویر انجام نشد.');
        return $current;
    }
    return 'assets/uploads/' . trim($subdir, '/') . '/' . $name;
}

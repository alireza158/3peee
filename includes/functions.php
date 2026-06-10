<?php
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}
define('DATA_DIR', APP_ROOT . '/data');
define('UPLOAD_DIR', APP_ROOT . '/assets/uploads');

default_timezone_set_if_needed();

function default_timezone_set_if_needed(): void
{
    if (!ini_get('date.timezone')) {
        date_default_timezone_set('Asia/Tehran');
    }
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function data_path(string $file): string
{
    $file = basename($file);
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    return DATA_DIR . '/' . $file;
}

function default_json_value(string $file)
{
    return $file === 'settings.json' ? new stdClass() : [];
}

function read_json(string $file, $default = null)
{
    $path = data_path($file);
    if ($default === null) {
        $default = default_json_value($file);
    }
    if (!file_exists($path)) {
        write_json($file, $default);
        return $default;
    }
    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === '') {
        return $default;
    }
    $decoded = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return $default;
    }
    return $decoded;
}

function write_json(string $file, $data): bool
{
    $path = data_path($file);
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    return file_put_contents($path, $json === false ? '[]' : $json, LOCK_EX) !== false;
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash_set(string $type, string $message): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_get(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

function make_id(): string
{
    return bin2hex(random_bytes(8));
}

function now_string(): string
{
    return date('Y-m-d H:i:s');
}

function sort_items(array $items): array
{
    usort($items, static function ($a, $b) {
        $orderA = (int)($a['sort_order'] ?? 0);
        $orderB = (int)($b['sort_order'] ?? 0);
        if ($orderA === $orderB) {
            return strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? ''));
        }
        return $orderA <=> $orderB;
    });
    return $items;
}

function active_items(string $file): array
{
    $items = read_json($file, []);
    if (!is_array($items)) {
        return [];
    }
    $items = array_values(array_filter($items, static fn($item) => !empty($item['is_active'])));
    return sort_items($items);
}

function find_item(array $items, string $id): ?array
{
    foreach ($items as $item) {
        if ((string)($item['id'] ?? '') === $id) {
            return $item;
        }
    }
    return null;
}

function normalize_tags($value): array
{
    if (is_array($value)) {
        return array_values(array_filter(array_map('trim', $value)));
    }
    return array_values(array_filter(array_map('trim', explode(',', (string)$value))));
}

function setting(array $settings, string $key, string $default = ''): string
{
    return (string)($settings[$key] ?? $default);
}

function admin_page_url(string $page): string
{
    return '/admin/' . ltrim($page, '/');
}

function render_admin_crud(array $config): void
{
    require_admin();
    $file = $config['file'];
    $items = read_json($file, []);
    if (!is_array($items)) { $items = []; }
    $fields = $config['fields'];
    $upload = $config['upload'] ?? null;
    $page = basename($_SERVER['PHP_SELF']);
    $action = $_POST['action'] ?? $_GET['action'] ?? 'list';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf_or_die();
        if ($action === 'save') {
            $id = trim($_POST['id'] ?? '');
            $existing = $id !== '' ? find_item($items, $id) : null;
            $item = $existing ?: ['id' => make_id()];
            foreach ($fields as $name => $field) {
                $type = $field['type'] ?? 'text';
                if ($type === 'bool') {
                    $item[$name] = isset($_POST[$name]);
                } elseif ($type === 'number') {
                    $item[$name] = (int)($_POST[$name] ?? 0);
                } elseif ($type === 'tags') {
                    $item[$name] = normalize_tags($_POST[$name] ?? '');
                } elseif ($type !== 'image') {
                    $item[$name] = trim((string)($_POST[$name] ?? ''));
                }
            }
            if ($upload) {
                $current = (string)($existing[$upload['field']] ?? '');
                $item[$upload['field']] = handle_image_upload($upload['input'], $upload['dir'], $current);
            }
            if (empty($item['created_at'])) { $item['created_at'] = now_string(); }
            $found = false;
            foreach ($items as $i => $old) {
                if ((string)($old['id'] ?? '') === (string)$item['id']) {
                    $items[$i] = $item;
                    $found = true;
                    break;
                }
            }
            if (!$found) { $items[] = $item; }
            write_json($file, array_values($items));
            flash_set('success', 'اطلاعات با موفقیت ذخیره شد.');
            redirect($page);
        }
        if ($action === 'delete') {
            $id = trim($_POST['id'] ?? '');
            $items = array_values(array_filter($items, static fn($item) => (string)($item['id'] ?? '') !== $id));
            write_json($file, $items);
            flash_set('success', 'آیتم حذف شد.');
            redirect($page);
        }
        if ($action === 'toggle') {
            $id = trim($_POST['id'] ?? '');
            foreach ($items as &$item) {
                if ((string)($item['id'] ?? '') === $id) {
                    $item['is_active'] = empty($item['is_active']);
                    break;
                }
            }
            unset($item);
            write_json($file, $items);
            flash_set('success', 'وضعیت تغییر کرد.');
            redirect($page);
        }
    }

    $edit = null;
    if (($action === 'edit') && isset($_GET['id'])) {
        $edit = find_item($items, (string)$_GET['id']);
    }
    $items = sort_items($items);
    $pageTitle = $config['title'];
    include APP_ROOT . '/includes/admin-header.php';
    include APP_ROOT . '/includes/admin-sidebar.php';
    ?>
    <main class="admin-main">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h3 fw-black mb-1"><?= e($config['title']) ?></h1>
                <p class="text-muted mb-0"><?= e($config['subtitle'] ?? 'مدیریت اطلاعات این بخش') ?></p>
            </div>
            <a class="btn btn-brand rounded-pill px-4" href="#editForm">افزودن مورد جدید</a>
        </div>
        <?php foreach (flash_get() as $flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?> rounded-4"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="admin-card p-4" id="editForm">
                    <h2 class="h5 fw-black mb-3"><?= $edit ? 'ویرایش' : 'افزودن' ?></h2>
                    <form method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
                        <?php foreach ($fields as $name => $field):
                            $type = $field['type'] ?? 'text';
                            $value = $edit[$name] ?? ($field['default'] ?? '');
                            if ($type === 'tags' && is_array($value)) { $value = implode(', ', $value); }
                        ?>
                            <div class="mb-3">
                                <?php if ($type === 'bool'): ?>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="<?= e($name) ?>" name="<?= e($name) ?>" <?= !empty($value) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold" for="<?= e($name) ?>"><?= e($field['label']) ?></label>
                                    </div>
                                <?php elseif ($type === 'textarea'): ?>
                                    <label class="form-label fw-bold"><?= e($field['label']) ?></label>
                                    <textarea class="form-control" name="<?= e($name) ?>" rows="4" <?= !empty($field['required']) ? 'required' : '' ?>><?= e($value) ?></textarea>
                                <?php elseif ($type === 'image'): ?>
                                    <label class="form-label fw-bold"><?= e($field['label']) ?></label>
                                    <input class="form-control" type="file" name="<?= e($field['input'] ?? $name) ?>" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                    <?php if (!empty($edit[$name])): ?><div class="small text-muted mt-2">فایل فعلی: <?= e($edit[$name]) ?></div><?php endif; ?>
                                <?php else: ?>
                                    <label class="form-label fw-bold"><?= e($field['label']) ?></label>
                                    <input class="form-control" type="<?= $type === 'number' ? 'number' : 'text' ?>" name="<?= e($name) ?>" value="<?= e($value) ?>" <?= !empty($field['required']) ? 'required' : '' ?>>
                                    <?php if ($type === 'tags'): ?><div class="form-text">با کاما جدا کنید.</div><?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <button class="btn btn-brand w-100 rounded-4 py-2" type="submit">ذخیره</button>
                        <?php if ($edit): ?><a class="btn btn-light w-100 rounded-4 mt-2" href="<?= e($page) ?>">انصراف</a><?php endif; ?>
                    </form>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="admin-card p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 admin-table">
                            <thead><tr><th>عنوان</th><th>ترتیب</th><th>وضعیت</th><th class="text-end">عملیات</th></tr></thead>
                            <tbody>
                            <?php if (!$items): ?>
                                <tr><td colspan="4" class="text-center text-muted py-5">هنوز آیتمی ثبت نشده است.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($items as $item):
                                $titleField = $config['title_field'];
                            ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= e($item[$titleField] ?? 'بدون عنوان') ?></div>
                                        <div class="small text-muted"><?= e($item['description'] ?? $item['subtitle'] ?? $item['position'] ?? '') ?></div>
                                    </td>
                                    <td><?= e($item['sort_order'] ?? '-') ?></td>
                                    <td><span class="badge rounded-pill <?= !empty($item['is_active']) ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= !empty($item['is_active']) ? 'فعال' : 'غیرفعال' ?></span></td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary rounded-pill" href="<?= e($page) ?>?action=edit&id=<?= e($item['id'] ?? '') ?>">ویرایش</a>
                                        <form class="d-inline" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= e($item['id'] ?? '') ?>"><button class="btn btn-sm btn-outline-warning rounded-pill">تغییر وضعیت</button></form>
                                        <form class="d-inline" method="post" onsubmit="return confirm('حذف شود؟')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($item['id'] ?? '') ?>"><button class="btn btn-sm btn-outline-danger rounded-pill">حذف</button></form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include APP_ROOT . '/includes/admin-footer.php';
}

<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$leads = read_json('leads.json', []); if(!is_array($leads)){$leads=[];}
$statuses = ['جدید','در حال پیگیری','انجام شده'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_or_die();
    $action = $_POST['action'] ?? '';
    $id = (string)($_POST['id'] ?? '');
    if ($action === 'status') {
        $status = in_array($_POST['status'] ?? '', $statuses, true) ? $_POST['status'] : 'جدید';
        foreach($leads as &$lead){ if((string)($lead['id'] ?? '') === $id){ $lead['status']=$status; break; } } unset($lead);
        write_json('leads.json',$leads); flash_set('success','وضعیت درخواست تغییر کرد.'); redirect('/admin/leads.php');
    }
    if ($action === 'delete') {
        $leads = array_values(array_filter($leads, static fn($lead)=>(string)($lead['id'] ?? '') !== $id));
        write_json('leads.json',$leads); flash_set('success','درخواست حذف شد.'); redirect('/admin/leads.php');
    }
}
$q = trim($_GET['q'] ?? '');
$filtered = $leads;
if($q !== ''){ $filtered = array_values(array_filter($leads, static fn($lead)=> str_contains((string)($lead['name'] ?? ''), $q) || str_contains((string)($lead['mobile'] ?? ''), $q))); }
$view = isset($_GET['id']) ? find_item($leads, (string)$_GET['id']) : null;
$pageTitle='درخواست‌های مشاوره'; include __DIR__ . '/../includes/admin-header.php'; include __DIR__ . '/../includes/admin-sidebar.php';
?>
<main class="admin-main"><div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 fw-black mb-1">درخواست‌های مشاوره</h1><p class="text-muted mb-0">جستجو، مشاهده جزئیات، تغییر وضعیت و حذف لیدها.</p></div><form class="d-flex gap-2" method="get"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="جستجو نام یا موبایل"><button class="btn btn-brand rounded-pill px-4">جستجو</button></form></div><?php foreach(flash_get() as $flash): ?><div class="alert alert-<?= e($flash['type']) ?> rounded-4"><?= e($flash['message']) ?></div><?php endforeach; ?><?php if($view): ?><div class="admin-card p-4 mb-4"><h2 class="h5 fw-black">جزئیات درخواست</h2><div class="row g-3"><div class="col-md-3"><strong>نام:</strong> <?= e($view['name'] ?? '') ?></div><div class="col-md-3"><strong>موبایل:</strong> <?= e($view['mobile'] ?? '') ?></div><div class="col-md-3"><strong>سطح:</strong> <?= e($view['level'] ?? '') ?></div><div class="col-md-3"><strong>تاریخ:</strong> <?= e($view['created_at'] ?? '') ?></div><div class="col-12"><strong>پیام:</strong><p class="mb-0 text-muted"><?= nl2br(e($view['message'] ?? '')) ?></p></div></div></div><?php endif; ?><div class="admin-card p-0 overflow-hidden"><div class="table-responsive"><table class="table admin-table align-middle mb-0"><thead><tr><th>نام</th><th>موبایل</th><th>سطح</th><th>پیام</th><th>وضعیت</th><th>تاریخ</th><th class="text-end">عملیات</th></tr></thead><tbody><?php if(!$filtered): ?><tr><td colspan="7" class="text-center text-muted py-5">موردی پیدا نشد.</td></tr><?php endif; foreach(array_reverse($filtered) as $lead): ?><tr><td class="fw-bold"><?= e($lead['name'] ?? '') ?></td><td><?= e($lead['mobile'] ?? '') ?></td><td><?= e($lead['level'] ?? '') ?></td><td class="text-muted small"><?= e(mb_substr((string)($lead['message'] ?? ''),0,45)) ?></td><td><form method="post" class="d-flex gap-2"><?= csrf_field() ?><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= e($lead['id'] ?? '') ?>"><select class="form-select form-select-sm" name="status"><?php foreach($statuses as $status): ?><option <?= (($lead['status'] ?? '')===$status)?'selected':'' ?>><?= e($status) ?></option><?php endforeach; ?></select><button class="btn btn-sm btn-outline-primary rounded-pill">ثبت</button></form></td><td><?= e($lead['created_at'] ?? '') ?></td><td class="text-end"><a class="btn btn-sm btn-outline-info rounded-pill" href="?id=<?= e($lead['id'] ?? '') ?>">جزئیات</a><form class="d-inline" method="post" onsubmit="return confirm('حذف شود؟')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($lead['id'] ?? '') ?>"><button class="btn btn-sm btn-outline-danger rounded-pill">حذف</button></form></td></tr><?php endforeach; ?></tbody></table></div></div></main><?php include __DIR__ . '/../includes/admin-footer.php'; ?>

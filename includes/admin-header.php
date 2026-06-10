<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/upload.php';
$admin = current_admin();
$pageTitle = $pageTitle ?? 'پنل مدیریت';
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | 3pe</title>
    <link rel="icon" type="image/png" href="/assets/logo.png">
    <link href="/assets/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        @font-face{font-family:Vazirmatn;src:url('/assets/fonts/Vazirmatn.woff2') format('woff2');font-weight:100 900;font-style:normal;font-display:swap}
        :root{--primary:#5b21b6;--primary2:#7c3aed;--secondary:#0284c7;--dark:#0f172a;--muted:#64748b;--line:rgba(15,23,42,.08);--bg:#f8fafc}
        *{box-sizing:border-box} body{font-family:Vazirmatn,Tahoma,sans-serif;background:radial-gradient(800px 420px at 90% -10%,rgba(91,33,182,.13),transparent 60%),linear-gradient(180deg,#f8fafc,#fff);color:#1e293b;min-height:100vh}
        a{text-decoration:none}.fw-black{font-weight:950}.btn-brand{border:0;color:#fff!important;background:linear-gradient(90deg,var(--primary),var(--secondary));box-shadow:0 14px 30px rgba(91,33,182,.18)}
        .admin-shell{min-height:100vh}.admin-sidebar{width:280px;position:fixed;inset:0 0 0 auto;background:#fff;border-left:1px solid var(--line);box-shadow:0 20px 60px rgba(15,23,42,.08);z-index:1030;padding:18px;overflow:auto}.admin-main{margin-right:280px;padding:30px}.admin-card{background:rgba(255,255,255,.92);border:1px solid var(--line);border-radius:24px;box-shadow:0 18px 50px rgba(15,23,42,.07)}
        .brand-box{display:flex;align-items:center;gap:10px;padding:12px 10px 20px;border-bottom:1px solid var(--line);margin-bottom:16px}.brand-box img{width:46px;height:46px;object-fit:contain}.nav-admin a{display:flex;align-items:center;gap:10px;color:#334155;padding:12px 14px;border-radius:16px;font-weight:800;margin-bottom:6px}.nav-admin a:hover,.nav-admin a.active{background:linear-gradient(90deg,rgba(91,33,182,.11),rgba(2,132,199,.10));color:var(--primary)}
        .topbar{display:none}.stat-card{position:relative;overflow:hidden}.stat-card:before{content:"";position:absolute;inset:auto -30px -45px auto;width:110px;height:110px;border-radius:50%;background:rgba(255,255,255,.20)}.admin-table thead th{background:#f8fafc;color:#475569;font-size:13px;padding:16px}.admin-table td{padding:16px;border-color:var(--line)}.form-control,.form-select{border-radius:14px;border-color:rgba(15,23,42,.12);padding:.72rem .9rem}.form-control:focus,.form-select:focus{border-color:rgba(91,33,182,.45);box-shadow:0 0 0 .25rem rgba(91,33,182,.10)}
        @media(max-width:991px){.admin-sidebar{position:static;width:auto;border:0;border-bottom:1px solid var(--line);border-radius:0}.admin-main{margin-right:0;padding:18px}.topbar{display:flex}.nav-admin{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:6px}.nav-admin a{font-size:13px;margin:0}}
    </style>
</head>
<body>
<div class="admin-shell">

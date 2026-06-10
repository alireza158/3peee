<?php
require_once __DIR__ . '/../includes/auth.php';
render_admin_crud([
 'file'=>'faqs.json','title'=>'مدیریت سوالات متداول','subtitle'=>'سوال و پاسخ‌های پرتکرار را مدیریت کنید.','title_field'=>'question',
 'fields'=>[
  'question'=>['label'=>'سوال','required'=>true], 'answer'=>['label'=>'پاسخ','type'=>'textarea','required'=>true], 'sort_order'=>['label'=>'ترتیب نمایش','type'=>'number','default'=>0], 'is_active'=>['label'=>'فعال باشد','type'=>'bool','default'=>true]
 ]
]);

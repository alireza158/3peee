<?php
require_once __DIR__ . '/../includes/auth.php';
render_admin_crud([
 'file'=>'gifts.json','title'=>'مدیریت هدایا','subtitle'=>'هدایا و بونوس‌های دوره را مدیریت کنید.','title_field'=>'title',
 'fields'=>[
  'title'=>['label'=>'عنوان','required'=>true], 'description'=>['label'=>'توضیحات','type'=>'textarea'], 'icon'=>['label'=>'آیکن/ایموجی'], 'sort_order'=>['label'=>'ترتیب نمایش','type'=>'number','default'=>0], 'is_active'=>['label'=>'فعال باشد','type'=>'bool','default'=>true]
 ]
]);

<?php
require_once __DIR__ . '/../includes/auth.php';
render_admin_crud([
 'file'=>'syllabus.json','title'=>'مدیریت سرفصل‌ها','subtitle'=>'مراحل و محتوای آموزشی دوره را مدیریت کنید.','title_field'=>'title',
 'fields'=>[
  'step_number'=>['label'=>'شماره مرحله','type'=>'number','default'=>1], 'title'=>['label'=>'عنوان','required'=>true], 'description'=>['label'=>'توضیحات','type'=>'textarea'], 'tags'=>['label'=>'تگ‌ها','type'=>'tags'], 'icon'=>['label'=>'آیکن/ایموجی'], 'sort_order'=>['label'=>'ترتیب نمایش','type'=>'number','default'=>0], 'is_active'=>['label'=>'فعال باشد','type'=>'bool','default'=>true]
 ]
]);

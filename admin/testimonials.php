<?php
require_once __DIR__ . '/../includes/auth.php';
render_admin_crud([
 'file'=>'testimonials.json','title'=>'مدیریت نظرات دانشجویان','subtitle'=>'نظرها، امتیازها و ترتیب نمایش آن‌ها را مدیریت کنید.','title_field'=>'name',
 'fields'=>[
  'name'=>['label'=>'نام','required'=>true], 'role'=>['label'=>'نقش/توضیح کوتاه'], 'comment'=>['label'=>'متن نظر','type'=>'textarea','required'=>true], 'rating'=>['label'=>'امتیاز','type'=>'number','default'=>5], 'avatar_letter'=>['label'=>'حرف آواتار'], 'sort_order'=>['label'=>'ترتیب نمایش','type'=>'number','default'=>0], 'is_active'=>['label'=>'فعال باشد','type'=>'bool','default'=>true]
 ]
]);

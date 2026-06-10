<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
render_admin_crud([
 'file'=>'works.json','title'=>'مدیریت نمونه‌کارها','subtitle'=>'افزودن، ویرایش، حذف، فعال/غیرفعال و ترتیب نمایش نمونه‌کارها.','title_field'=>'title',
 'upload'=>['field'=>'image','input'=>'image_file','dir'=>'works'],
 'fields'=>[
  'title'=>['label'=>'عنوان','required'=>true], 'subtitle'=>['label'=>'زیرعنوان'], 'description'=>['label'=>'توضیحات','type'=>'textarea'], 'image'=>['label'=>'تصویر','type'=>'image','input'=>'image_file'], 'label'=>['label'=>'برچسب'], 'sort_order'=>['label'=>'ترتیب نمایش','type'=>'number','default'=>0], 'is_active'=>['label'=>'فعال باشد','type'=>'bool','default'=>true]
 ]
]);

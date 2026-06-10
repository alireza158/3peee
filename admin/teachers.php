<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
render_admin_crud([
 'file'=>'teachers.json','title'=>'مدیریت اساتید','subtitle'=>'اطلاعات، تصویر، تجربه و مهارت‌های اساتید را مدیریت کنید.','title_field'=>'name',
 'upload'=>['field'=>'image','input'=>'image_file','dir'=>'teachers'],
 'fields'=>[
  'name'=>['label'=>'نام','required'=>true], 'position'=>['label'=>'سمت/عنوان'], 'description'=>['label'=>'توضیحات','type'=>'textarea'], 'image'=>['label'=>'تصویر','type'=>'image','input'=>'image_file'], 'tags'=>['label'=>'تگ‌ها','type'=>'tags'], 'experience'=>['label'=>'تجربه'], 'sort_order'=>['label'=>'ترتیب نمایش','type'=>'number','default'=>0], 'is_active'=>['label'=>'فعال باشد','type'=>'bool','default'=>true]
 ]
]);

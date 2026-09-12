<?php
$app = require '/qa/bootstrap.php';
if (file_exists('/qa/dashboard.sqlite')) throw new RuntimeException('QA fixture already exists; refusing reset.');
touch('/qa/dashboard.sqlite');
Illuminate\Support\Facades\Artisan::call('migrate', ['--force'=>true]);
App\Models\User::create(['name'=>'QA Administrator','email'=>'qa@example.test','password'=>'Qa-Only-2026!','role'=>'superadmin','is_active'=>true]);
App\Models\User::create(['name'=>'QA Inactive','email'=>'inactive@example.test','password'=>'Qa-Only-2026!','role'=>'superadmin','is_active'=>false]);
App\Models\User::create(['name'=>'QA Guest','email'=>'guest@example.test','password'=>'Qa-Only-2026!','role'=>'guest','is_active'=>true]);
Illuminate\Support\Facades\Artisan::call('db:seed', ['--class'=>'Database\\Seeders\\CmsSeeder','--force'=>true]);
for ($i=1;$i<=23;$i++) App\Models\Enquiry::create(['guest_name'=>"QA Guest $i",'email'=>"guest$i@example.test",'category'=>'Room & stay','message'=>'QA sample enquiry','status'=>$i<=21?'new':'read']);
file_put_contents('/qa/ready', 'SQLite fixture ready');
echo "Isolated SQLite QA database and synthetic users/content created.\n";

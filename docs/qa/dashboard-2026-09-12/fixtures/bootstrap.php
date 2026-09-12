<?php
require '/var/www/html/vendor/autoload.php';
foreach (['APP_ENV'=>'local','APP_DEBUG'=>'true','APP_KEY'=>'base64:'.base64_encode(str_repeat('q',32)), 'APP_URL'=>'http://127.0.0.1:9011','DB_CONNECTION'=>'sqlite','DB_DATABASE'=>'/qa/dashboard.sqlite','DB_URL'=>'','CACHE_STORE'=>'array','SESSION_DRIVER'=>'file','SESSION_COOKIE'=>'isolated_dashboard_qa','SESSION_DOMAIN'=>'','QUEUE_CONNECTION'=>'sync','MAIL_MAILER'=>'array','VIEW_COMPILED_PATH'=>'/qa/storage/framework/views','APP_CONFIG_CACHE'=>'/qa/config.php'] as $key=>$value) {
    putenv("$key=$value"); $_ENV[$key]=$value; $_SERVER[$key]=$value;
}
$app = require '/var/www/html/bootstrap/app.php';
$app->useEnvironmentPath('/qa');
$app->useStoragePath('/qa/storage');
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['filesystems.disks.public.root'=>'/qa/uploads','filesystems.disks.public.url'=>'http://127.0.0.1:9011/storage']);
if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== '/qa/dashboard.sqlite' || config('database.connections.sqlite.url')) throw new RuntimeException('Unsafe QA database');
return $app;

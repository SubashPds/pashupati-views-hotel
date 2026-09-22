<?php

use App\Models\Faq;
use App\Models\Package;
use App\Models\Room;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';

if (getenv('MYSQL_LIMITS_TEST') !== '1') {
    throw new RuntimeException('Run this check through tests/mysql/validation-limits.sh.');
}

$storage = sys_get_temp_dir().'/pashupati-mysql-validation';
foreach (['framework/cache', 'framework/sessions', 'framework/views', 'logs'] as $directory) {
    if (! is_dir($storage.'/'.$directory)) {
        mkdir($storage.'/'.$directory, 0777, true);
    }
}

// Never load the site's environment or cached configuration for this check.
foreach ([
    'APP_ENV' => 'testing',
    'APP_KEY' => 'base64:'.base64_encode(str_repeat('x', 32)),
    'APP_CONFIG_CACHE' => $storage.'/config.php',
    'APP_SERVICES_CACHE' => $storage.'/services.php',
    'APP_PACKAGES_CACHE' => $storage.'/packages.php',
    'APP_ROUTES_CACHE' => $storage.'/routes.php',
    'APP_EVENTS_CACHE' => $storage.'/events.php',
    'DB_CONNECTION' => 'mysql',
    'DB_URL' => '',
    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '3306',
    'DB_DATABASE' => 'validation_limits_test',
    'DB_USERNAME' => 'root',
    'DB_PASSWORD' => 'validation-test-password',
    'DB_SOCKET' => '',
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'array',
    'LOG_CHANNEL' => 'stderr',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->loadEnvironmentFrom('.env.mysql-validation-test');
$app->useStoragePath($storage);
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

if (DB::getDriverName() !== 'mysql' || DB::getDatabaseName() !== 'validation_limits_test' || DB::select('SHOW TABLES') !== []) {
    throw new RuntimeException('An empty, isolated MySQL validation_limits_test database is required.');
}
if ($kernel->call('migrate', ['--force' => true]) !== 0) {
    throw new RuntimeException($kernel->output());
}
if (! str_contains(DB::scalar('SELECT @@SESSION.sql_mode'), 'STRICT_')) {
    throw new RuntimeException('MySQL strict mode must be enabled.');
}

$reject = function (Closure $operation, int $errorCode): void {
    try {
        $operation();
    } catch (QueryException $exception) {
        if (($exception->errorInfo[1] ?? null) !== $errorCode) {
            throw $exception;
        }

        return;
    }

    throw new RuntimeException('MySQL accepted a value beyond the declared column limit.');
};

$cases = [
    ['FAQ question', Faq::class, ['question' => str_repeat('界', 255), 'answer' => 'Answer'], 'question', str_repeat('界', 256), 1406],
    ['Package minimum guests', Package::class, ['name' => 'Minimum guests', 'min_guests' => 127], 'min_guests', 128, 1264],
    ['Package maximum guests', Package::class, ['name' => 'Maximum guests', 'min_guests' => 127, 'max_guests' => 127], 'max_guests', 128, 1264],
    ['Package price', Package::class, ['name' => 'Price package', 'price_from' => '99999999.99'], 'price_from', '100000000.00', 1264],
    ['Room price', Room::class, ['name' => 'Price room', 'category' => 'deluxe', 'price_per_night' => '99999999.99', 'max_guests' => 2], 'price_per_night', '100000000.00', 1264],
];

foreach ($cases as [$label, $model, $data, $field, $overflow, $errorCode]) {
    $record = $model::create($data);
    if ((string) $record->fresh()->$field !== (string) $data[$field]) {
        throw new RuntimeException($label.' did not retain the accepted boundary value.');
    }
    $invalid = array_replace($data, [$field => $overflow]);
    if (isset($invalid['name'])) {
        $invalid['name'] .= ' overflow';
    }
    $reject(fn () => $model::create($invalid), $errorCode);
    $reject(fn () => $record->update([$field => $overflow]), $errorCode);
    if ((string) $record->fresh()->$field !== (string) $data[$field]) {
        throw new RuntimeException($label.' changed after a rejected update.');
    }
    echo $label.': boundary saved; overflow insert and update rejected.'.PHP_EOL;
}

echo 'MySQL '.DB::scalar('SELECT VERSION()').': all 5 schema boundary checks passed using application migrations.'.PHP_EOL;

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Purge dummy 'p' placeholders injected by container environment
foreach (['DB_CONNECTION', 'SESSION_DRIVER', 'CACHE_STORE', 'QUEUE_CONNECTION', 'BCRYPT_ROUNDS', 'APP_KEY', 'APP_ENV', 'LOG_CHANNEL', 'MAIL_MAILER', 'APP_TIMEZONE'] as $k) {
    if (getenv($k) === 'p') {
        putenv($k);
        unset($_ENV[$k], $_SERVER[$k]);
    }
}

$isTest = (getenv('APP_ENV') === 'testing' || defined('PHPUNIT_COMPOSER_INSTALL') || str_contains($_SERVER['argv'][0] ?? '', 'test') || str_contains($_SERVER['argv'][0] ?? '', 'phpunit'));
$envFile = ($isTest && file_exists(dirname(__DIR__).'/.env.testing')) ? '.env.testing' : '.env';
if (file_exists(dirname(__DIR__).'/'.$envFile)) {
    \Dotenv\Dotenv::createMutable(dirname(__DIR__), $envFile)->safeLoad();
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'organization' => \App\Http\Middleware\EnforceOrganization::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

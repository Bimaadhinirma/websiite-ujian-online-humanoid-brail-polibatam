<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Custom serve command: defaults host to 0.0.0.0 so you can run
// `php artisan serve` without passing --host=0.0.0.0
Artisan::command('serve {--host=0.0.0.0} {--port=8000}', function () {
    $host = $this->option('host') ?: '0.0.0.0';
    $port = $this->option('port') ?: 8000;

    $this->comment("Starting Laravel development server on http://{$host}:{$port}");

    // Use the current PHP binary and serve the `public` directory
    $binary = defined('PHP_BINARY') ? PHP_BINARY : 'php';

    // Build the command and execute it. passthru will forward output to the console.
    $cmd = escapeshellcmd($binary) . ' -S ' . escapeshellarg("{$host}:{$port}") . ' -t ' . escapeshellarg(base_path('public'));

    // Execute and return the same exit code as the PHP built-in server
    passthru($cmd, $exitCode);
    return $exitCode;
})->describe('Serve the application on the PHP development server (default host 0.0.0.0)');

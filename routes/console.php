<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('demo:reset {--force : Allow reset while APP_ENV=production}', function (): int {
    if (! config('demo.enabled')) {
        $this->error('Refusing to reset: DEMO_MODE is not enabled.');

        return 1;
    }

    if (app()->environment('production') && ! $this->option('force')) {
        $this->error('Production demo reset requires --force.');

        return 1;
    }

    Storage::disk('local')->deleteDirectory('request_attachments');

    $exitCode = $this->call('migrate:fresh', [
        '--seed' => true,
        '--force' => true,
    ]);

    if ($exitCode !== 0) {
        return $exitCode;
    }

    $this->info('Public demo data reset completed.');

    return 0;
})->purpose('Reset disposable public demo data safely when DEMO_MODE=true');

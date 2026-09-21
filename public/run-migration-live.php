<?php

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    $migrateOutput = Illuminate\Support\Facades\Artisan::output();

    Illuminate\Support\Facades\Artisan::call('config:clear');
    Illuminate\Support\Facades\Artisan::call('route:clear');
    Illuminate\Support\Facades\Artisan::call('view:clear');

    echo '<div style="font-family: sans-serif; padding: 40px; text-align: center; max-width: 600px; margin: 40px auto; border: 1px solid #10b981; border-radius: 12px; background: #ecfdf5;">';
    echo '<h2 style="color: #065f46; margin-bottom: 16px;">✅ Live Database Migrated Successfully!</h2>';
    echo '<p style="color: #047857; line-height: 1.6;">The 6 site management tables (<code>material_categories</code>, <code>material_entries</code>, <code>labour_entries</code>, <code>vendor_categories</code>, <code>vendors</code>, <code>vendor_payments</code>) have been created on Hostinger database <b>u276278848_recrm</b>.</p>';
    echo '<pre style="text-align: left; background: #ffffff; padding: 16px; border-radius: 8px; border: 1px solid #a7f3d0; max-height: 200px; overflow-y: auto; color: #1e293b;">' . htmlspecialchars($migrateOutput) . '</pre>';
    echo '<a href="/projects/1/site-management" style="display: inline-block; margin-top: 20px; padding: 12px 24px; background: #10b981; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">👉 Go to Site Management</a>';
    echo '</div>';
} catch (\Throwable $e) {
    echo '<div style="font-family: sans-serif; padding: 40px; text-align: center; max-width: 600px; margin: 40px auto; border: 1px solid #ef4444; border-radius: 12px; background: #fef2f2;">';
    echo '<h2 style="color: #991b1b; margin-bottom: 16px;">❌ Migration Error</h2>';
    echo '<p style="color: #b91c1c;">' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
}

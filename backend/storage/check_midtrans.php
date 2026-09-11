<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$cfg = config("midtrans");
echo "midtrans.server_key set: " . ((bool)$cfg["server_key"] ? "YES" : "NO") . "\n";
echo "midtrans.client_key set: " . ((bool)$cfg["client_key"] ? "YES" : "NO") . "\n";
echo "midtrans.is_production: " . ($cfg["is_production"] ? "true" : "false") . "\n";
echo "server_key pattern: " . ((bool)preg_match('/^Mid-server-/', $cfg["server_key"]) ? "YES(server-only-prefix)" : "NO") . "\n";

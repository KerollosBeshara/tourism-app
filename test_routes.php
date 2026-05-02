<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

$modules = app(\Nwidart\Modules\Facades\Module::class)->all();
echo "Loaded modules:\n";
foreach ($modules as $module) {
    echo "  - " . $module->getName() . " (enabled: " . ($module->isEnabled() ? 'yes' : 'no') . ")\n";
}

echo "\nRegistered service providers:\n";
$providers = app()->getProviders();
foreach ($providers as $provider) {
    if (strpos(get_class($provider), 'RouteServiceProvider') !== false) {
        echo "  - " . get_class($provider) . "\n";
    }
}

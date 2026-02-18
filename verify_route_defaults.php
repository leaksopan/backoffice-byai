<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Request::create('/m/master-data', 'GET');

// Match route
$route = app('router')->getRoutes()->match($request);

echo "Route Name: " . $route->getName() . "\n";
echo "Defaults: " . json_encode($route->defaults) . "\n";
echo "Parameter moduleKey: " . $route->parameter('moduleKey') . "\n";

// Also check resource route (which has no default yet)
$request2 = Request::create('/m/master-data/units', 'GET');
try {
    $route2 = app('router')->getRoutes()->match($request2);
    echo "Resource Route Name: " . $route2->getName() . "\n";
    echo "Resource Defaults: " . json_encode($route2->defaults) . "\n";
    echo "Resource Parameter moduleKey: " . $route2->parameter('moduleKey') . "\n";
} catch (\Exception $e) {
    echo "Resource verification failed: " . $e->getMessage() . "\n";
}

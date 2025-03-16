<?php
// Enable error display for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use App\Services\Logger;
use App\Services\Config;

// Initialize configuration
Config::getInstance();

// Debug information
error_log("=== Request Debug Info ===");
error_log("SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME']);
error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
error_log("PHP_SELF: " . $_SERVER['PHP_SELF']);
error_log("DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT']);
error_log("Base Path: " . Config::get('app.base_path'));
error_log("App URL: " . Config::get('app.url'));

// Database configuration
$capsule = new Capsule;
$capsule->addConnection(Config::get('database'));
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Create Container and App
$container = new Container();

// Register controllers in the container
$container->set('App\Controllers\ProjectController', function($container) {
    return new \App\Controllers\ProjectController();
});
$container->set('App\Controllers\TableController', function($container) {
    return new \App\Controllers\TableController();
});

AppFactory::setContainer($container);
$app = AppFactory::create();

// Set base path from config
$basePath = Config::get('app.base_path');
error_log("Setting Slim base path to: " . $basePath);
$app->setBasePath($basePath);

// Add routing middleware
$app->addRoutingMiddleware();

// Add error middleware with detailed error information
$errorMiddleware = $app->addErrorMiddleware(
    Config::get('app.debug'),
    Config::get('app.debug'),
    Config::get('app.debug')
);

// Add routes from routes.php
$routes = require __DIR__ . '/../src/routes.php';
$routes($app);

// Debug registered routes
$routes = $app->getRouteCollector()->getRoutes();
error_log("=== Registered Routes ===");
foreach ($routes as $route) {
    error_log($route->getPattern() . " [" . implode(',', $route->getMethods()) . "]");
}

// Run app
$app->run(); 
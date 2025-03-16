<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Debug information
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Script Name: " . $_SERVER['SCRIPT_NAME']);
error_log("PHP Self: " . $_SERVER['PHP_SELF']);

// Set the project root directory
$projectRoot = __DIR__;

// Set the public path
$publicPath = $projectRoot . '/public';

// Check if the requested file exists in public directory
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestedFile = $publicPath . $requestUri;

error_log("Requested File: " . $requestedFile);
error_log("File Exists: " . (file_exists($requestedFile) ? 'true' : 'false'));
error_log("Is Directory: " . (is_dir($requestedFile) ? 'true' : 'false'));

if (file_exists($requestedFile) && !is_dir($requestedFile)) {
    // If it's a real file, serve it directly
    return false;
} else {
    // Otherwise, include the front controller
    require $publicPath . '/index.php';
} 
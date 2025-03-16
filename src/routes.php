<?php

use App\Controllers\ProjectController;
use App\Controllers\TableController;
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    // Debug route to verify routing is working
    $app->get('/debug', function (Request $request, Response $response) {
        $response->getBody()->write('Debug route works!');
        return $response;
    });

    // Home page (list of projects)
    $app->get('/', ProjectController::class . ':index');

    // Projects routes
    $app->get('/projects/new', ProjectController::class . ':create');
    $app->post('/projects', ProjectController::class . ':store');
    $app->get('/projects/{id}', ProjectController::class . ':show');
    $app->get('/projects/{id}/edit', ProjectController::class . ':edit');
    $app->post('/projects/{id}', ProjectController::class . ':update');
    $app->get('/projects/{id}/clone', ProjectController::class . ':clone');
    $app->delete('/api/projects/{id}', ProjectController::class . ':destroy');

    // Tables routes
    $app->get('/projects/{projectId}/tables/new', TableController::class . ':create');
    $app->post('/projects/{projectId}/tables', TableController::class . ':store');
    $app->get('/tables/{id}', TableController::class . ':show');
    $app->get('/tables/{id}/edit', TableController::class . ':edit');
    $app->post('/tables/{id}', TableController::class . ':update');
    $app->get('/tables/{id}/clone', TableController::class . ':clone');
    $app->delete('/api/tables/{id}', TableController::class . ':destroy');

    // API routes for AJAX operations
    $app->post('/api/tables/{id}/entries', TableController::class . ':updateEntries');

    // Export routes
    $app->get('/tables/{id}/export/markdown', TableController::class . ':exportMarkdown');
    $app->get('/tables/{id}/export/csv', TableController::class . ':exportCsv');

    // Iframe tester route
    $app->get('/tables/{id}/iframe', TableController::class . ':iframe');

    // oEmbed API endpoint
    $app->get('/api/oembed', TableController::class . ':oembed');
}; 
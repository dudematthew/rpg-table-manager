<?php

namespace App\Controllers;

use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface as Response;
use App\Services\Logger;
use App\Services\Config;
use Monolog\Logger as MonologLogger;

abstract class Controller
{
    protected Engine $templates;
    protected MonologLogger $logger;
    protected Config $config;

    public function __construct()
    {
        // Initialize configuration
        $this->config = Config::getInstance();
        
        // Initialize the template engine with absolute path
        $templatesPath = dirname(__DIR__, 2) . '/templates';
        $this->templates = new Engine($templatesPath);
        
        // Initialize logger
        $this->logger = Logger::getInstance();
        
        // Add common template data
        $this->templates->addData([
            'basePath' => $this->config->get('app.base_path'),
            'urlFor' => function($name, $params = []) {
                return $this->config->get('app.base_path') . '/' . trim($name, '/');
            }
        ]);
    }

    protected function render(Response $response, string $template, array $data = []): Response
    {
        $this->logger->debug('Rendering template', [
            'template' => $template,
            'data' => $data
        ]);
        
        $response->getBody()->write($this->templates->render($template, $data));
        return $response->withHeader('Content-Type', 'text/html');
    }

    protected function json(Response $response, array $data, int $status = 200): Response
    {
        $this->logger->debug('Sending JSON response', [
            'data' => $data,
            'status' => $status
        ]);
        
        $jsonString = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        
        if ($jsonString === false) {
            $this->logger->error('JSON encode error', [
                'error' => json_last_error_msg(),
                'data' => $data
            ]);
            throw new \RuntimeException('Failed to encode response as JSON: ' . json_last_error_msg());
        }
        
        $response->getBody()->write($jsonString);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Cache-Control', 'no-cache')
            ->withStatus($status);
    }

    protected function redirect(Response $response, string $url): Response
    {
        // Handle both absolute and relative URLs
        if (strpos($url, 'http') !== 0 && $url[0] === '/') {
            $url = $this->config->get('app.base_path') . $url;
        }
        
        $this->logger->debug('Redirecting', ['url' => $url]);
        
        return $response
            ->withHeader('Location', $url)
            ->withStatus(302);
    }
} 
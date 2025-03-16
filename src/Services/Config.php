<?php

namespace App\Services;

use Monolog\Level;

class Config
{
    private static ?Config $instance = null;
    private array $config;
    private $logger;

    private function __construct()
    {
        $rootDir = dirname(__DIR__, 2);
        
        // Initialize basic config first (needed for logger)
        $this->initializeBasicConfig();
        
        // Now we can initialize logger
        $this->logger = Logger::getInstance();
        $this->logger->info("Config initialization started", ['root_dir' => $rootDir]);
        
        // Load the appropriate .env file based on environment
        $envFile = $this->loadEnvironmentFile($rootDir);
        
        // Debug environment variables after loading
        $this->logger->debug("Environment variables after loading", [
            'APP_ENV' => $_ENV['APP_ENV'] ?? 'not set',
            'DB_HOST' => $_ENV['DB_HOST'] ?? 'not set',
            'DB_NAME' => $_ENV['DB_NAME'] ?? 'not set',
            'APP_URL' => $_ENV['APP_URL'] ?? 'not set'
        ]);
        
        // Set the configuration based on environment
        $isProd = ($_ENV['APP_ENV'] ?? 'development') === 'production';
        $this->logger->info("Environment configuration", ['is_production' => $isProd]);
        
        // Determine base path from APP_URL or use default values
        $basePath = $this->determineBasePath($isProd);
        $this->logger->info("Base path determined", ['base_path' => $basePath]);
        
        // Update config with environment values
        $this->updateConfigWithEnv($isProd, $basePath);
    }

    private function initializeBasicConfig(): void
    {
        // Set minimal config needed for logger initialization
        $this->config = [
            'app' => [
                'env' => 'development',
                'debug' => true
            ],
            'logging' => [
                'max_files' => 5,
                'max_file_size' => 5242880,
                'date_format' => 'Y-m-d H:i:s'
            ]
        ];
    }

    private function updateConfigWithEnv(bool $isProd, string $basePath): void
    {
        $this->config = [
            'app' => [
                'env' => $_ENV['APP_ENV'] ?? 'development',
                'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'url' => $_ENV['APP_URL'] ?? ($isProd ? 'https://dudematthew.smallhost.pl/rpgtm' : 'http://localhost/rpg-table-manager'),
                'base_path' => $basePath
            ],
            'database' => [
                'driver' => 'mysql',
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'database' => $_ENV['DB_NAME'] ?? 'rpg_table_manager',
                'username' => $_ENV['DB_USER'] ?? 'root',
                'password' => $_ENV['DB_PASS'] ?? '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => ''
            ],
            'logging' => [
                'max_files' => 5,
                'max_file_size' => 5242880,
                'date_format' => 'Y-m-d H:i:s'
            ]
        ];
    }

    private function determineBasePath(bool $isProd): string
    {
        // Try to extract base path from APP_URL if set
        if (isset($_ENV['APP_URL'])) {
            $parsedUrl = parse_url($_ENV['APP_URL']);
            if (isset($parsedUrl['path'])) {
                $path = rtrim($parsedUrl['path'], '/');
                $this->logger->debug("Base path extracted from APP_URL", ['path' => $path]);
                return $path;
            }
        }

        // Default paths if APP_URL is not set or doesn't contain a path
        $path = $isProd ? '/rpgtm' : '/rpg-table-manager';
        $this->logger->debug("Using default base path", ['path' => $path, 'is_production' => $isProd]);
        return $path;
    }

    private function loadEnvironmentFile(string $rootDir): string
    {
        // First try to get environment from $_ENV
        $env = $_ENV['APP_ENV'] ?? null;
        
        // If not in $_ENV, try getenv()
        if (!$env) {
            $env = getenv('APP_ENV');
        }
        
        // Default to development if not set
        $env = $env ?: 'development';
        
        $this->logger->info("Environment detection", ['detected_env' => $env]);
        
        // Try environment-specific file first
        $envFile = "{$rootDir}/.env.{$env}";
        $this->logger->debug("Looking for environment file", ['file' => $envFile]);
        
        if (file_exists($envFile)) {
            $this->logger->info("Found environment-specific file", ['file' => basename($envFile)]);
        } else {
            $this->logger->info("Environment-specific file not found, trying .env");
            $envFile = "{$rootDir}/.env";
            
            if (!file_exists($envFile)) {
                $this->logger->warning("No .env file found, attempting to copy from template");
                $sourceFile = "{$rootDir}/.env.{$env}";
                if (file_exists($sourceFile)) {
                    copy($sourceFile, $envFile);
                    $this->logger->info("Copied environment file", [
                        'from' => basename($sourceFile),
                        'to' => basename($envFile)
                    ]);
                } else {
                    $this->logger->error("No environment file found and couldn't copy from template", [
                        'attempted_source' => $sourceFile
                    ]);
                    return $envFile;
                }
            }
        }

        try {
            $this->logger->info("Loading environment file", ['file' => basename($envFile)]);
            $dotenv = \Dotenv\Dotenv::createImmutable($rootDir, basename($envFile));
            $dotenv->load();
            
            // Verify some key variables were loaded
            $loaded = [
                'APP_ENV' => $_ENV['APP_ENV'] ?? 'not set',
                'DB_HOST' => $_ENV['DB_HOST'] ?? 'not set',
                'DB_NAME' => $_ENV['DB_NAME'] ?? 'not set'
            ];
            $this->logger->info("Environment variables loaded", $loaded);
        } catch (\Exception $e) {
            $this->logger->error("Error loading environment file", [
                'error' => $e->getMessage(),
                'file' => basename($envFile)
            ]);
        }
        
        return $envFile;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function get(string $key, $default = null)
    {
        return self::getInstance()->getValue($key, $default);
    }

    private function getValue(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $config = $this->config;

        foreach ($keys as $key) {
            if (!isset($config[$key])) {
                if (isset($this->logger)) {
                    $this->logger->warning("Configuration key not found", [
                        'key' => $key,
                        'using_default' => $default
                    ]);
                }
                return $default;
            }
            $config = $config[$key];
        }

        return $config;
    }
} 
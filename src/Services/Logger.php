<?php

namespace App\Services;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Level;
use Monolog\LogRecord;

class Logger
{
    private static ?MonologLogger $instance = null;
    
    // Constants for log configuration
    const MAX_FILES = 5;           // Keep 5 days of logs
    const MAX_FILE_SIZE = 5242880; // 5MB per file
    const DATE_FORMAT = "Y-m-d H:i:s";

    public static function getInstance(): MonologLogger
    {
        if (self::$instance === null) {
            self::$instance = self::createLogger();
        }

        return self::$instance;
    }

    private static function createLogger(): MonologLogger
    {
        $logger = new MonologLogger('rpg-table-manager');

        // Create a formatter that includes datetime, level, and message
        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            self::DATE_FORMAT
        );

        // Add handlers in order of preference
        $handlers = self::getHandlers();
        foreach ($handlers as $handler) {
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
        }

        return $logger;
    }

    private static function getHandlers(): array
    {
        $handlers = [];
        $rootDir = dirname(__DIR__, 2);
        $logsDir = $rootDir . DIRECTORY_SEPARATOR . 'logs';

        // Ensure logs directory exists with proper permissions
        self::ensureLogsDirectoryExists($logsDir);

        // Primary handler: Rotating file handler for daily logs
        if (is_writable($logsDir)) {
            try {
                $logFile = $logsDir . DIRECTORY_SEPARATOR . 'app.log';
                
                // Ensure the log file exists and is writable
                if (!file_exists($logFile)) {
                    $handle = fopen($logFile, 'a');
                    if ($handle) {
                        fclose($handle);
                        // On Windows, we need to explicitly set permissions
                        self::setWindowsPermissions($logFile);
                    }
                }

                if (is_writable($logFile)) {
                    $rotatingHandler = new RotatingFileHandler(
                        $logFile,
                        self::MAX_FILES,
                        Level::Debug,
                        true, // bubble
                        0644  // file permissions (ignored on Windows)
                    );
                    $handlers[] = $rotatingHandler;
                } else {
                    error_log("Warning: Log file not writable: " . $logFile);
                }
            } catch (\Exception $e) {
                error_log('Failed to create rotating file handler: ' . $e->getMessage());
            }
        } else {
            error_log("Warning: Logs directory not writable: " . $logsDir);
        }

        // Backup handler: Stream to PHP error log (always add this as fallback)
        try {
            $handlers[] = new ErrorLogHandler(
                ErrorLogHandler::OPERATING_SYSTEM,
                Level::Debug
            );
        } catch (\Exception $e) {
            error_log('Failed to create error log handler: ' . $e->getMessage());
        }

        return $handlers;
    }

    private static function ensureLogsDirectoryExists(string $logsDir): void
    {
        if (!file_exists($logsDir)) {
            $oldMask = umask(0);
            $created = mkdir($logsDir, 0755, true);
            umask($oldMask);
            
            if (!$created) {
                error_log("Failed to create logs directory: " . $logsDir);
                return;
            }
        }

        // On Windows, we need to explicitly set permissions
        self::setWindowsPermissions($logsDir);
    }

    private static function setWindowsPermissions(string $path): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            try {
                // Get the current user
                $user = get_current_user();
                
                // Use icacls to grant full permissions
                $command = sprintf(
                    'icacls "%s" /grant "%s":(OI)(CI)F /T /Q',
                    $path,
                    $user
                );
                
                // Execute the command
                $output = [];
                $returnVar = 0;
                exec($command, $output, $returnVar);
                
                if ($returnVar !== 0) {
                    error_log("Failed to set Windows permissions: " . implode("\n", $output));
                }
            } catch (\Exception $e) {
                error_log("Error setting Windows permissions: " . $e->getMessage());
            }
        }
    }

    /**
     * Clean up old log files
     */
    public static function cleanOldLogs(): void
    {
        $rootDir = dirname(__DIR__, 2);
        $logsDir = $rootDir . DIRECTORY_SEPARATOR . 'logs';
        
        if (!is_dir($logsDir)) {
            return;
        }

        $files = glob($logsDir . DIRECTORY_SEPARATOR . 'app-*.log');
        if ($files === false) {
            return;
        }

        // Sort files by modification time
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Keep only the most recent MAX_FILES files
        foreach (array_slice($files, self::MAX_FILES) as $file) {
            try {
                if (!@unlink($file)) {
                    error_log("Failed to delete old log file: " . $file);
                }
            } catch (\Exception $e) {
                error_log("Error deleting old log file: " . $e->getMessage());
            }
        }
    }
} 
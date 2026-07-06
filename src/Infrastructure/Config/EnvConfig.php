<?php

declare(strict_types=1);

namespace App\Infrastructure\Config;

class EnvConfig
{
    private array $settings;

    public function __construct()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
        $dotenv->load();

        $this->settings = [
            'db' => [
                'host' => $_ENV['DB_HOST'] ?? '',
                'database' => $_ENV['DB_DATABASE'] ?? '',
                'username' => $_ENV['DB_USERNAME'] ?? '',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
            ],
            'mysql' => [
                'host' => $_ENV['MYSQL_HOST'] ?? '127.0.0.1',
                'database' => $_ENV['MYSQL_DATABASE'] ?? 'db_pq7',
                'username' => $_ENV['MYSQL_USERNAME'] ?? 'admin1',
                'password' => $_ENV['MYSQL_PASSWORD'] ?? '',
            ],
            'ws' => [
                'host' => $_ENV['WS_HOST'] ?? '',
                'port' => (int)($_ENV['WS_PORT'] ?? 443),
                'ssl' => filter_var($_ENV['WS_SSL'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
                'path' => $_ENV['WS_PATH'] ?? '/ws',
                'token' => $_ENV['WS_AUTH_TOKEN'] ?? '',
                'no_server_response_limit' => (int)($_ENV['WS_NOSERVER_RESPONSE_LIMIT'] ?? 10),
            ],
            'remote_api' => [
                'base_url' => $_ENV['REMOTE_API_BASE_URL'] ?? 'https://172.191.199.255',
                'token' => $_ENV['WS_AUTH_TOKEN'] ?? '',
                'ssl_verify' => filter_var($_ENV['REMOTE_API_SSL_VERIFY'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            ],
            'app' => [
                'tick_interval' => (int)($_ENV['APP_TICK_INTERVAL'] ?? 2),
                'log_path' => $_ENV['LOG_PATH'] ?? '/var/log/php-service',
                'api_limits_url' => $_ENV['API_LIMITS_URL'] ?? '',
                'event_api_url' => $_ENV['EVENT_API_URL'] ?? '',
                'event_parameters_url' => $_ENV['EVENT_PARAMETERS_URL'] ?? '',
            ],
            'outbox' => [
                'queue_path' => $_ENV['OUTBOX_QUEUE_PATH'] ?? 'data/queue',
                'max_retries' => (int)($_ENV['OUTBOX_MAX_RETRIES'] ?? 5),
                'ttl_hours' => (int)($_ENV['OUTBOX_TTL_HOURS'] ?? 24),
                'retry_interval' => (int)($_ENV['OUTBOX_RETRY_INTERVAL'] ?? 300),
            ],
        ];
    }

    public function get(string $key)
    {
        $keys = explode('.', $key);
        $value = $this->settings;
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }
        return $value;
    }
}

<?php

namespace App;

class Config
{
    private array $settings;

    public function __construct()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $this->settings = [
            'db' => [
                'host' => $_ENV['DB_HOST'],
                'database' => $_ENV['DB_DATABASE'],
                'username' => $_ENV['DB_USERNAME'],
                'password' => $_ENV['DB_PASSWORD'],
            ],
            'ws' => [
                'host' => $_ENV['WS_HOST'],
                'port' => (int)$_ENV['WS_PORT'],
                'ssl' => filter_var($_ENV['WS_SSL'], FILTER_VALIDATE_BOOLEAN),
                'path' => $_ENV['WS_PATH'],
                'token' => $_ENV['WS_AUTH_TOKEN'] ?? '',
                'no_server_response_limit' => (int)$_ENV['WS_NOSERVER_RESPONSE_LIMIT'] ?? 10,
            ],
            'app' => [
                'tick_interval' => (int)$_ENV['APP_TICK_INTERVAL'],
                'log_path' => $_ENV['LOG_PATH'],
                'api_limits_url' => $_ENV['API_LIMITS_URL'] ?? '',
                'event_api_url' => $_ENV['EVENT_API_URL'] ?? '',
                'event_parameters_url' => $_ENV['EVENT_PARAMETERS_URL'] ?? '',
            ]
        ];
    }

    public function get(string $key)
    {
        // Simple key getter, e.g., 'db.host'
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

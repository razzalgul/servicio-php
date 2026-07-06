<?php

declare(strict_types=1);

date_default_timezone_set('America/Lima');
require_once __DIR__ . '/../vendor/autoload.php';

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FilterHandler;

use App\Domain\Contract\DatabaseReaderInterface;
use App\Domain\Contract\HttpSenderInterface;
use App\Domain\Contract\LocalDatabaseWriterInterface;
use App\Domain\Contract\OutboxRepositoryInterface;
use App\Domain\Contract\TagProviderInterface;
use App\Domain\Contract\WebSocketClientInterface;

use App\Infrastructure\Config\EnvConfig;
use App\Infrastructure\Database\MySqlConnection;
use App\Infrastructure\Database\SqlServerConnection;
use App\Infrastructure\Http\SwooleHttpSender;
use App\Infrastructure\Outbox\FileOutboxRepository;
use App\Infrastructure\Api\TagRepository;
use App\Infrastructure\WebSocket\SwooleWebSocketClient;

use App\Application\Task\RealtimeStreamTask;
use App\Application\Task\HourlyProductionTask;
use App\Application\Task\HourlyMineFeedTask;
use App\Application\Task\HourlyMineralFeedTask;
use App\Application\Task\PlantLoadsTask;
use App\Application\Task\EventSyncTask;
use App\Application\Task\OutboxRetryTask;

try {
    $containerBuilder = new ContainerBuilder();
    
    $containerBuilder->addDefinitions([
        EnvConfig::class => \DI\create(EnvConfig::class),
        
        DatabaseReaderInterface::class => \DI\get(SqlServerConnection::class),
        LocalDatabaseWriterInterface::class => \DI\get(MySqlConnection::class),
        HttpSenderInterface::class => \DI\get(SwooleHttpSender::class),
        OutboxRepositoryInterface::class => \DI\get(FileOutboxRepository::class),
        TagProviderInterface::class => \DI\get(TagRepository::class),
        WebSocketClientInterface::class => \DI\get(SwooleWebSocketClient::class),

        \Psr\Log\LoggerInterface::class => function (ContainerInterface $c) {
            $config = $c->get(EnvConfig::class);
            $logPath = $config->get('app.log_path') ?? 'logs';
            if (!is_dir($logPath)) {
                mkdir($logPath, 0777, true);
            }

            $logger = new Logger('app');

            // 1. Handler para INFO y WARNING en service.log
            $infoHandler = new StreamHandler($logPath . '/service.log', Logger::INFO);
            $logger->pushHandler(new FilterHandler(
                $infoHandler,
                Logger::INFO,
                Logger::WARNING
            ));

            // 2. Handler para ERROR y CRITICAL en error.log
            $errorHandler = new StreamHandler($logPath . '/error.log', Logger::ERROR);
            $logger->pushHandler($errorHandler);

            // 3. Log en la consola (stdout) para debugging
            $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

            return $logger;
        }
    ]);

    $container = $containerBuilder->build();
    $logger = $container->get(\Psr\Log\LoggerInterface::class);

    $logger->info("Iniciando contenedor de dependencias y resolviendo tareas (Clean Architecture)...");

    // Instanciar tarea de tiempo real (stream continuo)
    $realtimeTask = $container->get(RealtimeStreamTask::class);

    // Instanciar tareas programadas (batch / periódicas)
    $scheduledTasks = [
        $container->get(HourlyProductionTask::class),
        $container->get(HourlyMineFeedTask::class),
        $container->get(HourlyMineralFeedTask::class),
        $container->get(PlantLoadsTask::class),
        $container->get(EventSyncTask::class),
        $container->get(OutboxRetryTask::class),
    ];

    $logger->info("Iniciando corutinas de Swoole para todas las tareas del servicio...");

    \Swoole\Coroutine\run(function () use ($realtimeTask, $scheduledTasks, $logger) {
        // 1. Corutina para RealtimeStreamTask (bucle infinito interno)
        \Swoole\Coroutine::create(function () use ($realtimeTask, $logger) {
            try {
                $realtimeTask->run();
            } catch (\Throwable $e) {
                $logger->critical("Fallo fatal en RealtimeStreamTask: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        });

        // 2. Corutinas para las tareas periódicas / batch
        foreach ($scheduledTasks as $task) {
            \Swoole\Coroutine::create(function () use ($task, $logger) {
                $taskName = get_class($task);

                // Si es una tarea horaria (intervalo de 3600s), alinear la primera ejecución al inicio de la próxima hora (:00:00)
                if ($task->getIntervalSeconds() === 3600) {
                    $nextHourTimestamp = strtotime(date('Y-m-d H:00:00', time() + 3600));
                    $secondsUntilNextHour = $nextHourTimestamp - time();
                    if ($secondsUntilNextHour <= 0) {
                        $secondsUntilNextHour = 3600;
                    }
                    $logger->info("Alineando tarea horaria [{$taskName}] al inicio de la próxima hora (esperando {$secondsUntilNextHour}s)...");
                    \Swoole\Coroutine::sleep($secondsUntilNextHour);
                }

                while (true) {
                    try {
                        $logger->debug("Ejecutando tarea programada: {$taskName}");
                        $task->run();
                    } catch (\Throwable $e) {
                        $logger->error("Error ejecutando tarea [{$taskName}]: " . $e->getMessage(), [
                            'trace' => $e->getTraceAsString()
                        ]);
                    }

                    \Swoole\Coroutine::sleep($task->getIntervalSeconds());
                }
            });
        }
    });

} catch (\Throwable $e) {
    fwrite(STDERR, "Error fatal al iniciar el servicio: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

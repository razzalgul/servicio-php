<?php
date_default_timezone_set('America/Lima');
require_once __DIR__ . '/../vendor/autoload.php';

use DI\ContainerBuilder;
use App\EventSyncTask;

use Psr\Container\ContainerInterface;
use App\Config;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FilterHandler;

try {
    $containerBuilder = new ContainerBuilder();
    // ... definiciones y construcción del contenedor...
    $containerBuilder->addDefinitions([
        Psr\Log\LoggerInterface::class => function (ContainerInterface $c) {
            $config = $c->get(Config::class);
            $logPath = $config->get('app.log_path');

            $logger = new Logger('app');

            // 1. Handler para INFO y WARNING en service.log
            $infoHandler = new StreamHandler($logPath . '/service.log', Logger::INFO);
            $logger->pushHandler(new FilterHandler(
                $infoHandler,
                Logger::INFO, // Nivel mínimo
                Logger::WARNING  // Nivel máximo
            ));

            // 2. Handler para ERROR y CRITICAL en error.log
            $errorHandler = new StreamHandler($logPath . '/error.log', Logger::ERROR);
            $logger->pushHandler($errorHandler);

            // 3. (Opcional) Mantener el log en la consola para debugging
            $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

            return $logger;
        }
    ]);
    $container = $containerBuilder->build();

    // Instancia principal de la aplicación
    $application = $container->get(App\Application::class);

    // Instancia de EventSyncTask (ajusta la URL según tu config)
    $dbService = $container->get(App\DataBaseService::class);
    $logger = $container->get(Psr\Log\LoggerInterface::class);
    $config = $container->get(App\Config::class);
    $eventApiUrl = $config->get(App\Config::class);
    $tagRepo = $container->get(App\TagRepository::class);

    $eventTask = new EventSyncTask($dbService, $logger, $config,$tagRepo);

    // Ejecutar ambos procesos en corutinas separadas
    OpenSwoole\Coroutine::run(function () use ($application, $eventTask) {
        // Corutina para el ciclo principal (WebSocket)
        OpenSwoole\Coroutine::create(function () use ($application) {
            $application->run();
        });

        // Corutina para la tarea de eventos periódicos
        OpenSwoole\Coroutine::create(function () use ($eventTask) {
            while (true) {
                $eventTask->run();
                OpenSwoole\Coroutine::sleep($eventTask->getIntervalSeconds());
            }
        });
    });

} catch (Exception $e) {
    fwrite(STDERR, "Error fatal al iniciar el servicio: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

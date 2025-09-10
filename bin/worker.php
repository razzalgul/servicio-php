<?php
date_default_timezone_set('America/Lima');
require __DIR__ . '/../vendor/autoload.php';

use DI\ContainerBuilder;
use App\EventSyncTask;

try {
    $containerBuilder = new ContainerBuilder();
    // ...definiciones y construcción del contenedor...
    $containerBuilder->addDefinitions([
    Psr\Log\LoggerInterface::class => function () {
        $logger = new Monolog\Logger('app');
        $logger->pushHandler(new Monolog\Handler\StreamHandler('php://stdout', Monolog\Logger::DEBUG));
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

<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\Contract\DatabaseReaderInterface;
use App\Infrastructure\Config\EnvConfig;
use Psr\Log\LoggerInterface;
use PDO;

class SqlServerConnection implements DatabaseReaderInterface
{
    private ?\PDO $pdo = null;
    private array $dbConfig;

    public function __construct(EnvConfig $config, private LoggerInterface $logger)
    {
        $this->dbConfig = $config->get('db');
    }

    private function connect(): void
    {
        if ($this->pdo) {
            return;
        }

        try {
            $dsn = "sqlsrv:Server={$this->dbConfig['host']};Database={$this->dbConfig['database']};TrustServerCertificate=true";
            $this->pdo = new \PDO($dsn, $this->dbConfig['username'], $this->dbConfig['password'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::SQLSRV_ATTR_ENCODING => \PDO::SQLSRV_ENCODING_UTF8,
            ]);
            $this->logger->info("Conectado a la base de datos SQL Server.");
        } catch (\PDOException $e) {
            $this->logger->error("Error de conexión a la base de datos SQL Server: " . $e->getMessage());
            $this->pdo = null;
            throw $e;
        }
    }

    public function getLiveValues(): array
    {
        $this->connect();
        if (!$this->pdo) return [];
        $sql = "SELECT TagName, round(Value, 2) as pvalor FROM Runtime.dbo.Live";
        $stmt = $this->pdo->query($sql);

        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[$row['TagName']] = $row['pvalor'];
        }
        return $results;
    }

    public function getHistoricalValues(array $tagNames, string $dateTime): array
    {
        $this->logger->info('Solicitando getHistoricalValues con los parámetros:', [
            'tagNames' => $tagNames,
            'dateTime' => $dateTime
        ]);

        $this->connect();
        if (!$this->pdo) return [];

        if (empty($tagNames)) {
            $this->logger->warning('getHistoricalValues fue llamado con un array de tagNames vacío. Retornando array vacío.');
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($tagNames), '?'));
        $sql = "SELECT TagName, round(Value, 2) as pvalor FROM Runtime.dbo.AnalogHistory WHERE TagName IN ($placeholders) AND DateTime = ?";

        $params = array_merge($tagNames, [$dateTime]);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[$row['TagName']] = $row['pvalor'];
        }

        if (empty($results)) {
            $this->logger->warning('La consulta a AnalogHistory no devolvió resultados para los parámetros proporcionados.', [
                'dateTime' => $dateTime,
                'tags' => $tagNames
            ]);
        }
        return $results;
    }

    public function getHistoricalEvents(array $tagNames): array
    {
        $this->connect();
        if (!$this->pdo) return [];
        
        if (empty($tagNames)) {
            return [];
        }

        $placeholders = implode(',', array_map(fn($name) => $this->pdo->quote($name), $tagNames));
        $sql = "SELECT * FROM Runtime.dbo.v_EventHistory WHERE TagName IN ($placeholders) AND EventStamp BETWEEN DATEADD(WEEK, -1, GETDATE()) AND GETDATE() AND Type = 'OPR' ORDER BY EventStamp DESC";
        $stmt = $this->pdo->query($sql);
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = $row;
        }
        return $results;
    }

    public function getHourlyTagDelta(array $tagNames, string $prevHour): array
    {
        $this->connect();
        if (!$this->pdo || empty($tagNames)) return [];

        $placeholders = implode(',', array_fill(0, count($tagNames), '?'));

        // 1. Obtener valores actuales (usando AnalogLive)
        $sqlNow = "SELECT TagName, Value FROM Runtime.dbo.AnalogLive WHERE TagName IN ($placeholders)";
        $stmtNow = $this->pdo->prepare($sqlNow);
        $stmtNow->execute($tagNames);
        $currentValues = [];
        while ($row = $stmtNow->fetch(\PDO::FETCH_ASSOC)) {
            $currentValues[$row['TagName']] = $row['Value'];
        }

        // 2. Obtener valores de la hora anterior
        $sqlPrev = "SELECT TagName, Value FROM Runtime.dbo.AnalogHistory WHERE TagName IN ($placeholders) AND DateTime = ?";
        $paramsPrev = array_merge($tagNames, [$prevHour]);
        $stmtPrev = $this->pdo->prepare($sqlPrev);
        $stmtPrev->execute($paramsPrev);
        $previousValues = [];
        while ($row = $stmtPrev->fetch(\PDO::FETCH_ASSOC)) {
            $previousValues[$row['TagName']] = $row['Value'];
        }

        // 3. Calcular diferencias
        $deltas = [];
        foreach ($tagNames as $tag) {
            if (isset($currentValues[$tag]) && isset($previousValues[$tag])) {
                $deltas[$tag] = (float)($currentValues[$tag] - $previousValues[$tag]);
            } else {
                $this->logger->warning("Advertencia: No se encontró valor actual o histórico para {$tag} en getHourlyTagDelta (prevHour: {$prevHour}). Retornando 0.");
                $deltas[$tag] = 0.0;
            }
        }

        return $deltas;
    }

    public function getAverageValues(array $tagNames, int $minutesBack): array
    {
        $this->connect();
        if (!$this->pdo || empty($tagNames)) return [];

        $placeholders = implode(',', array_fill(0, count($tagNames), '?'));
        $sql = "SELECT TagName, round(AVG(Value),2) as pvalor FROM Runtime.dbo.History WHERE TagName IN ($placeholders) AND DateTime BETWEEN DateAdd(mi,-{$minutesBack},GetDate()) AND GetDate() GROUP BY TagName";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($tagNames);

        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[$row['TagName']] = (float)$row['pvalor'];
        }
        return $results;
    }
}

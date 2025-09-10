<?php

namespace App;
use Psr\Log\LoggerInterface;
use PDO;

class DataBaseService
{
    private ?\PDO $pdo = null;
    private array $dbConfig;

    public function __construct(Config $config, private LoggerInterface $logger)
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
            $this->logger->info("Conectado a la base de datos.");
        } catch (\PDOException $e) {
            $this->logger->error("Error de conexión a la base de datos: " . $e->getMessage());
            $this->pdo = null;
            throw $e; // Re-throw to be handled by the application loop
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
        $this->connect();
        if (!$this->pdo) return [];

        $placeholders = implode(',', array_fill(0, count($tagNames), '?'));
        $sql = "SELECT TagName, round(Value, 2) as pvalor FROM Runtime.dbo.AnalogHistory WHERE TagName IN ($placeholders) AND DateTime = ?";

        $params = array_merge($tagNames, [$dateTime]);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[$row['TagName']] = $row['pvalor'];
        }
        return $results;
    }
    public function getHistoricalEvents(array $tagNames): array
    {
        $this->connect();
        if (!$this->pdo) return [];
        //$placeholders = implode(',', array_fill(0, count($tagNames), '?'));
        $placeholders = implode(',', array_map(fn($name) => $this->pdo->quote($name), $tagNames));
        $sql = "SELECT * FROM Runtime.dbo.v_EventHistory WHERE TagName IN ($placeholders) AND EventStamp BETWEEN DATEADD(WEEK, -1, GETDATE()) AND GETDATE() AND Type = 'OPR' ORDER BY EventStamp DESC";
       $stmt = $this->pdo->query($sql);
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = $row; // Agrega cada fila completa al array
        }
        return $results;
    }
}


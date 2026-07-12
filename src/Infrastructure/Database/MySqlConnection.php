<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\Contract\LocalDatabaseWriterInterface;
use App\Infrastructure\Config\EnvConfig;
use Psr\Log\LoggerInterface;
use PDO;

class MySqlConnection implements LocalDatabaseWriterInterface
{
    private ?\PDO $pdo = null;
    private array $dbConfig;

    public function __construct(
        EnvConfig $config,
        private LoggerInterface $logger
    ) {
        $this->dbConfig = $config->get('mysql');
    }

    private function connect(): void
    {
        if ($this->pdo) return;
        $dsn = "mysql:host={$this->dbConfig['host']};dbname={$this->dbConfig['database']};charset=utf8mb4";
        $this->pdo = new \PDO($dsn, $this->dbConfig['username'], $this->dbConfig['password'], [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);
        $this->logger->info("Conectado a la base de datos MySQL local.");
    }

    public function insertPlantLoadC2(array $data): void
    {
        $this->connect();
        if (!$this->pdo) return;

        $sql = "INSERT INTO datosc2 (
            fecha, hora, linea, modo, frec, amp, sol, flujo, ton, ton_densimetro, ton_filtro, rel_densimetro, last_user_update
        ) VALUES (
            :fecha, :hora, :linea, :modo, :frec, :amp, :sol, :flujo, :ton, :ton_densimetro, :ton_filtro, :rel_densimetro, :last_user_update
        )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':fecha' => $data['fecha'] ?? null,
            ':hora' => $data['hora'] ?? null,
            ':linea' => $data['linea'] ?? null,
            ':modo' => $data['modo'] ?? null,
            ':frec' => $data['frec'] ?? null,
            ':amp' => $data['amp'] ?? null,
            ':sol' => $data['sol'] ?? null,
            ':flujo' => $data['flujo'] ?? null,
            ':ton' => $data['ton'] ?? null,
            ':ton_densimetro' => $data['ton_densimetro'] ?? null,
            ':ton_filtro' => $data['ton_filtro'] ?? null,
            ':rel_densimetro' => $data['rel_densimetro'] ?? null,
            ':last_user_update' => $data['last_user_update'] ?? null,
        ]);
    }

    public function insertPlantLoadReport(array $data): void
    {
        $this->connect();
        if (!$this->pdo) return;

        $sql = "INSERT INTO datos_repdia (
            fecha, hora, WIT0304_IO_Value, p5841_CB120_WIT, p5841_CB120_M103_Alarm_M_II,
            p5730_CB0001_Current1M_IO_Value, p5730_CB0003_status_Iavg, flujo_1001, flujo_2001,
            FIT0963_IO_Value, FIT0962_IO_Value, relmodol1, sol_rel1, FIT1801_IO_Value, ton_rel1,
            amp_rel1, frec_rel1, relmodol2, sol_rel2, FIT2801_IO_Value, ton_rel2, amp_rel2,
            frec_rel2, p5780_MX1001_status_Iavg, p5780_MX2001_status_Iavg, LIT1803_IO_Value,
            LIT2803_IO_Value, frecba_shoux, frecbb_shoux, frecbc_shoux, Shouxin_601_PIT_018_Value,
            Shouxin_601_PIT_017_Value, Shouxin_601_PIT_016_Value, relmallal1, relmallal2,
            DIT1801_IO_alue, WCT5841, WCT0303, WIT0303_IO_Value, BM1001_Pow, BM1002_Pow,
            BM2001_Pow, BM2002_Pow, N1920_FIT0001_IO_Value
        ) VALUES (
            :fecha, :hora, :WIT0304_IO_Value, :p5841_CB120_WIT, :p5841_CB120_M103_Alarm_M_II,
            :p5730_CB0001_Current1M_IO_Value, :p5730_CB0003_status_Iavg, :flujo_1001, :flujo_2001,
            :FIT0963_IO_Value, :FIT0962_IO_Value, :relmodol1, :sol_rel1, :FIT1801_IO_Value, :ton_rel1,
            :amp_rel1, :frec_rel1, :relmodol2, :sol_rel2, :FIT2801_IO_Value, :ton_rel2, :amp_rel2,
            :frec_rel2, :p5780_MX1001_status_Iavg, :p5780_MX2001_status_Iavg, :LIT1803_IO_Value,
            :LIT2803_IO_Value, :frecba_shoux, :frecbb_shoux, :frecbc_shoux, :Shouxin_601_PIT_018_Value,
            :Shouxin_601_PIT_017_Value, :Shouxin_601_PIT_016_Value, :relmallal1, :relmallal2,
            :DIT1801_IO_alue, :WCT5841, :WCT0303, :WIT0303_IO_Value, :BM1001_Pow, :BM1002_Pow,
            :BM2001_Pow, :BM2002_Pow, :N1920_FIT0001_IO_Value
        )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':fecha' => $data['fecha'] ?? null,
            ':hora' => $data['hora'] ?? null,
            ':WIT0304_IO_Value' => $data['WIT0304_IO_Value'] ?? null,
            ':p5841_CB120_WIT' => $data['p5841_CB120_WIT'] ?? null,
            ':p5841_CB120_M103_Alarm_M_II' => $data['p5841_CB120_M103_Alarm_M_II'] ?? null,
            ':p5730_CB0001_Current1M_IO_Value' => $data['p5730_CB0001_Current1M_IO_Value'] ?? null,
            ':p5730_CB0003_status_Iavg' => $data['p5730_CB0003_status_Iavg'] ?? null,
            ':flujo_1001' => $data['flujo_1001'] ?? null,
            ':flujo_2001' => $data['flujo_2001'] ?? null,
            ':FIT0963_IO_Value' => $data['FIT0963_IO_Value'] ?? null,
            ':FIT0962_IO_Value' => $data['FIT0962_IO_Value'] ?? null,
            ':relmodol1' => $data['relmodol1'] ?? null,
            ':sol_rel1' => $data['sol_rel1'] ?? null,
            ':FIT1801_IO_Value' => $data['FIT1801_IO_Value'] ?? null,
            ':ton_rel1' => $data['ton_rel1'] ?? null,
            ':amp_rel1' => $data['amp_rel1'] ?? null,
            ':frec_rel1' => $data['frec_rel1'] ?? null,
            ':relmodol2' => $data['relmodol2'] ?? null,
            ':sol_rel2' => $data['sol_rel2'] ?? null,
            ':FIT2801_IO_Value' => $data['FIT2801_IO_Value'] ?? null,
            ':ton_rel2' => $data['ton_rel2'] ?? null,
            ':amp_rel2' => $data['amp_rel2'] ?? null,
            ':frec_rel2' => $data['frec_rel2'] ?? null,
            ':p5780_MX1001_status_Iavg' => $data['p5780_MX1001_status_Iavg'] ?? null,
            ':p5780_MX2001_status_Iavg' => $data['p5780_MX2001_status_Iavg'] ?? null,
            ':LIT1803_IO_Value' => $data['LIT1803_IO_Value'] ?? null,
            ':LIT2803_IO_Value' => $data['LIT2803_IO_Value'] ?? null,
            ':frecba_shoux' => $data['frecba_shoux'] ?? null,
            ':frecbb_shoux' => $data['frecbb_shoux'] ?? null,
            ':frecbc_shoux' => $data['frecbc_shoux'] ?? null,
            ':Shouxin_601_PIT_018_Value' => $data['Shouxin_601_PIT_018_Value'] ?? null,
            ':Shouxin_601_PIT_017_Value' => $data['Shouxin_601_PIT_017_Value'] ?? null,
            ':Shouxin_601_PIT_016_Value' => $data['Shouxin_601_PIT_016_Value'] ?? null,
            ':relmallal1' => $data['relmallal1'] ?? null,
            ':relmallal2' => $data['relmallal2'] ?? null,
            ':DIT1801_IO_alue' => $data['DIT1801_IO_alue'] ?? null,
            ':WCT5841' => $data['WCT5841'] ?? null,
            ':WCT0303' => $data['WCT0303'] ?? null,
            ':WIT0303_IO_Value' => $data['WIT0303_IO_Value'] ?? null,
            ':BM1001_Pow' => $data['BM1001_Pow'] ?? null,
            ':BM1002_Pow' => $data['BM1002_Pow'] ?? null,
            ':BM2001_Pow' => $data['BM2001_Pow'] ?? null,
            ':BM2002_Pow' => $data['BM2002_Pow'] ?? null,
            ':N1920_FIT0001_IO_Value' => $data['N1920_FIT0001_IO_Value'] ?? null,
        ]);
    }
}

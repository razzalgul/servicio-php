<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Domain\Contract\DatabaseReaderInterface;
use App\Domain\Contract\HttpSenderInterface;
use App\Domain\Contract\LocalDatabaseWriterInterface;
use App\Domain\Contract\OutboxRepositoryInterface;
use App\Domain\Contract\ScheduledTaskInterface;
use App\Domain\DTO\OutboxMessage;
use Psr\Log\LoggerInterface;
use Throwable;

class PlantLoadsTask implements ScheduledTaskInterface
{
    private int $intervalSeconds = 600; // 10 minutos

    private array $tags = [
        '5730_PU0001_rev1.frecuencyRpm',
        '5730_PU0002_rev1.frecuencyRpm',
        '5730_PU0003_rev1.frecuencyRpm',
        '5730_PU0004_rev1.frecuencyRpm',
        '5730_PU0001_rev1.status.TotalCurrent',
        '5730_PU0002_rev1.status.TotalCurrent',
        '5730_PU0003_rev1.status.TotalCurrent',
        '5730_PU0004_rev1.status.TotalCurrent',
        'FIT1301.IO.Value', 'FIT2301.IO.Value',
        'WIT0303.IO.Value',
        'WIT0304.IO.Value',
        '5841_CB120.WIT',
        'WIT841_002.IO.Value',
        '5841_CB120_M103_Alarm.Current',
        '5730_CB0001_Current1M.IO.Value',
        '5730_CB0003.status.Iavg',
        'N1920_FIT0001.IO.Value',
        'FIT0962.IO.Value',
        'FIT0963.IO.Value',
        'FIT1801.IO.Value',
        'FIT1802.IO.Value',
        'FIT2801.IO.Value',
        'FIT2802.IO.Value',
        '5780_MX1001.status.Iavg',
        '5780_MX2001.status.Iavg',
        'LIT1803.IO.Value',
        'LIT2803.IO.Value',
        'Shouxin_601_PU01A.Frecuency',
        'Shouxin_601_PU02A.Frecuency',
        'Shouxin_601_PU01B.Frecuency',
        'Shouxin_601_PU02B.Frecuency',
        'Shouxin_601_PU01C.Frecuency',
        'Shouxin_601_PU02C.Frecuency',
        'Shouxin_601_PIT_016.Value',
        'Shouxin_601_PIT_017.Value',
        'Shouxin_601_PIT_018.Value',
        'DIT1801.IO.Value',
        'WI1802.IO.Value',
        'DIT1802.FMIT',
        'DIT2801.FMIT',
        'DIT2802.FMIT',
        'DIT1802.SIT',
        'DFI1801.IO.Value',
        'DIT2801.SIT',
        'DIT2802.SIT',
        '5780_PU1001_rev1.Status.SpeedPv',
        '5780_PU1001_rev1.Status.Current',
        '5780_PU1002_rev1.Status.SpeedPv',
        '5780_PU1002_rev1.Status.Current',
        '5780_PU2001_rev1.Status.SpeedPv',
        '5780_PU2001_rev1.Status.Current',
        '5780_PU2002_rev1.Status.SpeedPv',
        '5780_PU2002_rev1.Status.Current',
        'WIT1741.IO.Value',
        'WIT2741.IO.Value',
        'DIT1001_TV_Densidad.IO.Value',
        'DIT1001_PV.IO.Value',
        'DIT2001_TV_Densidad.IO.Value',
        'DIT2001_PV.IO.Value',
        'WCT5841WE120.Value',
        'WCT0303.Value',
        '5740_BM1001.M101_POW', '5740_BM1002.M101_POW', '5740_BM2001.M101_POW', '5740_BM2002.M101_POW'
    ];

    public function __construct(
        private DatabaseReaderInterface $dbReader,
        private LocalDatabaseWriterInterface $localDbWriter,
        private HttpSenderInterface $httpSender,
        private OutboxRepositoryInterface $outbox,
        private LoggerInterface $logger
    ) {}

    public function getIntervalSeconds(): int
    {
        return $this->intervalSeconds;
    }

    public function run(): void
    {
        $this->logger->info("Iniciando tarea PlantLoadsTask (cargas_rep_v2)...");

        $mfecha = date('Y-m-d H:i:s');
        $mhora = date('H:i:s');

        $avg = $this->dbReader->getAverageValues($this->tags, 10);

        // Lógica Línea 1
        $frec_z_l1 = 0;
        $amp_z_l1 = 0;
        $flow_z_l1 = null;
        $sol_l1 = null;
        $ton_l1 = null;
        $modo_l1 = 3;

        $frec_pu1 = (float)($avg["5730_PU0001_rev1.frecuencyRpm"] ?? 0);
        $frec_pu2 = (float)($avg["5730_PU0002_rev1.frecuencyRpm"] ?? 0);
        $fit1301 = (float)($avg["FIT1301.IO.Value"] ?? 0);

        if ($frec_pu1 > 1200 && $frec_pu2 < 750) {
            $frec_z_l1 = (int)$frec_pu1;
            $amp_z_l1 = (float)($avg["5730_PU0001_rev1.status.TotalCurrent"] ?? 0);
            $flow_z_l1 = (int)$fit1301;
            $sol_l1 = (float)($avg["DIT1001_TV_Densidad.IO.Value"] ?? 0);
            $ton_l1 = (float)($avg["DIT1001_PV.IO.Value"] ?? 0);
            $modo_l1 = 1;
        } elseif ($frec_pu2 > 1200 && $fit1301 > 700 && $frec_pu1 < 1000) {
            $frec_z_l1 = (int)$frec_pu2;
            $amp_z_l1 = (float)($avg["5730_PU0002_rev1.status.TotalCurrent"] ?? 0);
            $flow_z_l1 = (int)$fit1301;
            $sol_l1 = (float)($avg["DIT1001_TV_Densidad.IO.Value"] ?? 0);
            $ton_l1 = (float)($avg["DIT1001_PV.IO.Value"] ?? 0);
            $modo_l1 = 1;
        } elseif ($frec_pu2 > 360) {
            $frec_z_l1 = (int)$frec_pu2;
            $amp_z_l1 = (float)($avg["5730_PU0002_rev1.status.TotalCurrent"] ?? 0);
            $modo_l1 = 2;
        } else {
            $frec_z_l1 = (int)$frec_pu2;
            $amp_z_l1 = (float)($avg["5730_PU0002_rev1.status.TotalCurrent"] ?? 0);
            $modo_l1 = 3;
        }

        // Lógica Línea 2
        $frec_z_l2 = 0;
        $amp_z_l2 = 0;
        $flow_z_l2 = null;
        $sol_l2 = null;
        $ton_l2 = null;
        $modo_l2 = 3;

        $frec_pu3 = (float)($avg["5730_PU0003_rev1.frecuencyRpm"] ?? 0);
        $frec_pu4 = (float)($avg["5730_PU0004_rev1.frecuencyRpm"] ?? 0);
        $fit2301 = (float)($avg["FIT2301.IO.Value"] ?? 0);

        if ($frec_pu3 > 1200 && $frec_pu4 < 750) {
            $frec_z_l2 = (int)$frec_pu3;
            $amp_z_l2 = (float)($avg["5730_PU0003_rev1.status.TotalCurrent"] ?? 0);
            $flow_z_l2 = (int)$fit2301;
            $sol_l2 = (float)($avg["DIT2001_TV_Densidad.IO.Value"] ?? 0);
            $ton_l2 = (float)($avg["DIT2001_PV.IO.Value"] ?? 0);
            $modo_l2 = 1;
        } elseif ($frec_pu4 > 1000 && $fit2301 > 700 && $frec_pu3 < 1000) {
            $frec_z_l2 = (int)$frec_pu4;
            $amp_z_l2 = (float)($avg["5730_PU0004_rev1.status.TotalCurrent"] ?? 0);
            $flow_z_l2 = (int)$fit2301;
            $sol_l2 = (float)($avg["DIT2001_TV_Densidad.IO.Value"] ?? 0);
            $ton_l2 = (float)($avg["DIT2001_PV.IO.Value"] ?? 0);
            $modo_l2 = 1;
        } elseif ($frec_pu4 > 360) {
            $frec_z_l2 = (int)$frec_pu4;
            $amp_z_l2 = (float)($avg["5730_PU0004_rev1.status.TotalCurrent"] ?? 0);
            $modo_l2 = 2;
        } else {
            $frec_z_l2 = (int)$frec_pu4;
            $amp_z_l2 = (float)($avg["5730_PU0004_rev1.status.TotalCurrent"] ?? 0);
            $modo_l2 = 3;
        }

        // Relaves L1
        $relpu1001_amp = (float)($avg["5780_PU1001_rev1.Status.Current"] ?? 0);
        $relpu1001_hz  = (float)($avg["5780_PU1001_rev1.Status.SpeedPv"] ?? 0);
        $relpu1002_amp = (float)($avg["5780_PU1002_rev1.Status.Current"] ?? 0);
        $relpu1002_hz  = (float)($avg["5780_PU1002_rev1.Status.SpeedPv"] ?? 0);

        $relmodol1 = 3;
        $amp_rel1 = 0;
        $frec_rel1 = 0;
        $flow_rel1 = 0;
        $ton_rel1 = 0;
        $sol_rel1 = 0;

        if ($relpu1002_amp > 5 && $relpu1002_hz > 6) {
            $relmodol1 = 1;
            $amp_rel1 = (int)$relpu1002_amp;
            $frec_rel1 = (int)$relpu1002_hz;
            $flow_rel1 = (int)($avg["FIT1801.IO.Value"] ?? 0);
            $ton_rel1 = (float)($avg["WI1802.IO.Value"] ?? 0);
            $sol_rel1 = (float)($avg["DFI1801.IO.Value"] ?? 0);
        } elseif ($relpu1001_amp > 5 && $relpu1001_hz > 6) {
            $relmodol1 = 2;
            $amp_rel1 = (int)$relpu1001_amp;
            $frec_rel1 = (int)$relpu1001_hz;
            $flow_rel1 = (int)($avg["FIT1802.IO.Value"] ?? 0);
            $ton_rel1 = (float)($avg["DIT1802.FMIT"] ?? 0);
            $sol_rel1 = (float)($avg["DIT1802.SIT"] ?? 0);
        }

        // Relaves L2
        $relpu2001_amp = (float)($avg["5780_PU2001_rev1.Status.Current"] ?? 0);
        $relpu2001_hz  = (float)($avg["5780_PU2001_rev1.Status.SpeedPv"] ?? 0);
        $relpu2002_amp = (float)($avg["5780_PU2002_rev1.Status.Current"] ?? 0);
        $relpu2002_hz  = (float)($avg["5780_PU2002_rev1.Status.SpeedPv"] ?? 0);

        $relmodol2 = 3;
        $amp_rel2 = 0;
        $frec_rel2 = 0;
        $flow_rel2 = 0;
        $ton_rel2 = 0;
        $sol_rel2 = 0;

        if ($relpu2001_amp > 5 && $relpu2001_hz > 6) {
            $relmodol2 = 1;
            $amp_rel2 = (int)$relpu2001_amp;
            $frec_rel2 = (int)$relpu2001_hz;
            $flow_rel2 = (int)($avg["FIT2801.IO.Value"] ?? 0);
            $ton_rel2 = (float)($avg["DIT2801.FMIT"] ?? 0);
            $sol_rel2 = (float)($avg["DIT2801.SIT"] ?? 0);
        } elseif ($relpu2002_amp > 5 && $relpu2002_hz > 6) {
            $relmodol2 = 2;
            $amp_rel2 = (int)$relpu2002_amp;
            $frec_rel2 = (int)$relpu2002_hz;
            $flow_rel2 = (int)($avg["FIT2802.IO.Value"] ?? 0);
            $ton_rel2 = (float)($avg["DIT2802.FMIT"] ?? 0);
            $sol_rel2 = (float)($avg["DIT2802.SIT"] ?? 0);
        }

        $frecba_shoux = ((float)($avg["Shouxin_601_PU01A.Frecuency"] ?? 0) + (float)($avg["Shouxin_601_PU02A.Frecuency"] ?? 0)) / 2;
        $frecbb_shoux = ((float)($avg["Shouxin_601_PU01B.Frecuency"] ?? 0) + (float)($avg["Shouxin_601_PU02B.Frecuency"] ?? 0)) / 2;
        $frecbc_shoux = ((float)($avg["Shouxin_601_PU01C.Frecuency"] ?? 0) + (float)($avg["Shouxin_601_PU02C.Frecuency"] ?? 0)) / 2;

        // 1. Insertar en MySQL local: datosc2 (2 registros)
        $row_l1 = [
            'fecha' => $mfecha,
            'hora' => $mhora,
            'linea' => 1,
            'modo' => $modo_l1,
            'frec' => $frec_z_l1,
            'amp' => $amp_z_l1,
            'sol' => $sol_l1,
            'flujo' => $flow_z_l1,
            'ton' => null,
            'ton_densimetro' => $ton_l1,
            'ton_filtro' => null,
            'rel_densimetro' => null,
            'last_user_update' => null
        ];

        $row_l2 = [
            'fecha' => $mfecha,
            'hora' => $mhora,
            'linea' => 2,
            'modo' => $modo_l2,
            'frec' => $frec_z_l2,
            'amp' => $amp_z_l2,
            'sol' => $sol_l2,
            'flujo' => $flow_z_l2,
            'ton' => null,
            'ton_densimetro' => $ton_l2,
            'ton_filtro' => null,
            'rel_densimetro' => null,
            'last_user_update' => null
        ];

        try {
            $this->localDbWriter->insertPlantLoadC2($row_l1);
            $this->localDbWriter->insertPlantLoadC2($row_l2);
            $this->logger->info("PlantLoadsTask: Registros insertados en datosc2.");
        } catch (Throwable $e) {
            $this->logger->error("PlantLoadsTask: Error insertando en datosc2: " . $e->getMessage());
            $this->outbox->enqueue(new OutboxMessage(
                id: time() . '_datosc2_' . substr(md5(uniqid('', true)), 0, 6),
                taskType: 'datosc2_insert',
                destination: 'local_mysql',
                endpoint: 'datosc2',
                httpMethod: 'INSERT',
                payload: json_encode([$row_l1, $row_l2]),
                createdAt: date('c')
            ));
        }

        // 2. Insertar en MySQL local: datos_repdia (1 registro)
        $row_repdia = [
            'fecha' => $mfecha,
            'hora' => $mhora,
            'WIT0304_IO_Value' => (int)($avg["WIT0304.IO.Value"] ?? 0),
            'p5841_CB120_WIT' => (int)($avg["WIT841_002.IO.Value"] ?? 0),
            'p5841_CB120_M103_Alarm_M_II' => (int)($avg["5841_CB120_M103_Alarm.Current"] ?? 0),
            'p5730_CB0001_Current1M_IO_Value' => (int)($avg["5730_CB0001_Current1M.IO.Value"] ?? 0),
            'p5730_CB0003_status_Iavg' => (int)($avg["5730_CB0003.status.Iavg"] ?? 0),
            'flujo_1001' => (int)($avg["WIT1741.IO.Value"] ?? 0),
            'flujo_2001' => (int)($avg["WIT2741.IO.Value"] ?? 0),
            'FIT0963_IO_Value' => (int)($avg["FIT0963.IO.Value"] ?? 0),
            'FIT0962_IO_Value' => (int)($avg["FIT0962.IO.Value"] ?? 0),
            'relmodol1' => $relmodol1,
            'sol_rel1' => $sol_rel1,
            'FIT1801_IO_Value' => $flow_rel1,
            'ton_rel1' => $ton_rel1,
            'amp_rel1' => $amp_rel1,
            'frec_rel1' => $frec_rel1,
            'relmodol2' => $relmodol2,
            'sol_rel2' => $sol_rel2,
            'FIT2801_IO_Value' => $flow_rel2,
            'ton_rel2' => $ton_rel2,
            'amp_rel2' => $amp_rel2,
            'frec_rel2' => $frec_rel2,
            'p5780_MX1001_status_Iavg' => (int)($avg["5780_MX1001.status.Iavg"] ?? 0),
            'p5780_MX2001_status_Iavg' => (int)($avg["5780_MX2001.status.Iavg"] ?? 0),
            'LIT1803_IO_Value' => (float)($avg["LIT1803.IO.Value"] ?? 0),
            'LIT2803_IO_Value' => (float)($avg["LIT2803.IO.Value"] ?? 0),
            'frecba_shoux' => $frecba_shoux,
            'frecbb_shoux' => $frecbb_shoux,
            'frecbc_shoux' => $frecbc_shoux,
            'Shouxin_601_PIT_018_Value' => (float)($avg["Shouxin_601_PIT_018.Value"] ?? 0),
            'Shouxin_601_PIT_017_Value' => (float)($avg["Shouxin_601_PIT_017.Value"] ?? 0),
            'Shouxin_601_PIT_016_Value' => (float)($avg["Shouxin_601_PIT_016.Value"] ?? 0),
            'relmallal1' => null,
            'relmallal2' => null,
            'DIT1801_IO_alue' => (int)($avg["DIT1801.IO.Value"] ?? 0),
            'WCT5841' => (int)($avg["WCT5841WE120.Value"] ?? 0),
            'WCT0303' => (int)($avg["WCT0303.Value"] ?? 0),
            'WIT0303_IO_Value' => (int)($avg["WIT0303.IO.Value"] ?? 0),
            'BM1001_Pow' => (int)($avg["5740_BM1001.M101_POW"] ?? 0),
            'BM1002_Pow' => (int)($avg["5740_BM1002.M101_POW"] ?? 0),
            'BM2001_Pow' => (int)($avg["5740_BM2001.M101_POW"] ?? 0),
            'BM2002_Pow' => (int)($avg["5740_BM2002.M101_POW"] ?? 0),
            'N1920_FIT0001_IO_Value' => (int)($avg["N1920_FIT0001.IO.Value"] ?? 0),
        ];

        try {
            $this->localDbWriter->insertPlantLoadReport($row_repdia);
            $this->logger->info("PlantLoadsTask: Registro insertado en datos_repdia.");
        } catch (Throwable $e) {
            $this->logger->error("PlantLoadsTask: Error insertando en datos_repdia: " . $e->getMessage());
            $this->outbox->enqueue(new OutboxMessage(
                id: time() . '_datos_repdia_' . substr(md5(uniqid('', true)), 0, 6),
                taskType: 'datos_repdia_insert',
                destination: 'local_mysql',
                endpoint: 'datos_repdia',
                httpMethod: 'INSERT',
                payload: json_encode($row_repdia),
                createdAt: date('c')
            ));
        }

        // 3. Enviar a servidor remoto (/api/production)
        $postData = [
            [
                'linea' => 1,
                'amp' => $amp_z_l1,
                'frec' => $frec_z_l1,
                'modo' => $modo_l1,
                'flujo' => $flow_z_l1,
                'sol' => $sol_l1,
                'ton_densimetro' => $ton_l1,
                'fecha' => $mfecha,
                'hora' => $mhora
            ],
            [
                'linea' => 2,
                'amp' => $amp_z_l2,
                'frec' => $frec_z_l2,
                'modo' => $modo_l2,
                'flujo' => $flow_z_l2,
                'sol' => $sol_l2,
                'ton_densimetro' => $ton_l2,
                'fecha' => $mfecha,
                'hora' => $mhora
            ]
        ];

        $endpoint = '/api/production';
        $success = $this->httpSender->sendWithFallback('plant_loads', $endpoint, $postData);

        if ($success) {
            $this->logger->info("PlantLoadsTask: Datos enviados correctamente a {$endpoint}.");
        } else {
            $this->logger->warning("PlantLoadsTask: Envío remoto fallido, encolado en outbox.");
        }
    }
}

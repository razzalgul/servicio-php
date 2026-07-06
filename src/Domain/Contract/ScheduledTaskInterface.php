<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface ScheduledTaskInterface
{
    public function getIntervalSeconds(): int;

    public function run(): void;
}

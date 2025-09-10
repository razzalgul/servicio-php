<?php
namespace App;

interface ScheduledTaskInterface
{
    public function getIntervalSeconds(): int;
    public function run(): void;
}
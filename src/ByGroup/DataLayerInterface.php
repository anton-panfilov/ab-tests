<?php

namespace AP\ABTest\IntIdBased\ByGroup;

use AP\ABTest\IntIdBased\ByGroup\DataModel\GroupsCollection;
use AP\ABTest\IntIdBased\ByGroup\DataModel\Period;
use AP\ABTest\IntIdBased\ByGroup\DataModel\PeriodsCollection;

interface DataLayerInterface
{
    public function transactionStart(): void;

    public function transactionCommit(): void;

    public function transactionRollback(): void;

    public function insertPeriod(int $group, mixed $settings, int $startTs, ?int $endTs): int;

    public function getPeriod(int $id): ?Period;

    public function searchPeriod(int $group, int $timestamp): ?Period;

    public function removePeriod(int $id): void;

    public function getPeriods(int $group): PeriodsCollection;

    public function getGroups(): GroupsCollection;
}

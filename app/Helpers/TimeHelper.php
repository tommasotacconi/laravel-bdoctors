<?php

namespace App\Helpers;

use Carbon\CarbonImmutable;

class TimeHelper
{
    protected static function getAppFixedTime(): ?CarbonImmutable
    {
        $time = config('app.fixed_time');

        return $time ? CarbonImmutable::parse($time, config('app.timezone')) : null;
    }

    private static function getAppYear(): int
    {
        return static::getAppFixedTime()?->year ?? CarbonImmutable::now()->year;
    }

    public static function computeAppTime(bool $useAppFixedTime = true): CarbonImmutable
    {
        $base = $useAppFixedTime
            ? static::getAppFixedTime()
            : CarbonImmutable::now(config('app.timezone'));

        return ($base ?? CarbonImmutable::now(config('app.timezone')))->settings([
            'yearOverflow' => false
        ])->year(static::getAppYear());
    }

    public static function normalizeToAppYear($time): CarbonImmutable
    {
        return CarbonImmutable::parse($time, config('app.timezone'))
            ->settings(['yearOverflow' => false])
            ->year(static::getAppYear());
    }
}

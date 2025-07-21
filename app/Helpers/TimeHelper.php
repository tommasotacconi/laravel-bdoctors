<?php

namespace App\Helpers;

use Carbon\CarbonImmutable;

class TimeHelper
{
    /**
     * Compute current time and date and use it as time instant in the app year
     *
     * @return CarbonImmutable
     */
    public static function computeAppTime(bool $useAppFixedTime = true)
    {
        if ($useAppFixedTime) {
            $appFixedTime = CarbonImmutable::parse(env('APP_TIME'));

            return $appFixedTime;
        }

        $currentTime = CarbonImmutable::now();
        $yearsDiff = $currentTime->year - CarbonImmutable::parse(env('APP_TIME'))->year;
        $currentTimeShifted = $currentTime->subYears($yearsDiff);

        return $currentTimeShifted;
    }
}

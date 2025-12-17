<?php

namespace App\Models;

use App\Helpers\TimeHelper;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProfileSponsorship extends Pivot
{
    protected $table = 'profile_sponsorship';

    protected $dates = [
        'start_date',
        'end_date'
    ];

    public function scopeActive($query) {
        $computedTime = TimeHelper::computeAppTime(false);
        $tb = $this->getTable();

        return $query
            ->where("$tb.start_date", '<=', $computedTime)
            ->where("$tb.end_date", '>=', $computedTime);
    }

    public function sponsorship() {
        return $this->belongsTo(Sponsorship::class);
    }
}

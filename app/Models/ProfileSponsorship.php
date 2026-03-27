<?php

namespace App\Models;

use App\Helpers\TimeHelper;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProfileSponsorship extends Pivot
{
    protected $table = 'profile_sponsorship';

    public function scopeActive($query, $time = null)
    {
        $computedTime = $time ?? TimeHelper::computeAppTime(false);

        $query->where("{$this->table}.start_date", '<=', $computedTime)
            ->where("{$this->table}.end_date", '>=', $computedTime);
        \Log::info('SQL', [
            'query' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);
    }

    public function sponsorship()
    {
        return $this->belongsTo(Sponsorship::class);
    }
}

<?php

namespace App\Actions\Review;

use App\Models\Profile;
use App\Models\Specialization;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class FilterReview
{
    public function __construct()
    {
    }

    public function handle(string $specialization, ?int $rating, ?int $reviewsNum): Collection
    {
        // Check specialization existance before executing complete query
        $spec = Specialization::select('name')->where('name', '=', $specialization);
        if ($spec->get()->isEmpty()) throw new Exception('Specialization not found');

        $query = Profile::select('profiles.*', 'specializations.name as specialization_name')
            ->join('users', 'profiles.user_id', '=', 'users.id')
            ->join('specialization_user', 'users.id', '=', 'specialization_user.user_id')
            ->join('specializations', 'specialization_user.specialization_id', '=', 'specializations.id')
            ->leftJoin('reviews', 'profiles.id', '=', 'reviews.profile_id')
            ->whereRaw('LOWER(`specializations`.`name`) = ?', [$specialization])
            ->selectRaw('ROUND(AVG(reviews.vote), 0) AS avg_vote')->selectRaw('COALESCE(COUNT(reviews.id), 0) AS total_reviews')
            ->groupBy('profiles.id', 'specializations.id')->with(['user.specializations', 'reviews', 'activeSponsorshipPivot.sponsorship']);
        if ($rating !== null && $rating !== "null")
            $query->havingRaw('avg_vote >= ?', [$rating]);
        if ($reviewsNum !== null && $reviewsNum !== "null")
            $query->havingRaw('total_reviews >= ?', [$reviewsNum]);

        return $query->get()->append('active_sponsorship');
    }
}

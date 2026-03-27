<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Actions\Profile\CreateProfile;
use App\Actions\Profile\EditProfile;
use App\Actions\Profile\GetProfile;
use App\Actions\Profile\UpdateProfile;
use App\Helpers\TimeHelper;
use App\Http\Responses\RespondsWithApi;
use App\Models\Profile;
use App\Models\ProfileSponsorship;
use App\Models\User;
use App\Validation\BaseValidation;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    use RespondsWithApi;

    public function __construct(protected Request $req) {}

    public function index()
    {
        $profiles = Profile::with([
            'user.specializations',
            'activeSponsorshipPivot.sponsorship'
        ])->get()->append('active_sponsorship');

        return $this->apiResponse($profiles, 'profiles');
    }

    public function sponsoredIndex()
    {
        $fRTime = TimeHelper::normalizeToAppYear($this->req->query('firstReqTime'));
        $profilesPaginator = Profile::whereHas(
            'sponsorshipPivot',
            fn($q) => $q->active($fRTime)
        )->with(['user.specializations',])
            ->orderByDesc(ProfileSponsorship::select('start_date')
                ->whereColumn('profiles.id', 'profile_id')->active($fRTime))
            ->paginate($this->req->query('per_page', 10));

        return $this->apiResponse($profilesPaginator, 'paginated_profiles');
    }

    /**
     *
     * @param  string  $name Route parameter 'name'
     * @param  GetProfile  $getter
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(GetProfile $getter, string $name)
    {
        $user = $this->resolveUser($name);
        $profile = $getter->handle(user: $user);

        $apiResponseArgs = [];
        if (!$profile) $apiResponseArgs = [
            $user->setVisible(['first_name', 'last_name'])->toArray(),
            'user',
            'The requested profile could not be found',
            404
        ];
        else $apiResponseArgs = [$profile, 'profile'];

        return $this->apiResponse(...$apiResponseArgs);
    }

    /**
     * Return the user based on the 'name' parameter
     *
     * @param string $name Eiter 'authenticated' or a user full name separated by '-',
     * eventually including homonymous_id
     * @return User
     */
    private function resolveUser(string $name)
    {
        $user = Auth::user();
        if ($name === 'authenticated') {
            if (!$user) throw new AuthenticationException();
        } else {
            $nameEls = explode('-', $name);
            if (!isset($nameEls[2])) $nameEls[] = null;
            $keyedNameElements = array_combine(['first_name', 'last_name', 'homonymous_id'], $nameEls);
            $user = User::where($keyedNameElements)->firstOrFail();
        }

        return $user;
    }

    public function store(CreateProfile $creator)
    {
        $validated = $this->req->validate(BaseValidation::profileToCreate());

        return $this->apiResponse(
            $creator->handle($this->req->user(), $validated),
            'profile',
            'profile created'
        );
    }

    public function edit(EditProfile $finder)
    {
        return $this->apiResponse(
            $finder->handle($this->req->user()),
            'profile',
            'profile retrieved for edit'
        );
    }

    public function update(UpdateProfile $updater)
    {
        $validated = [
            'profile' => $this->req->validate(BaseValidation::profile()),
            'user' => $this->req->validate(BaseValidation::user())
        ];
        $profile = $updater->handle($this->req->user(), $validated['profile'], $validated['user']);


        return $this->apiResponse($profile, 'profile', 'profile updated');
    }
}

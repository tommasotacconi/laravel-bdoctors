<?php

namespace App\Actions\Profiles;

use App\Actions\StoreFile;
use App\Models\Profile;
use App\Validation\BaseValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreateProfile
{
    public function __construct(protected Request $req, protected StoreFile $sF) {}

    public function handle(): Profile
    {
        $validated = $this->req->validate(BaseValidation::profile());

        $validated['user_id'] = Auth::id();
        $this->prepareFileField('photo', $validated, 'photos');
        $this->prepareFileField('curriculum', $validated, 'curricula');

        return Profile::create($validated);
    }

    private function prepareFileField(string $field, array &$validatedReq, string $storageDir)
    {
        if ($this->req->hasFile($field)) {
            $validatedReq[$field] = $this->sF->handle($validatedReq[$field], $storageDir);
        }
    }
}

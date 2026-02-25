<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;

class SetFileField
{
    public function __construct(protected StoreFile $sF) {}

    /**
     * Set file related field if present
     *
     * @param string $field
     * @param array $validatedReq
     * @param string $storageDir
     * @return void
     */
    public function handle(UploadedFile|null $validatedFile, string $storageDir)
    {
        if ($validatedFile)
            return $this->sF->handle($validatedFile, $storageDir);
    }
}

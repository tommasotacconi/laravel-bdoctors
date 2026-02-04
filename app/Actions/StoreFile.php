<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class StoreFile
{
    /**
     * Store file and returns file path
     *
     * @param UploadedFile $file
     * @param string $dbDirectory
     * @return String
     */
    public function handle(UploadedFile $file, string $storageDir): String
    {
        return $file->store($storageDir, 'public');
    }
}

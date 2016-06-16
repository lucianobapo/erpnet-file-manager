<?php
/**
 * Created by PhpStorm.
 * User: luciano
 * Date: 16/06/16
 * Time: 00:21
 */

namespace ErpNET\FileManager;

use Illuminate\Support\Facades\Storage;

class FileManager
{
    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile|array $uploadedFile
     * @param string $fileDir
     * @param string $newName
     * @return bool
     */
    public function saveFile($uploadedFile, $fileDir, $newName = null){
        if ($uploadedFile->isValid()){
            if (substr($fileDir,-1)!=DIRECTORY_SEPARATOR)
                $fileDir = $fileDir . DIRECTORY_SEPARATOR;

            if (!Storage::exists($fileDir)) Storage::makeDirectory($fileDir);

            if (is_null($newName))
                $fileName = $uploadedFile->getClientOriginalName(). '.' . $uploadedFile->getClientOriginalExtension();
            else
                $fileName = $newName . '.' . $uploadedFile->getClientOriginalExtension();

            return Storage::put($fileDir . $fileName, file_get_contents($uploadedFile->getRealPath()));
        } else return false;
    }
}
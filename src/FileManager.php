<?php
/**
 * Created by PhpStorm.
 * User: luciano
 * Date: 16/06/16
 * Time: 00:21
 */

namespace ErpNET\FileManager;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

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
                $fileName = $uploadedFile->getClientOriginalName();
            else
                $fileName = $newName . '.' . $uploadedFile->getClientOriginalExtension();

            $fileContents = file_get_contents($uploadedFile->getRealPath());
            if (Storage::put($fileDir . $fileName, $fileContents))
                return $fileName;
            else
                return false;
        } else return false;
    }

    /**
     * @param \GuzzleHttp\Psr7\Stream $fileContent
     * @param string $fileDir
     * @param string $newName
     * @return bool
     */
    public function saveJpg($fileContent, $fileDir, $newName = null){
        if (substr($fileDir,-1)!=DIRECTORY_SEPARATOR)
            $fileDir = $fileDir . DIRECTORY_SEPARATOR;

        if (!Storage::exists($fileDir)) Storage::makeDirectory($fileDir);

        if (is_null($newName))
            $fileName = md5($fileContent->getContents()).'.jpg';
        else
            $fileName = $newName;

        if (Storage::exists($fileDir . $fileName)){
            return $fileName;
        } else {
            if (Storage::put($fileDir . $fileName, $fileContent->__toString()))
                return $fileName;
            else
                return false;
        }
    }

    public function loadImageFile($file, $fileDir){
        $image = $this->makeImage($file, $fileDir);
        abort_if(is_null($image),500, 'Erro na Imagem');
        return $image->response();
    }

    public function loadImageFileFit($size, $file, $fileDir){
        $resolution = explode('x',$size);
        $image = $this->makeImage($file, $fileDir);
        abort_if(is_null($image),500, 'Erro na Imagem');
        if (count($resolution)==2)
            $image = $image->fit($resolution[0],$resolution[1]);
        return $image->response();
    }

    protected function makeImage($file, $fileDir){
        if (substr($fileDir,-1)!=DIRECTORY_SEPARATOR)
            $fileDir = $fileDir . DIRECTORY_SEPARATOR;

        if (Storage::exists($fileDir . $file)){
            $contents = Storage::get($fileDir . $file);
            $manager = new ImageManager(array('driver' => 'gd','allow_url_fopen'=>true));
            return $manager->make($contents);
        }
        else
            return null;
    }
}
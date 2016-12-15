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
    private $cacheImageManager;
    private $cacheImageManagerDuration;
    private $manager;

    public function __construct(){
        $this->cacheImageManager = true;
        $this->cacheImageManagerDuration = 60*24*30;
        $config = array('driver' => 'gd', 'allow_url_fopen' => true);
        if ($this->cacheImageManager) $config['cache'] = ['path' => storage_path('framework/cache/image')];
        $this->manager = new ImageManager($config);
    }

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

            $fileContents = file_get_contents($uploadedFile->getRealPath());

            if (is_null($newName))
//                $fileName = $uploadedFile->getClientOriginalName();
                $fileName = md5($fileContents). '.' . $uploadedFile->getClientOriginalExtension();
            else
                $fileName = $newName . '.' . $uploadedFile->getClientOriginalExtension();

            if (Storage::exists($fileDir . $fileName)){
                return $fileName;
            } else {
                if (Storage::put($fileDir . $fileName, $fileContents,
                    \Illuminate\Contracts\Filesystem\Filesystem::VISIBILITY_PUBLIC))
                    return $fileName;
                else
                    return false;
            }
        } else return false;
    }

    /**
     * @param \GuzzleHttp\Psr7\Stream | \Psr\Http\Message\StreamInterface $fileContent
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

    public function loadImageFileFit($size, $file, $fileDir, $format='png'){
        $resolution = explode('x',$size);
        $image = $this->makeImage($file, $fileDir);

        abort_if(is_null($image),500, 'Erro ao carregar a Imagem');
        abort_if(count($resolution)!=2,500, 'Erro na definição da Resolução');

        $baseImg = $this->manager->canvas($resolution[0],$resolution[1]);
        $image = $image->resize($resolution[0],$resolution[1], function ($c) {
            $c->aspectRatio();
            $c->upsize();
        });

        $baseImg
            ->insert($image, 'center')
            ->stream($format);

        return $baseImg->response();

//        $image = $image->fit($resolution[0],$resolution[1]);
//        return $image->response();
    }

    /**
     * @param string $file
     * @param string $fileDir
     * @return \Intervention\Image\Image|null
     */
    protected function makeImage($file, $fileDir){
        if (substr($fileDir,-1)!=DIRECTORY_SEPARATOR)
            $fileDir = $fileDir . DIRECTORY_SEPARATOR;

        if (!Storage::exists($fileDir . $file)) return null;

        if ($this->cacheImageManager)
            return $this->manager->cache(function($image) use ($fileDir, $file) {
                $contents = Storage::get($fileDir . $file);
                $image->make($contents);
            }, $this->cacheImageManagerDuration, true);
        else{
            $contents = Storage::get($fileDir . $file);
            return $this->manager->make($contents);
        }
    }

    /**
     * @param string $id
     * @param string $size
     * @return \Intervention\Image\Image
     */
    protected function makeSocialProfileImage($id, $size = '116x116')
    {
        if ($size=="large" || $size=="normal" || $size=="small" || $size=="album" || $size=="square") {
            $source = 'https://graph.facebook.com/' . $id . '/picture?type=' . $size;
            if ($this->cacheImageManager)
                $image = $this->manager->cache(function($image) use ($source) {
                    $image->make($source);
                }, $this->cacheImageManagerDuration, true);
            else
                $image = $this->manager->make($source);
        } else {
            $source = 'https://graph.facebook.com/' . $id . '/picture?type=large';
            $resize = explode('x', $size);
            if ($this->cacheImageManager)
                $image = $this->manager->cache(function($image) use ($source, $resize) {
                    $image->make($source)->resize($resize[0], $resize[1]);
                }, $this->cacheImageManagerDuration, true);
            else
                $image = $this->manager->make($source)->resize($resize[0], $resize[1]);
        }
        return $image;
    }

    /**
     * @param string $file
     * @param string $fileDir
     * @param string $id
     * @param array $params
     * @return \Intervention\Image\Image
     */
    public function insertSocialProfileWithBgImage($file, $id, $params = [], $fileDir = 'jokes')
    {
        $size = isset($params['size']) ? $params['size'] : '116x116';
        $socialProfileImage = $this->makeSocialProfileImage($id, $size);

        $background = $this->makeImage($file, $fileDir);

        $position = isset($params['position']) ? $params['position'] : 'top-left';
        $x = isset($params['x']) ? $params['x'] : 0;
        $y = isset($params['y']) ? $params['y'] : 0;
        $background->insert($socialProfileImage, $position, $x, $y);

        if (isset($params['name'])){
            $namesize = isset($params['namesize']) ? $params['namesize'] : 24;
            $namecolor = isset($params['namecolor']) ? $params['namecolor'] : '000000';
            $namex = isset($params['namex']) ? $params['namex'] : 270;
            $namey = isset($params['namey']) ? $params['namey'] : 230;

            $background->text(str_replace('-',' ',$params['name']), $namex, $namey, function ($font) use ($namesize,$namecolor) {
                $font->file(base_path('resources/fonts').'/arialbd.ttf');
                $font->size($namesize);
                $font->color('#'.$namecolor);
                $font->align('left');
                $font->valign('top');
            });
        }

        return $background;

//        $newName = md5($background->stream()).substr($file,-4);

//        $fileName = $this->saveJpg($background->stream(), $fileDir, $newName);

    }

    /**
     * @param string $file
     * @param string $fileDir
     * @param string $id
     * @param array $params
     * @return \Intervention\Image\Image
     */
    public function resourceImgSocialProfileWithBg($file, $id, $params = [], $fileDir = 'jokes')
    {
        $size = empty($params['paramProfileImageSize']) ? '116x116': $params['paramProfileImageSize'];

        $socialProfileImage = $this->makeSocialProfileImage($id, $size);

        $background = $this->makeImage($file, $fileDir);

        $position = isset($params['position']) ? $params['position'] : 'top-left';

        $x = empty($params['paramProfileImageX']) ? 0 : $params['paramProfileImageX'];
        $y = empty($params['paramProfileImageY']) ? 0 : $params['paramProfileImageY'];

        $background->insert($socialProfileImage, $position, $x, $y);

        if (isset($params['name'])){

            $namesize = empty($params['paramNameSize']) ? 24 : $params['paramNameSize'];
            $namecolor = empty($params['paramNameColor']) ? '000000' : $params['paramNameColor'];
            $namex = empty($params['paramNameX']) ? 270 : $params['paramNameX'];
            $namey = empty($params['paramNameY']) ? 230 : $params['paramNameY'];

            $background->text(str_replace('-',' ',$params['name']), $namex, $namey, function ($font) use ($namesize, $namecolor) {
                $fontFile = base_path('resources/fonts') . '/arialbd.ttf';
                if (file_exists($fontFile)) $font->file($fontFile);
                $font->size($namesize);
                $font->color('#'.$namecolor);
                $font->align('left');
                $font->valign('top');
            });
        }

        return $background;
    }
}
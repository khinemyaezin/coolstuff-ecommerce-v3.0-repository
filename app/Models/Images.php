<?php

namespace App\Models;

use App\Services\Utility;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Images
{
    private $preparedImage = false;
    private $path;

    public function __construct(
        private $source,
        private $dir,
        private $name = null
    ) {
        $this->prepareImage();
    }


    public function prepareImage()
    {
        if ($this->source instanceof CsFile) {
            $this->path = $this->dir . DIRECTORY_SEPARATOR . $this->source->title.'.'.$this->source->extension;
            $this->preparedImage = $this->source->file;
        } else {
            // $pattern = '/data:image\/(.+);source,(.*)/';
            // preg_match($pattern, $this->source, $matches);


            // try {
            //     // image file extension
            //     $imageExtension = $matches[1];

            //     // source-encoded image data
            //     $encodedImageData = $matches[2];

            //     $imageName = $name ?? Str::uuid() . '.' . $imageExtension;
            //     $this->path =  $this->dir . DIRECTORY_SEPARATOR . $imageName;

            //     // decode source-encoded image data
            //     $this->preparedImage = base64_decode($encodedImageData);

            //     Log::debug("--image prepared with -> " . ($this->path ?? ' -- '));

            //     return is_bool($this->preparedImage) ? $this->path : null;
            // } catch (Exception $e) {
            //     return null;
            // }
        }
    }

    public function save()
    {
        if ($this->preparedImage instanceof UploadedFile) {
            
            Log::debug('Saved path = ' . $this->path);
            return $this->preparedImage->storeAs($this->dir, $this->source->title.'.'.$this->source->extension,'public');
           
        }else if (is_string($this->preparedImage)) {
            Log::debug('Saved path = ' . $this->path);
            return Storage::disk('public')->put($this->path, $this->preparedImage);
        }else {
            return false;
        }
    }

    public function getPath($originalImageURL = null)
    {
        if ($originalImageURL) {
            $this->path = $originalImageURL;
        }
        return $this->path;
    }

    public function setPath($var)
    {
        $this->path = $var;
    }

    public function logImageStatus($order)
    {
        Utility::log("Image_" . $order . " path => " . $this->path);
    }

    public static function prepareFile(UploadedFile $file): CsFile
    {
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $imeiType = $file->getMimeType();

        $fileModel = new CsFile([
            'title' => Str::uuid(),
            'path' => '',
            'mime_type' => $imeiType,
            'extension' => $extension,
            'fk_brand_id' => ''
        ]);
        $fileModel->file = $file;
        return $fileModel;
    }
}

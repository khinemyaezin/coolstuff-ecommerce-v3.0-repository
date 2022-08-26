<?php

namespace App\Models;

use App\Services\ImageService;
use App\Services\Utility;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Images
{
    private $preparedImage = false;
    private $path;

    public function __construct(
        private $base64,
        private $dir,
        private $name = null
    ) {
        $this->prepareImage();
    }

    public function prepareImage()
    {

        $pattern = '/data:image\/(.+);base64,(.*)/';
        preg_match($pattern, $this->base64, $matches);


        try {
            // image file extension
            $imageExtension = $matches[1];

            // base64-encoded image data
            $encodedImageData = $matches[2];

            $imageName = $name ?? Str::random(10) . '.' . $imageExtension;
            $this->path =  $this->dir . DIRECTORY_SEPARATOR . $imageName;

            // decode base64-encoded image data
            $this->preparedImage = base64_decode($encodedImageData);

            Log::debug("--image prepared with -> ".($this->path ?? ' -- '));

            return is_bool($this->preparedImage) ? $this->path : null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function save()
    {
        if ($this->preparedImage) {
            Log::debug('Saved path = ' . $this->path);
            Storage::disk('public')->put($this->path, $this->preparedImage);
        } else {
            return true;
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
        Utility::log("Image_".$order." path => " . $this->path);
    }
}

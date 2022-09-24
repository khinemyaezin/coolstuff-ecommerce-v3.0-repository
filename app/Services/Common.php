<?php

namespace App\Services;

use App\Models\Criteria;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;




class Common
{
    public const DEFAULT_VALIDATION_RULES = [
        'relationships' => 'string|nullable',
        'pagination' => 'number|nullable'
    ];

    static function getPaginate($count)
    {
        if ($count && $count == -1) {
            return config('constants.PAGINATION_MAX_COUNT');
        } else if ($count && $count > 0) {
            return $count;
        } else {
            return config('constants.PAGINATION_COUNT');
        }
    }

    static function deleteImage($url)
    {
        $file = public_path('storage/' . $url);
        error_log('Image.deleting.$file : ' . $file);
        if (File::exists($file)) {
            error_log('Image.deleting.$file->exists : true');
            return File::delete($file);
        } else {
            error_log('Image.deleting.$file->not exists : true');
            return false;
        }
    }

    public static function settings()
    {
        return config('settings');
    }

    static function splitToArray($req)
    {
        return $req ? preg_split('@,@', $req, -1, PREG_SPLIT_NO_EMPTY) : [];
    }

    static function prepareRelationships(Criteria $criteria, $data)
    {
        if ($criteria->relationships && is_array($criteria->relationships)) {
            foreach ($criteria->relationships as $relationship) {
                // check optional relationships exists
                $relation = [];
                if (isset($criteria->httpParams[$relationship])) { //null check
                    $optional = $criteria->httpParams[$relationship]; //check if exists
                    if ($optional) {
                        $relation[$relationship] = function ($query) use ($optional) {
                            $optionalArray =  Common::splitToArray($optional);
                            foreach ($optionalArray as $value) {
                                $query->with($value);
                            }
                        };
                        $data = $data->with($relation);
                    }
                } else {
                    $data = $data->with($relationship);
                }
            }
        }

        return $data;
    }

    static function isID($str)
    {
        return preg_match('/^$|^-1$/', $str) == 0;
    }

    static function getURL($filePath)
    {
        if (filter_var($filePath, FILTER_VALIDATE_URL)) {
            return $filePath;
        } else {
            return $filePath ? str_replace('\\', '/', asset('storage/' . $filePath)) : null;
        }
    }

    static function log($content)
    {
        return;
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/debug.log'),
        ])->info($content);
    }
}

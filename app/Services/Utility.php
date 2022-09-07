<?php

namespace App\Services;

use App\Models\Criteria;
use App\Models\ViewResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class Utility
{
    public static $IMAGE_AVATARS = 'avatars';
    public static $IMAGE_PRODUCTS = 'products';
    public static $PAGINATION_COUNT = 10;
    public static $PAGINATION_MAX_COUNT = 500;
    public static $ERROR_DEFCODE = -1;
    public static $TOKEN = 'cs-token';
    public static $SETTINGS = 'settings';
    public static $DATETIME_FORMAT = 'd-m-Y h:i:s A';
    public static $CATEGORY_DEPTH_LVL_FOR_ATTRIBUTES = 3;

    public static $BIZ_STATUS = [
        'active' => 2,
        'draf' => 6,
        'deleted' => 4
    ];
    public static $ROW_STATUS = [
        'normal' => 2,
        'delete' => 4,
    ];
    public static $FILE_TYPE = [
        1 => 'src',
        2 => 'background-image',
    ];

    public static $QUOTE_STATUS = [
        'confirm' => 2,
        'pending' => 6,
    ];

    static function getPaginate($count)
    {
        if ($count && $count == -1) {
            return Utility::$PAGINATION_MAX_COUNT;
        } else if ($count && $count > 0) {
            return $count;
        } else {
            return Utility::$PAGINATION_COUNT;
        }
    }

    static function jsonError($data)
    {
        $result = new ViewResult();
        $result->message = $data->customMessages;
        $result->success = false;
        return $result;
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
    static function translateError($error)
    {
        switch ($error->getCode()) {
                //framework
            case 1001:
                return "Invalid Request";
                break;
            case 1002:
                return "Request resource doesn't exist";
                break;

                //sql error 
            case 23503:
                return "The action can't be completed because another process is using.";
                break;

            case 23505:
                return "Data already exists.";
                break;

            default:
                return "Something went wrong!";
                break;
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
                if (isset($criteria->optional[$relationship])) { //null check
                    $optional = $criteria->optional[$relationship]; //check if exists
                    if ($optional) {
                        $relation[$relationship] = function ($query) use ($optional) {
                            $optionalArray =  Utility::splitToArray($optional);
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
        if(filter_var($filePath, FILTER_VALIDATE_URL)) {
            return $filePath;
        }else {
            return $filePath ? str_replace('\\', '/', asset('storage/' . $filePath)) : null;
        }
        
    }
    static function log($content)
    {
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/debug.log'),
        ])->info($content);
    }
}

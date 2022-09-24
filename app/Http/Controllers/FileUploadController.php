<?php

namespace App\Http\Controllers;

use App\Exceptions\FailToSave;
use App\Http\Requests\FileRequest;
use App\Http\Requests\FileUploadRequest;
use App\Models\Criteria;
use App\Models\FilesInBrand;
use App\Models\Images;
use App\Models\ViewResult;
use App\Services\BrandService;
use App\Services\Common;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FileUploadController extends Controller
{
    function __construct(protected BrandService $brandService)
    {
    }

    public function store(FileUploadRequest $request)
    {
        DB::beginTransaction();
        $result = new ViewResult();
        try {
            $result->details = [];
            if ($request->hasFile('images')) {
                $allowedfileExtension = ['pdf', 'jpg', 'png', 'docx'];
                $files = $request->file('images');

                foreach ($files as $file) {
                    $csFile = Images::prepareFile($file);
                    $csFile->ratio = $request->ratio;
                    $check = in_array($csFile->extension, $allowedfileExtension);
                    if (!$check) {
                        throw new Exception('Invalid format', 1001);
                    }

                    $image = new Images($csFile, config('constants.IMAGE_PRODUCTS'));
                    $csFile->path = $image->getPath();

                    $csFile->save();
                    $filesInBrand = new FilesInBrand([
                        'fk_brand_id' => Auth::user()->fk_brand_id,
                        'fk_file_id' => $csFile->id
                    ]);

                    $filesInBrand->save();
                    $image->save();
                    array_push($result->details, $csFile);
                    $result->success();
                }
            }
        } catch (Exception $e) {
            Common::log("error uploading images");
            $result->error($e);
        }
        $result->completeTransaction();
        return response()->json($result, $result->getHttpStatus());
    }

    public function getMedias(FileRequest $request)
    {
        $criteria = new Criteria();
        $criteria->pagination = $request['pagination'];
        $criteria->optional = $request->all();
        $result = $this->brandService->getMedias($criteria);
        return response()->json($result, $result->getHttpStatus());
    }
}







//dd($check);

// if($check)

// {

// $items= Item::create($request->all());

// foreach ($request->photos as $photo) {

// $filename = $photo->store('photos');

// ItemDetail::create([

// 'item_id' => $items->id,

// 'filename' => $filename

// ]);

// }
//     }
// }

<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequest;
use App\Http\Requests\CategorySaveRequest;
use App\Http\Requests\GetCategoryByDepthRequest;
use App\Http\Requests\GetCategoryRequest;
use App\Models\Categories;
use App\Models\Criteria;
use App\Models\ViewResult;
use App\Services\CategoryService;
use Illuminate\Support\Facades\DB;

class CategoriesApiController extends Controller
{
    function __construct(protected CategoryService $categoryService)
    {
    }

    public function index(GetCategoryRequest $request)
    {
        $criteria = new Criteria();
        $criteria->pagination = $request['pagination'];
        $criteria->details = $request->validated();
        return response()->json($this->categoryService->getCategories($criteria));
    }

    public function getSubCategories()
    {
        return response()->json($this->categoryService->getSubCategories(request()->id));
    }

    public function getCategoriesByDepth(GetCategoryByDepthRequest $request)
    {
        $criteria = new Criteria();
        $criteria->pagination = $request['pagination'];
        $criteria->optional = $request->validated();
        $criteria->details['depth'] = $request->route('depth');
        return response()->json($this->categoryService->getCategoriesByDepth($criteria));
    }

    public function store(CategorySaveRequest $request)
    {
        DB::beginTransaction();
        $result = null;

        $category = new Categories([
            'title' => $request['title'],
        ]);
        $result = $this->categoryService->create($category, $request->parent_id);

        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function update($id)
    {
        DB::beginTransaction();
        $request = request();
        // $result = null;
        // $validator = validator($request->all(), [
        //     'title' => 'string|required|max:100'
        // ]);
        // if ($validator->fails()) {
        //     $result = new ViewResult();
        //     $result->error(new InvalidRequest(), $validator->errors());
        // } else {
        // }
        $category = new Categories();
        $category->id =  $id;
        $category->title = $request['title'];
        $result = $this->categoryService->update($category);
        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        $result = $this->categoryService->delete($id);
        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }
  
    public function searchCategories()
    {
        return response()->json($this->categoryService->searchCategories(request()->title));
    }
}

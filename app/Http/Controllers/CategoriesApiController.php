<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequest;
use App\Models\Categories;
use App\Models\ViewResult;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriesApiController extends Controller
{
    function __construct(protected CategoryService $categoryService)
    {
    }

    public function index()
    {
        return response()->json($this->categoryService->getCategories());
    }

    public function getSubCategories()
    {
        return response()->json($this->categoryService->getSubCategories(request()->id));
    }
    public function getCategoriesByDepth($depth)
    {
        return response()->json($this->categoryService->getCategoriesByDepth($depth));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        $result = null;
        $validator = validator($request->all(), [
            'parent_id' => array('string', 'required', 'regex:/(^[0-9]+$)/u', 'exists:categories,id'),
            'title' => 'string|required|max:100'
        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            $result->error(new InvalidRequest(), $validator->errors());
        } else {
            $category = new Categories([
                'title' => $request['title'],
            ]);
            $result = $this->categoryService->create($category, $request->parent_id);
        }
        $result->completeTransaction();
        return response()->json($result);
    }

    public function show(Categories $categories)
    {
        //
    }

    public function update($id)
    {
        DB::beginTransaction();
        $request = request();
        $result = null;
        $validator = validator($request->all(), [
            'title' => 'string|required|max:100'
        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            $result->error(new InvalidRequest(), $validator->errors());
        } else {
            $category = new Categories();
            $category->id =  $id;
            $category->title = $request['title'];
            $result = $this->categoryService->update($category);
        }
        $result->completeTransaction();
        return response()->json($result);
    }

    public function destroy(Categories $categories)
    {
        //
    }
    public function createCategoryLeaves()
    {
        DB::beginTransaction();
        $result = $this->categoryService->createCategoryLeaves();
        $result->completeTransaction();
        return response()->json($result);
    }
    public function searchCategories()
    {
        return response()->json($this->categoryService->searchCategories(request()->title));
    }
}

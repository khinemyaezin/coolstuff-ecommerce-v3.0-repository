<?php

namespace App\Services;

use App\Models\Categories;
use App\Models\CategoryLeaves;
use App\Models\ViewResult;
use Exception;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function getCategories()
    {
        $result = new ViewResult();
        try {
            $result->details = DB::select('SELECT node.id,node.title, (COUNT(parent.title) - 1) AS depth 
                FROM categories AS node,categories AS parent
                WHERE node.lft BETWEEN parent.lft AND parent.rgt
                GROUP BY node.title,node.lft,node.id 
                ORDER BY node.lft
            ');

            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function getCategoriesByDepth($depth)
    {
        $result = new ViewResult();
        try {
            $result->details = DB::table('categories as node')->join('categories as parent',function($join)
            {
                $join->whereRaw('node.lft between parent.lft and parent.rgt');
            })
            ->groupBy(['node.id','node.title','node.lft'])
            ->havingRaw('(COUNT(parent.title)-1)=?',[2])
            ->orderBy('node.lft')
            ->selectRaw("node.id,node.title,
                            array_to_string( 
                                array_agg(parent.title ORDER BY parent.lft),' > '
                            ) as path,
                            (COUNT(parent.title)-1) as depth")->paginate(Utility::getPaginate(null));

            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
    public function getSubCategories($id)
    {
        $result = new ViewResult();
        try {
            $result->details = DB::select('SELECT node.id,node.title, (COUNT(parent.id) - (sub_tree.depth + 1)) AS depth
                FROM categories AS node, categories AS parent, categories AS sub_parent,
                (SELECT node.id, (COUNT(parent.id) - 1) AS depth 
                FROM categories AS node, categories AS parent
                WHERE node.lft BETWEEN parent.lft AND parent.rgt AND node.id = ? 
                GROUP BY node.id,node.title,node.lft ORDER BY node.lft)AS sub_tree 
                WHERE node.lft BETWEEN parent.lft AND parent.rgt AND node.lft BETWEEN sub_parent.lft AND sub_parent.rgt AND sub_parent.id = sub_tree.id 
                GROUP BY node.id,node.title,sub_tree.depth,node.lft HAVING (COUNT(parent.id) - (sub_tree.depth + 1)) <= 1 ORDER BY node.lft;
            ', [$id]);

            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function create(Categories $category, $parentId)
    {
        $result = new ViewResult();
        try {
            $result->details = DB::select('select store_category(?,?) as id', [intval($parentId), $category->title]);
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function update(Categories $data)
    {
        $result = new ViewResult();
        try {

            $category = Categories::find($data->id);
            $category->title = $data->title;
            $result->complete(
                $category->save()
            );
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
    public function createCategoryLeaves()
    {
        $result = new ViewResult();
        try {
            $result->details = DB::select('select store_category_leaf(?)',[Utility::$CATEGORY_DEPTH_LVL_FOR_ATTRIBUTES]);
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
    public function searchCategories($title)
    {
        $result = new ViewResult();
        try {
            if ($title) {
                $result->details = CategoryLeaves::whereRaw("lower(replace(path,'/',' ')) = lower('".$title."')")->limit(10)
                    ->union(
                        CategoryLeaves::whereRaw("lower(replace(path,'/',' ')) LIKE lower('".$title."%')")->limit(10)
                    )->union(
                        CategoryLeaves::whereRaw("lower(replace(path,'/',' ')) LIKE lower('%".$title."%')")->limit(10)
                    )->get();
            }else {
                $result->details = CategoryLeaves::limit(10)->get();
            }

            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
}

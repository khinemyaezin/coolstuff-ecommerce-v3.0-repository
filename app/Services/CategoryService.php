<?php

namespace App\Services;

use App\Models\Categories;
use App\Models\CategoryLeaves;
use App\Models\Criteria;
use App\Models\ViewResult;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function getCategories(Criteria $criteria)
    {
        //dd($criteria);
        $result = new ViewResult();
        //DB::enableQueryLog();
        try {
            if (isset($criteria->details['sub'])) {
                $result = $this->getSubCategories($criteria->details['sub']);
            } else {
                $where = [];
                $data = [];
                $limit = '';

                if (isset($criteria->details['title'])) {
                    array_push($where, 'AND lower(parent.title) like :title');
                    $data[':title']  = strtolower($criteria->details['title'] . '%');
                }
                $limit = count($where) > 0 ? ' limit 1' :  '';

                $result->details = DB::select(
                    'SELECT node.id,node.title, (COUNT(parent.title) - 1) AS depth 
                    FROM categories AS node,categories AS parent
                    WHERE node.lft BETWEEN parent.lft AND parent.rgt '
                        . join(' ', $where)
                        . ' GROUP BY node.title,node.lft,node.id 
                    ORDER BY node.lft' . $limit,
                    $data
                );
            }

            // $result->queryLog = DB::getQueryLog();
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function getCategoriesByDepth(Criteria $criteria)
    {
        $result = new ViewResult();
        $depth = isset($criteria->details['depth']) ? $criteria->details['depth'] : 2;
        try {
           $records = DB::table('categories as node')
           ->join('categories as parent', function ($join) use ($criteria) {
                $join->whereRaw('node.lft between parent.lft and parent.rgt');
                if (isset($criteria->optional['title'])) {
                    $join->whereRaw('node.ts_path_search @@ phraseto_tsquery(?)', [$criteria->optional['title']]);
                }
            })
            ->groupBy(['node.id', 'node.title', 'node.lft'])
            ->havingRaw('(COUNT(parent.title)-1)=?', [$depth])
            ->orderBy('node.lft')
            ->selectRaw("node.id,node.title,node.full_path as path,(COUNT(parent.title)-1) as depth")
            ->paginate(Common::getPaginate($criteria->pagination));

            foreach($criteria->optional as $key=>$value) {
                $records->appends([$key => $value]);
            }
            
            $result->details = $records;
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
            $result->details = collect($result->details)->transform(function ($value) {
                //dd($value);
                return Categories::findOrFail($value->id);
            });
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

            $category = Categories::findOrFail($data->id);
            $category->title = $data->title;
            $category->save();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function delete($id)
    {
        $result = new ViewResult();
        try {
            $category = Categories::findOrFail($id);
            if (!$category) {
                throw new ModelNotFoundException("Invlaid request");
            }
            DB::select("select * from delete_category(?)", [$id]);
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
                $result->details = DB::select(
                    "SELECT t1.id,t1.lft,t1.rgt,title,t1.full_path
                    , (select id from (SELECT row_number() over(order by t2.rgt desc)-1 as depth,
                            t2.id
                            FROM categories t2 
                            WHERE t2.lft <= t1.lft AND t2.rgt >= t1.rgt	
                            
                            ORDER BY t2.rgt desc) as ps where ps.depth=2) AS lvl_id
                    FROM categories t1 where t1.rgt = t1.lft+1 and ts_path_search @@ phraseto_tsquery(?) 
                    ORDER BY ts_rank(ts_path_search, phraseto_tsquery(?)) DESC",
                    [$title,$title]
                );
            } else {
                $result->details = CategoryLeaves::limit(10)->get();
            }

            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
}

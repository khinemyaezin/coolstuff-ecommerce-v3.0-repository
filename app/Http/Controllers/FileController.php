<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileController extends Controller
{
    public function upload(Request $req)
    {
       if($req->hasFile('file')) {
            return response( 'accepted',200);
       }
       return response( 'not accepted',200);
    }
}

<?php

namespace App\Models;

use App\Services\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilesInBrand extends Model
{
    use HasFactory;
    use HasCompositePrimaryKey;
    protected $table = "files_in_brands";
    protected $primaryKey = array('fk_brand_id', 'fk_file_id');

    protected $fillable = [
        'fk_file_id',
        'fk_brand_id',
    ];

}

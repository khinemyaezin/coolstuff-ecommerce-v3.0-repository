<?php

namespace App\Models;

use App\Casts\ImageUrlGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class CsFile extends Model
{
    use HasFactory,CsModel;
    public UploadedFile $file;
    
    protected $table = "files";
    protected $fillable = [
        'title',
        'path',
        'mime_type',
        'extension',
        'fk_brand_id',
        'ratio'
    ];
    protected $casts = [
        'id' => 'string',
        'path' => ImageUrlGenerate::class,
    ];
    protected $hidden = [
        'status',
        'biz_status',
        'title',
        'created_at',
        'updated_at'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    use HasFactory,CsModel;
    protected $fillable = [
        "id",
        "code",
        "title",
        "description",
    ];
    protected  $casts = [
        'id' => 'string',
        'created_at' => 'datetime:d-m-Y h:i:s A',
        'updated_at' => 'datetime:d-m-Y h:i:s A',
    ];
    protected $hidden = [
        'status'
    ];
    public function tasks()
    {
        return $this->belongsToMany(Tasks::class, 'roles_privileges', 'fk_role_id', 'fk_task_id');
    }
}

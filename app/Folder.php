<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $casts = [
        'user_id' => 'integer',
    ];

    public function tasks()
    {
        // $this->hasMany('App\Task', 'folder_id', 'id') の省略形
        return $this->hasMany('App\Task');
    }
}

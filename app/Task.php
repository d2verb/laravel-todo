<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Task extends Model
{
    protected $dates = ['due_date'];
    protected $casts = [
        'folder_id' => 'integer',
    ];


    // 状態定義
    const STATUS = [
        1 => [ 'label' => '未着手', 'class' => 'label-danger' ],
        2 => [ 'label' => '着手中', 'class' => 'label-info' ],
        3 => [ 'label' => '完了', 'class' => '' ],
    ];

    public function getStatusLabelAttribute()
    {
        // 状態値
        $status = $this->attributes['status'];

        //定義されていなければ空文字を返す
        if (!isset(self::STATUS[$status])) {
            return '';
        }

        return self::STATUS[$status]['label'];
    }

    public function getStatusClassAttribute()
    {
        // 状態値
        $status = $this->attributes['status'];

        //定義されていなければ空文字を返す
        if (!isset(self::STATUS[$status])) {
            return '';
        }

        return self::STATUS[$status]['class'];
    }

    public function getFormattedDueDateAttribute()
    {
        return Carbon::parse($this->attributes['due_date'])->format('Y/m/d');
    }
}

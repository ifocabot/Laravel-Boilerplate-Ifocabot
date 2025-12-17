<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $table = "levels";

    protected $fillable = [
        'name',
        'grade_code',
        'min_salary',
        'max_salary',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = "categories";

    protected $primaryKey = "CategoryID";

    protected $fillable = ['name', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}

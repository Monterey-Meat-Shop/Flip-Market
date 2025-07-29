<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $table = "brands";

    protected $primaryKey = "BrandID";

    protected $fillable = ['name', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}

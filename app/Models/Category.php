<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "categories";

    protected $primaryKey = "CategoryID";

    public $incrementing = false;

    protected $keyType = 'integer';

    protected $fillable = ['name', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    protected static function booted()
    {
        static::deleting(function (Category $category) {
            if (!$category->isForceDeleting()) {
                $category->is_active = false;
                $category->saveQuietly();
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'CategoryID');
    }
}

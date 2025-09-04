<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "brands";

    protected $primaryKey = "BrandID";

    public $incrementing = false;

    protected $keyType = 'integer';

    protected $fillable = ['name', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    protected static function booted()
    {
        static::deleting(function (Brand $brand) {
            // Only update the status if it's a soft delete, not a force delete.
            if (! $brand->isForceDeleting()) {
                $brand->is_active = false;
                $brand->saveQuietly(); // avoid triggering events again
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

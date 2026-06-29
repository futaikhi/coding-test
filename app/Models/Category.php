<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['name', 'is_active', 'attributes'];

    // Cast tipe data agar otomatis menjadi array/object saat dibaca di Vue
    protected $casts = [
        'is_active' => 'boolean',
        'attributes' => 'array',
    ];

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }
}
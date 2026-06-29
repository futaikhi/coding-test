<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Category extends Model implements Auditable
{
    use HasUuids, SoftDeletes, AuditableTrait;

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
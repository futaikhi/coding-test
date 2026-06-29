<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMutation extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['material_id', 'type', 'quantity', 'note'];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
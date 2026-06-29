<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class StockMutation extends Model implements Auditable
{
    use HasUuids, SoftDeletes, AuditableTrait;

    protected $fillable = ['material_id', 'type', 'quantity', 'note'];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
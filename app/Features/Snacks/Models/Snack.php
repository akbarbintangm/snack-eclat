<?php

namespace App\Features\Snacks\Models;

use App\Features\Transactions\Models\TransactionDetail;
use App\Support\Models\Concerns\HasAuditFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Snack extends Model
{
    use HasAuditFields;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }
}

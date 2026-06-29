<?php

namespace App\Features\Transactions\Models;

use App\Support\Models\Concerns\HasAuditFields;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasAuditFields;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'reference_no',
        'transaction_date',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date:Y-m-d',
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

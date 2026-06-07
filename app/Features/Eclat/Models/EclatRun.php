<?php

namespace App\Features\Eclat\Models;

use App\Support\Models\Concerns\HasAuditFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EclatRun extends Model
{
    use HasAuditFields;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'run_code',
        'min_support',
        'min_confidence',
        'total_transactions',
        'total_items',
        'frequent_itemset_count',
        'rule_count',
        'execution_time_ms',
        'tid_list',
        'frequent_itemsets',
        'notes',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'min_support' => 'float',
            'min_confidence' => 'float',
            'total_transactions' => 'integer',
            'total_items' => 'integer',
            'frequent_itemset_count' => 'integer',
            'rule_count' => 'integer',
            'execution_time_ms' => 'integer',
            'tid_list' => 'array',
            'frequent_itemsets' => 'array',
        ];
    }

    public function results(): HasMany
    {
        return $this->hasMany(HasilEclat::class);
    }
}

<?php

namespace App\Features\Eclat\Models;

use App\Support\Models\Concerns\HasAuditFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HasilEclat extends Model
{
    use HasAuditFields;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'hasil_eclat';

    protected $fillable = [
        'eclat_run_id',
        'combination_item',
        'antecedent_items',
        'consequent_items',
        'transaction_count',
        'support',
        'confidence',
        'lift_ratio',
        'tid_list',
        'recommendation',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'antecedent_items' => 'array',
            'consequent_items' => 'array',
            'transaction_count' => 'integer',
            'support' => 'float',
            'confidence' => 'float',
            'lift_ratio' => 'float',
            'tid_list' => 'array',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(EclatRun::class, 'eclat_run_id');
    }
}

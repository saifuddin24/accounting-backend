<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\HasProfile;

class ChartOfAccount extends Model
{
    use HasFactory, HasProfile;

    protected $fillable = [
        'profile_id',
        'code',
        'name',
        'type', // Asset, Liability, Equity, Income, Expense
        'sub_type',
        'parent_id',
        'normal_balance', // debit, credit
        'description',
        'is_active',
        'is_restricted',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_restricted' => 'boolean',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function journalItems(): HasMany
    {
        return $this->hasMany(JournalItem::class, 'account_id');
    }
}

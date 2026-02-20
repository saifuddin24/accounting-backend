<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\HasProfile;

class JournalEntry extends Model
{
    use HasFactory, HasProfile;

    protected $fillable = [
        'profile_id',
        'fiscal_year_id',
        'entry_number',
        'date',
        'description',
        'reference',
        'total_amount',
        'status', // draft, posted, cancelled
    ];

    protected $casts = [
        'date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(JournalItem::class);
    }
}

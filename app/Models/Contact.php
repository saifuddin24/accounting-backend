<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasProfile;

class Contact extends Model
{
  use HasFactory, SoftDeletes, HasProfile;

  protected $fillable = [
    'profile_id',
    'name',
    'type',
    'email',
    'phone',
    'address',
    'notes',
  ];

  public function journalEntries(): HasMany
  {
    return $this->hasMany(JournalEntry::class);
  }
}

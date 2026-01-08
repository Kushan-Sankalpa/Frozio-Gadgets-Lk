<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'client_id',
        'staff_id',
        'branch_id',
        'date',
        'starts_at',
        'ends_at',
        'total_price',
        'status',
        'notes',
        'block_type',
        'cancel_reson',
        'frequency',
        'description',
        'notes',
        'auto_cancelled',
        'auto_cancelled_at',
        'auto_cancel_reason',
        'placed_by',
        'approved_by',
        'rejected_by',
        'cancelled_by',
         'blocked_by',
    ];

    protected $casts = [
        'date'      => 'date',
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(BookingService::class);
    }

    public function sales()
    {
        return $this->hasMany(BookingSale::class);
    }

    public function isBlockedTime(): bool
    {
        return $this->status === 'blocked_time';
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function placedBy()
    {
        return $this->belongsTo(User::class, 'placed_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
      public function blockedBy()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookTransaction extends Model
{
    protected $fillable = [
        'book_id',
        'transaction_type',
        'amount',
        'transaction_date',
        'reference_number',
        'short_notes',
        'full_details',
        'voucher_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    /**
     * Get the book this transaction belongs to
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the voucher associated with this transaction
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * Get the user who created this transaction
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if this is a deposit
     */
    public function isDeposit(): bool
    {
        return $this->transaction_type === 'deposit';
    }

    /**
     * Check if this is a withdrawal
     */
    public function isWithdrawal(): bool
    {
        return $this->transaction_type === 'withdrawal';
    }

    /**
     * Get display name for the transaction type
     */
    public function getTypeDisplayAttribute(): string
    {
        return $this->isDeposit() ? 'Bank Deposit' : 'Bank Withdrawal';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'bank_account_number',
        'opening_balance',
        'is_cash_book',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_cash_book' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function suspenseAccounts()
    {
        return $this->hasMany(SuspenseAccount::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function payrollEntries()
    {
        return $this->hasMany(PayrollEntry::class);
    }

    public function transactions()
    {
        return $this->hasMany(BookTransaction::class);
    }

    public function deposits()
    {
        return $this->hasMany(BookTransaction::class)->where('transaction_type', 'deposit');
    }

    public function withdrawals()
    {
        return $this->hasMany(BookTransaction::class)->where('transaction_type', 'withdrawal');
    }

    // Helper methods
    public function getBalance()
    {
        $totalDebit = $this->vouchers()->sum('debit');
        $totalCredit = $this->vouchers()->sum('credit');

        return $this->opening_balance + $totalDebit - $totalCredit;
    }

    /**
     * Get balance in cash view (receipts are DR, payments are CR)
     * In cash view: balance = opening + credit - debit
     * This is the opposite of bank view
     */
    public function getCashViewBalance()
    {
        $totalDebit = $this->vouchers()->sum('debit');
        $totalCredit = $this->vouchers()->sum('credit');

        // Cash view: credits (receipts) increase balance, debits (expenses) decrease
        return $this->opening_balance + $totalCredit - $totalDebit;
    }

    /**
     * Get balance up to a specific date (exclusive) for bank view
     */
    public function getBalanceUpToDate($date)
    {
        $totalDebit = $this->vouchers()->where('date', '<', $date)->sum('debit');
        $totalCredit = $this->vouchers()->where('date', '<', $date)->sum('credit');

        return $this->opening_balance + $totalDebit - $totalCredit;
    }

    /**
     * Get cash view balance up to a specific date (exclusive)
     */
    public function getCashViewBalanceUpToDate($date)
    {
        $totalDebit = $this->vouchers()->where('date', '<', $date)->sum('debit');
        $totalCredit = $this->vouchers()->where('date', '<', $date)->sum('credit');

        return $this->opening_balance + $totalCredit - $totalDebit;
    }
}

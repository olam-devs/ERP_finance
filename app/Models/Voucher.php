<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'tenant';

    protected $fillable = [
        'date',
        'student_id',
        'particular_id',
        'book_id',
        'voucher_type',
        'voucher_number',
        'debit',
        'credit',
        'payment_by_receipt_to',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function particular()
    {
        return $this->belongsTo(Particular::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function suspenseAccount()
    {
        return $this->hasOne(SuspenseAccount::class);
    }

    public function expense()
    {
        return $this->hasOne(Expense::class);
    }

    public function payrollEntry()
    {
        return $this->hasOne(PayrollEntry::class);
    }

    public function bankTransaction()
    {
        return $this->hasOne(BankTransaction::class);
    }

    // Auto-generate voucher number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($voucher) {
            if (!$voucher->voucher_number) {
                $lastVoucher = static::where('voucher_type', $voucher->voucher_type)
                    ->latest('id')
                    ->first();

                $lastNumber = $lastVoucher ? (int) substr($lastVoucher->voucher_number, -6) : 0;
                $newNumber = $lastNumber + 1;

                $prefix = strtoupper(substr($voucher->voucher_type, 0, 3));
                $voucher->voucher_number = $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
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
        'staff_id',
        'position',
        'department',
        'monthly_salary',
        'phone',
        'email',
        'bank_name',
        'bank_account',
        'date_joined',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'monthly_salary' => 'decimal:2',
        'date_joined' => 'date',
    ];

    // Relationships
    public function payrollEntries()
    {
        return $this->hasMany(PayrollEntry::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

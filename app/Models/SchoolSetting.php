<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     * Using default mysql connection for single-school setup
     *
     * @var string|null
     */
    protected $connection = 'mysql';

    protected $fillable = [
        'school_name',
        'po_box',
        'region',
        'phone',
        'email',
        'logo_path',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
    ];

    public static function getSettings()
    {
        return static::first() ?? static::create([
            'school_name' => 'School Name',
            'po_box' => '',
            'region' => '',
            'phone' => '',
            'email' => '',
        ]);
    }
}

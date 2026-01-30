<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Particular;
use App\Models\Voucher;
use App\Models\Book;
use App\Services\ActivityLogger;
use App\Traits\HasSchoolContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HeadmasterController extends Controller
{
    use HasSchoolContext;

    protected ActivityLogger $activityLogger;

    public function __construct(ActivityLogger $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    /**
     * Log headmaster action.
     */
    protected function logAction(string $action, string $description): void
    {
        $schoolId = $this->getSchoolId();
        $headmasterId = session('headmaster_id');
        $headmasterName = session('headmaster_name');

        if ($schoolId && $headmasterId) {
            $this->activityLogger->logHeadmasterPortalAction(
                $schoolId,
                $headmasterId,
                $headmasterName ?? 'Unknown',
                $action,
                $description
            );
        }
    }

    /**
     * Show headmaster dashboard.
     */
    public function dashboard()
    {
        $settings = \App\Models\SchoolSetting::getSettings();
        
        // Get summary statistics (same as accountant dashboard)
        $totalStudents = Student::count();
        $totalBooks = Book::where('is_active', true)->count();
        $totalParticulars = Particular::count();
        
        // Fee collection statistics
        $totalFeesExpected = Voucher::where('voucher_type', 'debit')
            ->sum('amount');
        $totalFeesCollected = Voucher::where('voucher_type', 'credit')
            ->sum('amount');
        $collectionRate = $totalFeesExpected > 0 
            ? ($totalFeesCollected / $totalFeesExpected) * 100 
            : 0;

        // Recent transactions
        $recentTransactions = Voucher::with(['student', 'particular', 'book'])
            ->latest()
            ->take(10)
            ->get();

        return view('headmaster.dashboard', compact(
            'settings',
            'totalStudents',
            'totalBooks',
            'totalParticulars',
            'totalFeesExpected',
            'totalFeesCollected',
            'collectionRate',
            'recentTransactions'
        ));
    }

    /**
     * Show student ledgers.
     */
    public function ledgers()
    {
        $settings = \App\Models\SchoolSetting::getSettings();
        return view('headmaster.ledgers', compact('settings'));
    }

    /**
     * Show particular ledgers.
     */
    public function particularLedger()
    {
        $settings = \App\Models\SchoolSetting::getSettings();
        return view('headmaster.particular-ledger', compact('settings'));
    }

    /**
     * Show overdue amounts.
     */
    public function overdue()
    {
        $settings = \App\Models\SchoolSetting::getSettings();
        return view('headmaster.overdue', compact('settings'));
    }

    /**
     * Show invoices.
     */
    public function invoices()
    {
        $settings = \App\Models\SchoolSetting::getSettings();
        return view('headmaster.invoices', compact('settings'));
    }
}

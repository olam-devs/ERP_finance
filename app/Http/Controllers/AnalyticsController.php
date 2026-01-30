<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Voucher;
use App\Models\Book;
use App\Models\Particular;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function getAnalytics($period = 'month')
    {
        [$dateFrom, $dateTo] = $this->getPeriodDates($period);

        // Total Expected Fees (Sales from particular_student)
        $totalExpected = DB::table('particular_student')->sum('sales');

        // Total Collections (Credits from vouchers where voucher_type = 'Receipt')
        // This includes regular receipts AND resolved suspense accounts (which create Receipt vouchers)
        $totalCollections = Voucher::whereBetween('date', [$dateFrom, $dateTo])
            ->where('voucher_type', 'Receipt')
            ->sum('credit');

        // Total Expenses (Credits from vouchers where voucher_type = 'Payment')
        $totalExpenses = Voucher::whereBetween('date', [$dateFrom, $dateTo])
            ->where('voucher_type', 'Payment')
            ->sum('credit');

        // Active students
        $activeStudents = Student::where('status', 'active')->count();

        // Outstanding balances (Total Sales - Total Credits)
        $outstandingBalance = DB::table('particular_student')
            ->sum(DB::raw('sales - credit'));

        // Collection trend for line graph (daily for week/month, monthly for year)
        $collectionTrend = $this->getCollectionTrend($period, $dateFrom, $dateTo);

        // Books distribution for pie chart (filtered by period)
        $booksDistribution = $this->getBooksDistribution($dateFrom, $dateTo);

        // Particulars data for bar chart
        $particularsData = $this->getParticularsData();

        // Student payment completion status by class
        $classStats = $this->getClassPaymentStats();

        return response()->json([
            'period' => $period,
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
            'summary' => [
                'total_expected' => $totalExpected,
                'total_collections' => $totalCollections,
                'total_expenses' => $totalExpenses,
                'net_income' => $totalCollections - $totalExpenses,
                'active_students' => $activeStudents,
                'outstanding_balance' => $outstandingBalance,
            ],
            'collection_trend' => $collectionTrend,
            'books_distribution' => $booksDistribution,
            'particulars_data' => $particularsData,
            'class_stats' => $classStats,
        ]);
    }

    private function getPeriodDates($period)
    {
        $now = now();

        return match($period) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'weekly', 'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'monthly', 'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'yearly', 'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }

    private function getCollectionTrend($period, $dateFrom, $dateTo)
    {
        if ($period === 'today') {
            // Hourly for today
            $collections = Voucher::whereBetween('date', [$dateFrom, $dateTo])
                ->where('voucher_type', 'Receipt')
                ->selectRaw('HOUR(created_at) as hour, SUM(credit) as amount')
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            $trend = [];
            for ($i = 0; $i < 24; $i++) {
                $collection = $collections->where('hour', $i)->first();
                $trend[] = [
                    'label' => sprintf('%02d:00', $i),
                    'expected' => 0, // We don't track hourly expectations
                    'amount' => $collection ? (float)$collection->amount : 0,
                ];
            }
            return $trend;
        }

        if (in_array($period, ['weekly', 'week', 'monthly', 'month'])) {
            // Daily
            $collections = Voucher::whereBetween('date', [$dateFrom, $dateTo])
                ->where('voucher_type', 'Receipt')
                ->select(DB::raw('DATE(date) as date'), DB::raw('SUM(credit) as amount'))
                ->groupBy(DB::raw('DATE(date)'))
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $trend = [];
            $current = $dateFrom->copy();
            while ($current <= $dateTo) {
                $dateKey = $current->toDateString();
                $collection = $collections->get($dateKey);

                $trend[] = [
                    'label' => $current->format('M d'),
                    'expected' => 0,
                    'amount' => $collection ? (float)$collection->amount : 0,
                ];
                $current->addDay();
            }
            return $trend;
        }

        // Yearly - monthly aggregation
        $collections = Voucher::whereBetween('date', [$dateFrom, $dateTo])
            ->where('voucher_type', 'Receipt')
            ->selectRaw('MONTH(date) as month, SUM(credit) as amount')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $trend = [];
        for ($i = 1; $i <= 12; $i++) {
            $collection = $collections->get($i);
            $trend[] = [
                'label' => date('M', mktime(0, 0, 0, $i, 1)),
                'expected' => 0,
                'amount' => $collection ? (float)$collection->amount : 0,
            ];
        }
        return $trend;
    }

    private function getBooksDistribution($dateFrom, $dateTo)
    {
        $books = Book::all();
        $distribution = [];

        foreach ($books as $book) {
            // Get collections (credits from receipts) for this book in the period
            $collections = Voucher::where('book_id', $book->id)
                ->where('voucher_type', 'Receipt')
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->sum('credit');

            if ($collections > 0) {
                $distribution[] = [
                    'name' => $book->name,
                    'amount' => $collections,
                ];
            }
        }

        return $distribution;
    }

    private function getParticularsData()
    {
        $particulars = Particular::where('is_active', true)->get();
        $data = [];

        foreach ($particulars as $particular) {
            // Total expected (sales) for this particular
            $expected = DB::table('particular_student')
                ->where('particular_id', $particular->id)
                ->sum('sales');

            // Total collected (credits) for this particular
            $collected = DB::table('particular_student')
                ->where('particular_id', $particular->id)
                ->sum('credit');

            if ($expected > 0 || $collected > 0) {
                $data[] = [
                    'particular_id' => $particular->id,
                    'particular_name' => $particular->name,
                    'expected' => $expected,
                    'collected' => $collected,
                    'outstanding' => $expected - $collected,
                ];
            }
        }

        return $data;
    }

    private function getClassPaymentStats()
    {
        $classes = SchoolClass::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $stats = [];

        foreach ($classes as $class) {
            $students = $class->students()->where('status', 'active')->get();

            if ($students->isEmpty()) {
                continue;
            }

            $totalStudents = $students->count();
            $completedStudents = 0;
            $totalExpected = 0;
            $totalCollected = 0;

            foreach ($students as $student) {
                $studentExpected = DB::table('particular_student')
                    ->where('student_id', $student->id)
                    ->sum('sales');

                $studentCollected = DB::table('particular_student')
                    ->where('student_id', $student->id)
                    ->sum('credit');

                $totalExpected += $studentExpected;
                $totalCollected += $studentCollected;

                // Student is "completed" if they've paid everything
                if ($studentExpected > 0 && $studentCollected >= $studentExpected) {
                    $completedStudents++;
                }
            }

            $collectionRate = $totalExpected > 0
                ? ($totalCollected / $totalExpected) * 100
                : 0;

            $stats[] = [
                'class_name' => $class->name,
                'total_students' => $totalStudents,
                'completed_students' => $completedStudents,
                'expected_amount' => $totalExpected,
                'collected_amount' => $totalCollected,
                'collection_rate' => round($collectionRate, 1),
            ];
        }

        return $stats;
    }

    // Add helper endpoint for getting all particulars
    public function getParticulars()
    {
        $particulars = Particular::where('is_active', true)
            ->select('id', 'name')
            ->get();

        return response()->json($particulars);
    }

    // Add helper endpoint for student payment summary
    public function getStudentPaymentSummary($studentId)
    {
        $student = Student::with(['schoolClass', 'particulars'])->findOrFail($studentId);

        $particulars = $student->particulars->map(function($particular) {
            return [
                'particular_id' => $particular->id,
                'particular_name' => $particular->name,
                'sales' => $particular->pivot->sales ?? 0,
                'debit' => $particular->pivot->debit ?? 0,
                'credit' => $particular->pivot->credit ?? 0,
                'balance' => ($particular->pivot->sales ?? 0) + ($particular->pivot->debit ?? 0) - ($particular->pivot->credit ?? 0),
                'deadline' => $particular->pivot->deadline,
            ];
        });

        $totalSales = $particulars->sum('sales');
        $totalPaid = $particulars->sum('credit');
        $totalBalance = $particulars->sum('balance');

        $totalAssignments = $student->particulars->count();
        $completedAssignments = $student->particulars->filter(function($particular) {
            $sales = $particular->pivot->sales ?? 0;
            $credit = $particular->pivot->credit ?? 0;
            return $sales > 0 && $credit >= $sales;
        })->count();

        return response()->json([
            'name' => $student->name,
            'student_reg_no' => $student->student_reg_no,
            'class' => $student->schoolClass->name ?? $student->class,
            'total_expected' => (float)$totalSales,
            'total_collected' => (float)$totalPaid,
            'collection_rate' => $totalSales > 0 ? round(($totalPaid / $totalSales) * 100, 1) : 0,
            'total_assignments' => $totalAssignments,
            'completed_assignments' => $completedAssignments,
        ]);
    }

    // Custom date range analytics
    public function getCustomAnalytics(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $dateFrom = \Carbon\Carbon::parse($validated['from_date'])->startOfDay();
        $dateTo = \Carbon\Carbon::parse($validated['to_date'])->endOfDay();

        // Total Expected Fees (Sales from particular_student)
        $totalExpected = DB::table('particular_student')->sum('sales');

        // Total Collections (Credits from vouchers where voucher_type = 'Receipt')
        $totalCollections = Voucher::whereBetween('date', [$dateFrom, $dateTo])
            ->where('voucher_type', 'Receipt')
            ->sum('credit');

        // Total Expenses (Credits from vouchers where voucher_type = 'Payment')
        $totalExpenses = Voucher::whereBetween('date', [$dateFrom, $dateTo])
            ->where('voucher_type', 'Payment')
            ->sum('credit');

        // Active students
        $activeStudents = Student::where('status', 'active')->count();

        // Outstanding balances (Total Sales - Total Credits)
        $outstandingBalance = DB::table('particular_student')
            ->sum(DB::raw('sales - credit'));

        // Collection trend for line graph
        $collectionTrend = $this->getCustomCollectionTrend($dateFrom, $dateTo);

        // Books distribution for pie chart (filtered by period)
        $booksDistribution = $this->getBooksDistribution($dateFrom, $dateTo);

        // Particulars data for bar chart
        $particularsData = $this->getParticularsData();

        // Student payment completion status by class
        $classStats = $this->getClassPaymentStats();

        return response()->json([
            'period' => 'custom',
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
            'summary' => [
                'total_expected' => $totalExpected,
                'total_collections' => $totalCollections,
                'total_expenses' => $totalExpenses,
                'net_income' => $totalCollections - $totalExpenses,
                'active_students' => $activeStudents,
                'outstanding_balance' => $outstandingBalance,
            ],
            'collection_trend' => $collectionTrend,
            'books_distribution' => $booksDistribution,
            'particulars_data' => $particularsData,
            'class_stats' => $classStats,
        ]);
    }

    private function getCustomCollectionTrend($dateFrom, $dateTo)
    {
        $daysDiff = $dateFrom->diffInDays($dateTo);

        // Daily aggregation for ranges up to 60 days
        if ($daysDiff <= 60) {
            $collections = Voucher::whereBetween('date', [$dateFrom, $dateTo])
                ->where('voucher_type', 'Receipt')
                ->select(DB::raw('DATE(date) as date'), DB::raw('SUM(credit) as amount'))
                ->groupBy(DB::raw('DATE(date)'))
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $trend = [];
            $current = $dateFrom->copy();
            while ($current <= $dateTo) {
                $dateKey = $current->toDateString();
                $collection = $collections->get($dateKey);

                $trend[] = [
                    'label' => $current->format('M d'),
                    'expected' => 0,
                    'amount' => $collection ? (float)$collection->amount : 0,
                ];
                $current->addDay();
            }
            return $trend;
        }

        // Monthly aggregation for longer ranges
        $collections = Voucher::whereBetween('date', [$dateFrom, $dateTo])
            ->where('voucher_type', 'Receipt')
            ->selectRaw('YEAR(date) as year, MONTH(date) as month, SUM(credit) as amount')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->keyBy(function($item) {
                return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            });

        $trend = [];
        $current = $dateFrom->copy()->startOfMonth();
        while ($current <= $dateTo) {
            $key = $current->format('Y-m');
            $collection = $collections->get($key);

            $trend[] = [
                'label' => $current->format('M Y'),
                'expected' => 0,
                'amount' => $collection ? (float)$collection->amount : 0,
            ];
            $current->addMonth();
        }
        return $trend;
    }
}

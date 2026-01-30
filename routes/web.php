<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\FeeItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ParticularController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\SuspenseAccountController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\BankAPIController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\BookTransactionController;
use App\Http\Controllers\ScholarshipController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('accountant.dashboard');
});

// Public Bank Webhook Endpoint (no auth required - called by bank)
Route::post('/api/bank-webhook', [BankAPIController::class, 'webhook'])->name('api.bank-webhook');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/accountant-dashboard', function() {
        $settings = \App\Models\SchoolSetting::getSettings();
        return view('admin.accountant.dashboard', compact('settings'));
    })->name('accountant.dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['check.role:superadmin,accountant'])->prefix('accountant')->name('accountant.')->group(function () {
        // Dedicated Module Pages
        Route::get('/books', function() {
            $settings = \App\Models\SchoolSetting::getSettings();
            return view('admin.accountant.modules.books', compact('settings'));
        })->name('books');

        Route::get('/particulars', function() {
            $settings = \App\Models\SchoolSetting::getSettings();
            return view('admin.accountant.modules.particulars', compact('settings'));
        })->name('particulars');

        Route::get('/fee-entry', function() {
            $settings = \App\Models\SchoolSetting::getSettings();
            return view('admin.accountant.modules.fee-entry', compact('settings'));
        })->name('fee-entry');

        Route::get('/ledgers', function() {
            $settings = \App\Models\SchoolSetting::getSettings();
            return view('admin.accountant.modules.ledgers', compact('settings'));
        })->name('ledgers');

        Route::get('/particular-ledger', function() {
            $settings = \App\Models\SchoolSetting::getSettings();
            return view('admin.accountant.modules.particular-ledger', compact('settings'));
        })->name('particular-ledger');

        Route::get('/overdue', function() {
            $settings = \App\Models\SchoolSetting::getSettings();
            return view('admin.accountant.modules.overdue', compact('settings'));
        })->name('overdue');

        Route::get('/suspense', function() {
            $settings = \App\Models\SchoolSetting::getSettings();
            return view('admin.accountant.modules.suspense', compact('settings'));
        })->name('suspense');

        Route::get('/payroll', function() {
            $settings = \App\Models\SchoolSetting::getSettings();
            return view('admin.accountant.modules.payroll', compact('settings'));
        })->name('payroll');

        Route::get('/bank-api', function() {
            $settings = \App\Models\SchoolSetting::getSettings();
            return view('admin.accountant.modules.bank-api', compact('settings'));
        })->name('bank-api');

        Route::get('/classes', function() {
            $settings = \App\Models\SchoolSetting::getSettings();
            return view('admin.accountant.modules.classes', compact('settings'));
        })->name('classes');

        Route::get('/students', function() {
            $settings = \App\Models\SchoolSetting::getSettings();
            return view('admin.accountant.modules.students', compact('settings'));
        })->name('students');

        Route::get('/sms', [SmsController::class, 'indexAccountant'])->name('sms');
        Route::get('/phone-numbers', [SmsController::class, 'managePhoneNumbersAccountant'])->name('phone-numbers');
        Route::get('/sms-logs', [SmsController::class, 'logsAccountant'])->name('sms-logs');
        Route::post('/sms/send-overdue-reminders', [SmsController::class, 'sendOverdueReminders'])->name('sms.send-overdue-reminders');

        // Headmaster Management
        Route::get('/headmasters', [\App\Http\Controllers\HeadmasterManagementController::class, 'index'])->name('headmasters');
        Route::post('/headmasters', [\App\Http\Controllers\HeadmasterManagementController::class, 'store'])->name('headmasters.store');
        Route::put('/headmasters/{headmaster}', [\App\Http\Controllers\HeadmasterManagementController::class, 'update'])->name('headmasters.update');
        Route::post('/headmasters/{headmaster}/toggle', [\App\Http\Controllers\HeadmasterManagementController::class, 'toggleStatus'])->name('headmasters.toggle');
        Route::delete('/headmasters/{headmaster}', [\App\Http\Controllers\HeadmasterManagementController::class, 'destroy'])->name('headmasters.destroy');

        Route::get('/invoices-page', [LedgerController::class, 'invoicesPage'])->name('invoices-page');

        Route::get('/expenses', function() {
            $settings = \App\Models\SchoolSetting::getSettings();
            return view('admin.accountant.modules.expenses', compact('settings'));
        })->name('expenses');

        Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings');
        Route::put('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');

        // Invoice download routes
        Route::get('/invoices/all-students/pdf', [LedgerController::class, 'exportAllStudentsInvoicesPdf'])->name('invoices.all-students.pdf');
        Route::get('/invoices/class/{className}/pdf', [LedgerController::class, 'exportClassInvoicesPdf'])->name('invoices.class.pdf');
        Route::get('/invoices/student/{studentId}/pdf', [LedgerController::class, 'exportStudentInvoicePdf'])->name('invoices.student.pdf');
    });

    Route::middleware(['check.role:superadmin,accountant'])->group(function () {
        Route::resource('invoices', InvoiceController::class);
        Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
        Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPDF'])->name('invoices.pdf');

        Route::resource('payments', PaymentController::class);
        Route::post('payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
        Route::get('api/invoice-balance', [PaymentController::class, 'getInvoiceBalance'])->name('api.invoice.balance');

        Route::resource('students', StudentController::class);

        Route::resource('fee-items', FeeItemController::class);

        // Students API routes
        Route::get('api/students', [StudentController::class, 'apiIndex'])->name('api.students.index');
        Route::get('api/students/search', [StudentController::class, 'searchStudents'])->name('api.students.search');
        Route::get('api/students/{studentId}/payment-summary', [StudentController::class, 'getStudentPaymentSummary'])->name('api.students.payment-summary');
        Route::get('api/classes', [StudentController::class, 'apiClasses'])->name('api.classes.index');

        // Student CSV Import routes
        Route::get('api/students/csv/template', [StudentController::class, 'downloadStudentTemplate'])->name('api.students.csv.template');
        Route::post('api/students/csv/upload', [StudentController::class, 'uploadStudentCsv'])->name('api.students.csv.upload');

        // Student Promotion routes
        Route::get('/student-promotion', [StudentController::class, 'promotionPage'])->name('students.promotion-page');
        Route::get('api/students/for-promotion', [StudentController::class, 'getStudentsForPromotion'])->name('api.students.for-promotion');
        Route::post('api/students/promote', [StudentController::class, 'promoteStudents'])->name('api.students.promote');
        Route::get('api/students/{studentId}/particulars', [StudentController::class, 'getStudentParticulars'])->name('api.students.particulars');
        Route::get('api/students/{studentId}/particulars/{particularId}/details', [StudentController::class, 'getStudentParticularDetails'])->name('api.students.particular-details');

        // School Classes routes
        Route::get('api/school-classes', [SchoolClassController::class, 'index'])->name('api.school-classes.index');
        Route::post('api/school-classes', [SchoolClassController::class, 'store'])->name('api.school-classes.store');
        Route::get('api/school-classes/{id}', [SchoolClassController::class, 'show'])->name('api.school-classes.show');
        Route::put('api/school-classes/{id}', [SchoolClassController::class, 'update'])->name('api.school-classes.update');
        Route::delete('api/school-classes/{id}', [SchoolClassController::class, 'destroy'])->name('api.school-classes.destroy');
        Route::get('api/school-classes-dropdown', [SchoolClassController::class, 'apiClasses'])->name('api.school-classes.dropdown');
        Route::get('api/classes', [SchoolClassController::class, 'index'])->name('api.classes.index'); // Alias for ledgers page

        // Books routes
        Route::get('api/books', [BookController::class, 'index'])->name('api.books.index');
        Route::post('api/books', [BookController::class, 'store'])->name('api.books.store');
        Route::get('api/books/{id}', [BookController::class, 'show'])->name('api.books.show');
        Route::put('api/books/{id}', [BookController::class, 'update'])->name('api.books.update');
        Route::delete('api/books/{id}', [BookController::class, 'destroy'])->name('api.books.destroy');
        Route::post('api/books/create-cash-book', [BookController::class, 'createCashBook'])->name('api.books.cash-book');

        // Particulars routes
        Route::get('api/particulars', [ParticularController::class, 'index'])->name('api.particulars.index');
        Route::post('api/particulars', [ParticularController::class, 'store'])->name('api.particulars.store');
        Route::get('api/particulars/{id}', [ParticularController::class, 'show'])->name('api.particulars.show');
        Route::put('api/particulars/{id}', [ParticularController::class, 'update'])->name('api.particulars.update');
        Route::delete('api/particulars/{id}', [ParticularController::class, 'destroy'])->name('api.particulars.destroy');
        Route::post('api/particulars/{id}/assign-students', [ParticularController::class, 'assignStudents'])->name('api.particulars.assign');
        Route::post('api/particulars/{id}/bulk-opening-balance', [ParticularController::class, 'bulkOpeningBalance'])->name('api.particulars.bulk');
        Route::get('api/particulars/{id}/existing-assignments', [ParticularController::class, 'getExistingAssignments'])->name('api.particulars.existing-assignments');
        Route::get('api/particulars/{id}/students-for-new-assignment', [ParticularController::class, 'getStudentsForNewAssignment'])->name('api.particulars.students-for-new');
        Route::post('api/particulars/{particularId}/assignments', [ParticularController::class, 'createAssignment'])->name('api.particulars.create-assignment');
        Route::put('api/particulars/{particularId}/assignments/{studentId}', [ParticularController::class, 'updateAssignment'])->name('api.particulars.update-assignment');
        Route::delete('api/particulars/{particularId}/assignments/{studentId}', [ParticularController::class, 'deleteAssignment'])->name('api.particulars.delete-assignment');

        // Academic Years routes
        Route::get('api/academic-years', [AcademicYearController::class, 'index'])->name('api.academic-years.index');
        Route::get('api/academic-years/active', [AcademicYearController::class, 'active'])->name('api.academic-years.active');
        Route::get('api/academic-years/current', [AcademicYearController::class, 'current'])->name('api.academic-years.current');
        Route::post('api/academic-years', [AcademicYearController::class, 'store'])->name('api.academic-years.store');
        Route::put('api/academic-years/{id}', [AcademicYearController::class, 'update'])->name('api.academic-years.update');
        Route::post('api/academic-years/{id}/set-current', [AcademicYearController::class, 'setCurrent'])->name('api.academic-years.set-current');
        Route::delete('api/academic-years/{id}', [AcademicYearController::class, 'destroy'])->name('api.academic-years.destroy');

        // Vouchers routes
        Route::get('api/vouchers', [VoucherController::class, 'index'])->name('api.vouchers.index');
        Route::post('api/vouchers', [VoucherController::class, 'store'])->name('api.vouchers.store');
        Route::get('api/vouchers/{id}', [VoucherController::class, 'show'])->name('api.vouchers.show');
        Route::put('api/vouchers/{id}', [VoucherController::class, 'update'])->name('api.vouchers.update');
        Route::delete('api/vouchers/{id}', [VoucherController::class, 'destroy'])->name('api.vouchers.destroy');
        Route::get('api/vouchers/search/student', [VoucherController::class, 'searchStudent'])->name('api.vouchers.search');

        // Ledgers routes
        Route::get('api/ledgers/student/{studentId}', [LedgerController::class, 'studentLedger'])->name('api.ledgers.student');
        Route::get('api/ledgers/class/{classId}', [LedgerController::class, 'classLedger'])->name('api.ledgers.class');
        Route::get('api/ledgers/book/{bookId}', [LedgerController::class, 'bookLedger'])->name('api.ledgers.book');
        Route::get('api/ledgers/particular/{particularId}', [LedgerController::class, 'particularLedger'])->name('api.ledgers.particular');

        // Ledger Export routes (PDF & CSV)
        Route::get('api/ledgers/student/{studentId}/pdf', [LedgerController::class, 'exportStudentLedgerPdf'])->name('api.ledgers.student.pdf');
        Route::get('api/ledgers/student/{studentId}/csv', [LedgerController::class, 'exportStudentLedgerCsv'])->name('api.ledgers.student.csv');
        Route::get('api/ledgers/class/{classId}/pdf', [LedgerController::class, 'exportClassLedgerPdf'])->name('api.ledgers.class.pdf');
        Route::get('api/ledgers/class/{classId}/csv', [LedgerController::class, 'exportClassLedgerCsv'])->name('api.ledgers.class.csv');
        Route::get('api/ledgers/book/{bookId}/pdf', [LedgerController::class, 'exportBookLedgerPdf'])->name('api.ledgers.book.pdf');
        Route::get('api/ledgers/book/{bookId}/csv', [LedgerController::class, 'exportBookLedgerCsv'])->name('api.ledgers.book.csv');
        Route::get('api/ledgers/all-students/pdf', [LedgerController::class, 'exportAllStudentsLedgersPdf'])->name('api.ledgers.all-students.pdf');
        Route::get('api/ledgers/particular/{particularId}/pdf', [LedgerController::class, 'exportParticularLedgerPdf'])->name('api.ledgers.particular.pdf');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/income-statement', [ReportController::class, 'incomeStatement'])->name('reports.income-statement');
        Route::get('reports/balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
        Route::get('reports/trial-balance', [ReportController::class, 'trialBalance'])->name('reports.trial-balance');
        Route::get('reports/fee-collection', [ReportController::class, 'feeCollection'])->name('reports.fee-collection');
        Route::get('reports/outstanding-balances', [ReportController::class, 'outstandingBalances'])->name('reports.outstanding');
        Route::get('reports/student-statement/{studentId?}', [ReportController::class, 'studentStatement'])->name('reports.student-statement');

        // SMS routes
        Route::get('sms', [SmsController::class, 'index'])->name('sms.index');
        Route::get('sms/logs', [SmsController::class, 'logs'])->name('sms.logs');
        Route::post('sms/send', [SmsController::class, 'sendSms'])->name('sms.send');
        Route::get('sms/balance', [SmsController::class, 'checkBalance'])->name('sms.balance');
        Route::get('sms/manage-phones', [SmsController::class, 'managePhoneNumbers'])->name('sms.manage-phones');
        Route::get('sms/download-template', [SmsController::class, 'downloadPhoneTemplate'])->name('sms.download-template');
        Route::post('sms/upload-phones', [SmsController::class, 'uploadPhoneNumbers'])->name('sms.upload-phones');
        Route::put('sms/student/{studentId}/phone', [SmsController::class, 'updatePhoneNumber'])->name('sms.update-phone');

        // SMS Template routes
        Route::get('sms/templates', [SmsController::class, 'getTemplates'])->name('sms.templates');
        Route::post('sms/templates', [SmsController::class, 'saveTemplate'])->name('sms.save-template');
        Route::delete('sms/templates/{templateId}', [SmsController::class, 'deleteTemplate'])->name('sms.delete-template');

        // SMS API routes
        Route::get('api/sms/students/class/{className}', [SmsController::class, 'getStudentsByClass'])->name('api.sms.students.class');
        Route::get('api/sms/students/search', [SmsController::class, 'searchStudents'])->name('api.sms.students.search');
        Route::get('api/sms/credits', [SmsController::class, 'getSmsCredits'])->name('api.sms.credits');
        Route::get('api/sms/debug-school', [SmsController::class, 'debugSchool'])->name('api.sms.debug-school');
        Route::post('api/sms/calculate-count', [SmsController::class, 'calculateMessageSms'])->name('api.sms.calculate-count');

        // Suspense Account routes
        Route::get('api/suspense-accounts', [SuspenseAccountController::class, 'index'])->name('api.suspense.index');
        Route::post('api/suspense-accounts', [SuspenseAccountController::class, 'store'])->name('api.suspense.store');
        Route::get('api/suspense-accounts/{id}', [SuspenseAccountController::class, 'show'])->name('api.suspense.show');
        Route::put('api/suspense-accounts/{id}', [SuspenseAccountController::class, 'update'])->name('api.suspense.update');
        Route::delete('api/suspense-accounts/{id}', [SuspenseAccountController::class, 'destroy'])->name('api.suspense.destroy');
        Route::post('api/suspense-accounts/{id}/resolve', [SuspenseAccountController::class, 'resolve'])->name('api.suspense.resolve');
        Route::get('api/suspense-accounts/summary/unresolved', [SuspenseAccountController::class, 'unresolvedSummary'])->name('api.suspense.summary');

        // Payroll - Staff routes
        Route::get('api/staff', [PayrollController::class, 'indexStaff'])->name('api.staff.index');
        Route::post('api/staff', [PayrollController::class, 'storeStaff'])->name('api.staff.store');
        Route::get('api/staff/{id}', [PayrollController::class, 'showStaff'])->name('api.staff.show');
        Route::put('api/staff/{id}', [PayrollController::class, 'updateStaff'])->name('api.staff.update');
        Route::delete('api/staff/{id}', [PayrollController::class, 'destroyStaff'])->name('api.staff.destroy');
        Route::get('api/staff/{id}/payment-history', [PayrollController::class, 'staffPaymentHistory'])->name('api.staff.payment-history');
        Route::get('api/staff/csv/template', [PayrollController::class, 'downloadStaffTemplate'])->name('api.staff.csv.template');
        Route::post('api/staff/csv/upload', [PayrollController::class, 'uploadStaffCsv'])->name('api.staff.csv.upload');

        // Payroll - Payroll Entry routes
        Route::get('api/payroll', [PayrollController::class, 'indexPayroll'])->name('api.payroll.index');
        Route::post('api/payroll', [PayrollController::class, 'storePayroll'])->name('api.payroll.store');
        Route::get('api/payroll/{id}', [PayrollController::class, 'showPayroll'])->name('api.payroll.show');
        Route::put('api/payroll/{id}', [PayrollController::class, 'updatePayroll'])->name('api.payroll.update');
        Route::delete('api/payroll/{id}', [PayrollController::class, 'destroyPayroll'])->name('api.payroll.destroy');
        Route::get('api/payroll/reports/monthly', [PayrollController::class, 'monthlyReport'])->name('api.payroll.monthly-report');

        // Overdue amounts route
        Route::get('api/overdue-amounts', [DashboardController::class, 'getOverdueAmounts'])->name('api.overdue-amounts');

        // Bank API Integration routes
        Route::get('api/bank-transactions', [\App\Http\Controllers\BankAPIController::class, 'index'])->name('api.bank-transactions.index');
        Route::get('api/bank-transactions/{id}', [\App\Http\Controllers\BankAPIController::class, 'show'])->name('api.bank-transactions.show');
        Route::post('api/bank-transactions/{id}/retry', [\App\Http\Controllers\BankAPIController::class, 'retry'])->name('api.bank-transactions.retry');
        Route::get('api/bank-api-settings', [\App\Http\Controllers\BankAPIController::class, 'getSettings'])->name('api.bank-api-settings.get');
        Route::put('api/bank-api-settings', [\App\Http\Controllers\BankAPIController::class, 'updateSettings'])->name('api.bank-api-settings.update');
        Route::post('api/bank-webhook/simulate', [\App\Http\Controllers\BankAPIController::class, 'simulate'])->name('api.bank-webhook.simulate');

        // Analytics routes
        Route::get('api/analytics/custom', [\App\Http\Controllers\AnalyticsController::class, 'getCustomAnalytics'])->name('api.analytics.custom');
        Route::get('api/analytics/{period}', [\App\Http\Controllers\AnalyticsController::class, 'getAnalytics'])->name('api.analytics');
        Route::get('api/students/{studentId}/payment-summary', [\App\Http\Controllers\AnalyticsController::class, 'getStudentPaymentSummary'])->name('api.students.payment-summary');
        Route::get('api/students/search', [\App\Http\Controllers\StudentController::class, 'search'])->name('api.students.search');

        // Bank Accounts routes
        Route::get('api/bank-accounts', [\App\Http\Controllers\BankAccountController::class, 'index'])->name('api.bank-accounts.index');
        Route::post('api/bank-accounts', [\App\Http\Controllers\BankAccountController::class, 'store'])->name('api.bank-accounts.store');
        Route::put('api/bank-accounts/{id}', [\App\Http\Controllers\BankAccountController::class, 'update'])->name('api.bank-accounts.update');
        Route::delete('api/bank-accounts/{id}', [\App\Http\Controllers\BankAccountController::class, 'destroy'])->name('api.bank-accounts.destroy');

        // Expense routes
        Route::get('api/expenses/summary', [\App\Http\Controllers\ExpenseController::class, 'summary'])->name('api.expenses.summary');
        Route::get('api/expenses/analytics', [\App\Http\Controllers\ExpenseController::class, 'analytics'])->name('api.expenses.analytics');
        Route::get('api/expenses', [\App\Http\Controllers\ExpenseController::class, 'index'])->name('api.expenses.index');
        Route::post('api/expenses', [\App\Http\Controllers\ExpenseController::class, 'store'])->name('api.expenses.store');
        Route::get('api/expenses/{id}', [\App\Http\Controllers\ExpenseController::class, 'show'])->name('api.expenses.show');
        Route::put('api/expenses/{id}', [\App\Http\Controllers\ExpenseController::class, 'update'])->name('api.expenses.update');
        Route::delete('api/expenses/{id}', [\App\Http\Controllers\ExpenseController::class, 'destroy'])->name('api.expenses.destroy');
        Route::post('api/expenses/{id}/process', [\App\Http\Controllers\ExpenseController::class, 'process'])->name('api.expenses.process');
        Route::post('api/expenses/{id}/cancel', [\App\Http\Controllers\ExpenseController::class, 'cancel'])->name('api.expenses.cancel');

        // Book Transactions (Deposits & Withdrawals) routes
        Route::get('api/book-transactions/{bookId}', [BookTransactionController::class, 'index'])->name('api.book-transactions.index');
        Route::post('api/book-transactions/deposit', [BookTransactionController::class, 'storeDeposit'])->name('api.book-transactions.deposit');
        Route::post('api/book-transactions/withdrawal', [BookTransactionController::class, 'storeWithdrawal'])->name('api.book-transactions.withdrawal');
        Route::get('api/book-transactions/show/{id}', [BookTransactionController::class, 'show'])->name('api.book-transactions.show');
        Route::delete('api/book-transactions/{id}', [BookTransactionController::class, 'destroy'])->name('api.book-transactions.destroy');

        // Scholarship routes
        Route::get('api/scholarships', [ScholarshipController::class, 'index'])->name('api.scholarships.index');
        Route::post('api/scholarships', [ScholarshipController::class, 'store'])->name('api.scholarships.store');
        Route::put('api/scholarships/{id}', [ScholarshipController::class, 'update'])->name('api.scholarships.update');
        Route::post('api/scholarships/{id}/deactivate', [ScholarshipController::class, 'deactivate'])->name('api.scholarships.deactivate');
        Route::get('api/scholarships/student/{studentId}', [ScholarshipController::class, 'studentScholarships'])->name('api.scholarships.student');
        Route::get('api/scholarships/student/{studentId}/details', [ScholarshipController::class, 'studentDetailsForScholarship'])->name('api.scholarships.student.details');
        Route::post('api/scholarships/check', [ScholarshipController::class, 'checkScholarship'])->name('api.scholarships.check');
        Route::get('api/scholarships/summary/by-particular', [ScholarshipController::class, 'summaryByParticular'])->name('api.scholarships.summary');
    });
});

// Parent Portal Routes (using custom session-based auth) - OUTSIDE main auth middleware
Route::prefix('parent')->name('parent.')->middleware('parent.auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\ParentController::class, 'dashboard'])->name('dashboard');
    Route::get('/fees', [App\Http\Controllers\ParentController::class, 'fees'])->name('fees');
    Route::get('/invoices', [App\Http\Controllers\ParentController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/download', [App\Http\Controllers\ParentController::class, 'downloadInvoice'])->name('invoices.download');
    Route::get('/messages', [App\Http\Controllers\ParentController::class, 'messages'])->name('messages');
    Route::get('/notifications', [App\Http\Controllers\ParentController::class, 'notifications'])->name('notifications');
    Route::get('/download-statement', [App\Http\Controllers\ParentController::class, 'downloadStatement'])->name('download-statement');
    Route::post('/change-language', [App\Http\Controllers\ParentController::class, 'changeLanguage'])->name('change-language');
});

require __DIR__.'/auth.php';

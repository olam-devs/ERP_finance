<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Fee Statement - {{ $student->name }}</title>
    <style>
        @page { margin: 15mm 10mm; }
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 10px; border-bottom: 1px solid #333; padding-bottom: 8px; }
        .school-logo { max-width: 60px; max-height: 60px; margin-bottom: 5px; }
        .school-name { font-size: 16px; font-weight: bold; margin-bottom: 3px; }
        .school-info { font-size: 9px; color: #666; }
        .invoice-title { font-size: 14px; font-weight: bold; margin: 8px 0; text-align: center; }
        .student-info { background-color: #f5f5f5; padding: 8px; margin-bottom: 10px; border-left: 3px solid #2196f3; font-size: 9px; line-height: 1.4; }
        .fees-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .fees-table th { background-color: #2196f3; color: white; padding: 5px; font-size: 9px; text-align: left; }
        .fees-table td { border: 1px solid #ddd; padding: 4px 5px; font-size: 9px; }
        .amount { text-align: right; font-weight: bold; }
        .total-row { background-color: #f9f9f9; font-weight: bold; font-size: 10px; }
        .balance-box { background-color: #fff3cd; border: 1px solid #ffc107; padding: 10px; margin: 10px 0; text-align: center; }
        .balance-amount { font-size: 18px; font-weight: bold; color: #d9534f; }
        .overdue { background-color: #f8d7da; color: #721c24; }
        .paid-full { background-color: #d4edda; color: #155724; }
        .footer { margin-top: 15px; padding-top: 8px; border-top: 1px solid #999; font-size: 8px; text-align: center; color: #666; }
        .bank-section { font-size: 9px; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #999; }
        .bank-option { margin-bottom: 4px; padding: 4px; background-color: #f9f9f9; border-left: 2px solid #2196f3; }
        .scholarship-badge { display: inline-block; background: #ffc107; color: #000; padding: 1px 4px; border-radius: 3px; font-size: 7px; font-weight: bold; margin-left: 3px; }
        .scholarship-row { background-color: #fff8e1; }
        .scholarship-summary { background-color: #fff3cd; border: 1px solid #ffc107; padding: 8px; margin: 8px 0; border-radius: 4px; }
        .original-amount { text-decoration: line-through; color: #999; font-size: 8px; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        @if(isset($school) && $school->logo_path && file_exists(storage_path('app/public/' . $school->logo_path)))
            <img src="{{ storage_path('app/public/' . $school->logo_path) }}" alt="School Logo" class="school-logo">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'Darasa Secondary School' }}</div>
        <div class="school-info">
            {{ $school->po_box ?? 'P.O. Box 12345' }} | {{ $school->region ?? 'Dar es Salaam' }} | Tel: {{ $school->phone ?? '+255 123 456 789' }}
        </div>
    </div>

    <div class="invoice-title">FEE STATEMENT</div>

    <!-- Student Information -->
    <div class="student-info">
        <strong>Student Name:</strong> {{ $student->name }}<br>
        <strong>Registration No:</strong> {{ $student->student_reg_no }}<br>
        <strong>Class:</strong> {{ $student->schoolClass->name ?? 'N/A' }}<br>
        <strong>Date:</strong> {{ date('d/m/Y') }}
        @if(isset($invoiceData['has_scholarships']) && $invoiceData['has_scholarships'])
            <br><span class="scholarship-badge" style="font-size: 9px; padding: 2px 6px;">🎓 SCHOLARSHIP RECIPIENT</span>
        @endif
    </div>

    @if(isset($invoiceData['has_scholarships']) && $invoiceData['has_scholarships'])
    <!-- Scholarship Summary -->
    <div class="scholarship-summary">
        <div style="font-weight: bold; font-size: 10px; color: #856404; margin-bottom: 4px;">🎓 SCHOLARSHIP SUMMARY</div>
        <table style="width: 100%; font-size: 9px;">
            <tr>
                <td>Original Fee Total:</td>
                <td style="text-align: right; font-weight: bold;">TSh {{ number_format($invoiceData['total_original_fees'], 2) }}</td>
            </tr>
            <tr style="color: #28a745;">
                <td>Scholarship Amount (Forgiven):</td>
                <td style="text-align: right; font-weight: bold;">- TSh {{ number_format($invoiceData['total_scholarship_amount'], 2) }}</td>
            </tr>
            <tr style="border-top: 1px solid #ffc107; font-weight: bold;">
                <td>Net Fee Amount:</td>
                <td style="text-align: right;">TSh {{ number_format($invoiceData['total_fees'], 2) }}</td>
            </tr>
        </table>
    </div>
    @endif

    <!-- Fee Items Table Organized by Academic Year -->
    @if(isset($invoiceData['items_by_year']) && count($invoiceData['items_by_year']) > 0)
        @foreach($invoiceData['items_by_year'] as $yearData)
        <div style="margin-bottom: 15px;">
            <div style="background-color: #2196f3; color: white; padding: 6px 10px; font-weight: bold; font-size: 11px; border-radius: 3px 3px 0 0;">
                📅 Academic Year: {{ $yearData['year_name'] }}
                @if($yearData['subtotal_balance'] > 0)
                    <span style="float: right; background: #f44336; padding: 2px 8px; border-radius: 3px; font-size: 9px;">Balance: TSh {{ number_format($yearData['subtotal_balance'], 2) }}</span>
                @else
                    <span style="float: right; background: #4caf50; padding: 2px 8px; border-radius: 3px; font-size: 9px;">Paid</span>
                @endif
            </div>
            <table class="fees-table" style="margin-top: 0;">
                <thead>
                    <tr>
                        <th style="width: 40%;">Fee Item</th>
                        <th style="width: 20%;">Amount Required</th>
                        <th style="width: 20%;">Amount Paid</th>
                        <th style="width: 20%;">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($yearData['items'] as $item)
                    <tr class="{{ $item['is_overdue'] ? 'overdue' : '' }} {{ isset($item['has_scholarship']) && $item['has_scholarship'] ? 'scholarship-row' : '' }}">
                        <td>
                            {{ $item['name'] }}
                            @if(isset($item['has_scholarship']) && $item['has_scholarship'])
                                <span class="scholarship-badge">🎓 {{ $item['scholarship_type'] === 'full' ? 'FULL' : 'PARTIAL' }}</span>
                                @if($item['scholarship_name'])
                                    <br><small style="color: #856404; font-size: 7px;">{{ $item['scholarship_name'] }}</small>
                                @endif
                            @endif
                            @if($item['deadline'])
                                <br><small style="color: #666; font-size: 8px;">Deadline: {{ date('d/m/Y', strtotime($item['deadline'])) }}</small>
                                @if($item['is_overdue'])
                                    <br><small style="color: #d9534f; font-weight: bold; font-size: 8px;">OVERDUE</small>
                                @endif
                            @endif
                        </td>
                        <td class="amount">
                            @if(isset($item['has_scholarship']) && $item['has_scholarship'] && $item['scholarship_amount'] > 0)
                                <span class="original-amount">TSh {{ number_format($item['original_amount'], 2) }}</span><br>
                            @endif
                            TSh {{ number_format($item['amount'], 2) }}
                            @if(isset($item['has_scholarship']) && $item['has_scholarship'] && $item['scholarship_amount'] > 0)
                                <br><small style="color: #28a745; font-size: 7px;">(-{{ number_format($item['scholarship_amount'], 2) }} scholarship)</small>
                            @endif
                        </td>
                        <td class="amount">TSh {{ number_format($item['paid'], 2) }}</td>
                        <td class="amount">TSh {{ number_format($item['balance'], 2) }}</td>
                    </tr>
                    @endforeach
                    <tr style="background-color: #e3f2fd; font-weight: bold;">
                        <td>Subtotal ({{ $yearData['year_name'] }})</td>
                        <td class="amount">TSh {{ number_format($yearData['subtotal_fees'], 2) }}</td>
                        <td class="amount">TSh {{ number_format($yearData['subtotal_paid'], 2) }}</td>
                        <td class="amount">TSh {{ number_format($yearData['subtotal_balance'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endforeach

        <!-- Grand Total -->
        <table class="fees-table">
            <tbody>
                <tr class="total-row" style="background-color: #1976d2; color: white;">
                    <td style="width: 40%;"><strong>GRAND TOTAL (All Academic Years)</strong></td>
                    <td class="amount" style="width: 20%;">TSh {{ number_format($invoiceData['total_fees'], 2) }}</td>
                    <td class="amount" style="width: 20%;">TSh {{ number_format($invoiceData['total_paid'], 2) }}</td>
                    <td class="amount" style="width: 20%;">TSh {{ number_format($invoiceData['balance_remaining'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <!-- Fallback for old data without academic year -->
        <table class="fees-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Fee Item</th>
                    <th style="width: 20%;">Amount Required</th>
                    <th style="width: 20%;">Amount Paid</th>
                    <th style="width: 20%;">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoiceData['items'] as $item)
                <tr class="{{ $item['is_overdue'] ? 'overdue' : '' }}">
                    <td>
                        {{ $item['name'] }}
                        @if($item['deadline'])
                            <br><small style="color: #666; font-size: 8px;">Deadline: {{ date('d/m/Y', strtotime($item['deadline'])) }}</small>
                            @if($item['is_overdue'])
                                <br><small style="color: #d9534f; font-weight: bold; font-size: 8px;">OVERDUE</small>
                            @endif
                        @endif
                    </td>
                    <td class="amount">TSh {{ number_format($item['amount'], 2) }}</td>
                    <td class="amount">TSh {{ number_format($item['paid'], 2) }}</td>
                    <td class="amount">TSh {{ number_format($item['balance'], 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 15px;">No fees assigned yet</td>
                </tr>
                @endforelse

                <tr class="total-row">
                    <td><strong>TOTAL</strong></td>
                    <td class="amount">TSh {{ number_format($invoiceData['total_fees'], 2) }}</td>
                    <td class="amount">TSh {{ number_format($invoiceData['total_paid'], 2) }}</td>
                    <td class="amount">TSh {{ number_format($invoiceData['balance_remaining'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <!-- Balance Summary -->
    <div class="balance-box {{ $invoiceData['balance_remaining'] <= 0 ? 'paid-full' : '' }}">
        @if($invoiceData['balance_remaining'] > 0)
            <div style="font-size: 11px; margin-bottom: 5px; font-weight: bold;">AMOUNT REMAINING TO PAY:</div>
            <div class="balance-amount">TSh {{ number_format($invoiceData['balance_remaining'], 2) }}</div>
            <div style="margin-top: 8px; font-size: 9px; color: #666;">
                Please ensure payment is made by the deadline date(s) indicated above.
            </div>

            @if(isset($bankAccounts) && $bankAccounts->count() > 0)
            <div class="bank-section">
                <div style="font-size: 10px; font-weight: bold; color: #333; margin-bottom: 4px;">🏦 PAYMENT DETAILS:</div>
                @foreach($bankAccounts as $index => $bankAccount)
                <div class="bank-option">
                    <strong>{{ $index + 1 }}.</strong> {{ $bankAccount->bank_name }}: {{ $bankAccount->account_number }}
                </div>
                @endforeach
            </div>
            @endif
        @else
            <div style="font-size: 14px; color: #155724; font-weight: bold;">✅ ALL FEES PAID IN FULL</div>
            <div style="margin-top: 3px; font-size: 10px; color: #155724;">
                Thank you for your prompt payment!
            </div>
        @endif
    </div>

    <div class="footer">
        Generated by Darasa Finance System | {{ date('l, F j, Y \a\t g:i A') }}<br>
        For inquiries, please contact the school office.
    </div>
</body>
</html>

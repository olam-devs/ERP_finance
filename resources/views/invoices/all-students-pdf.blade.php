<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Fee Statements</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .school-logo { max-width: 80px; max-height: 80px; margin-bottom: 10px; }
        .school-name { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .school-info { font-size: 10px; color: #666; }
        .invoice-title { font-size: 16px; font-weight: bold; margin: 15px 0; text-align: center; }
        .student-info { background-color: #f5f5f5; padding: 10px; margin-bottom: 15px; border-left: 4px solid #2196f3; }
        .fees-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .fees-table th { background-color: #2196f3; color: white; padding: 8px; font-size: 11px; text-align: left; }
        .fees-table td { border: 1px solid #ddd; padding: 6px; font-size: 10px; }
        .amount { text-align: right; font-weight: bold; }
        .total-row { background-color: #f9f9f9; font-weight: bold; font-size: 12px; }
        .balance-box { background-color: #fff3cd; border: 2px solid #ffc107; padding: 15px; margin: 20px 0; text-align: center; }
        .balance-amount { font-size: 24px; font-weight: bold; color: #d9534f; }
        .overdue { background-color: #f8d7da; color: #721c24; }
        .paid-full { background-color: #d4edda; color: #155724; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #999; font-size: 9px; text-align: center; color: #666; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @foreach($allInvoices as $index => $invoice)
        @if($index > 0)
        <div class="page-break"></div>
        @endif

        <!-- Header for each page -->
        <div class="header">
            @if($school->logo_path && file_exists(storage_path('app/public/' . $school->logo_path)))
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
            <strong>Student Name:</strong> {{ $invoice['student']->name }}<br>
            <strong>Registration No:</strong> {{ $invoice['student']->student_reg_no }}<br>
            <strong>Class:</strong> {{ $invoice['student']->schoolClass->name ?? 'N/A' }}<br>
            <strong>Date:</strong> {{ date('d/m/Y') }}
        </div>

        <!-- Fee Items Table -->
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
                @forelse($invoice['invoiceData']['items'] as $item)
                <tr class="{{ $item['is_overdue'] ? 'overdue' : '' }}">
                    <td>
                        {{ $item['name'] }}
                        @if($item['deadline'])
                            <br><small style="color: #666;">Payment Deadline: {{ date('d/m/Y', strtotime($item['deadline'])) }}</small>
                            @if($item['is_overdue'])
                                <br><small style="color: #d9534f; font-weight: bold;">⚠️ OVERDUE</small>
                            @endif
                        @endif
                    </td>
                    <td class="amount">TSh {{ number_format($item['amount'], 2) }}</td>
                    <td class="amount">TSh {{ number_format($item['paid'], 2) }}</td>
                    <td class="amount">TSh {{ number_format($item['balance'], 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px;">No fees assigned yet</td>
                </tr>
                @endforelse

                <tr class="total-row">
                    <td><strong>TOTAL</strong></td>
                    <td class="amount">TSh {{ number_format($invoice['invoiceData']['total_fees'], 2) }}</td>
                    <td class="amount">TSh {{ number_format($invoice['invoiceData']['total_paid'], 2) }}</td>
                    <td class="amount">TSh {{ number_format($invoice['invoiceData']['balance_remaining'], 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Balance Summary -->
        <div class="balance-box {{ $invoice['invoiceData']['balance_remaining'] <= 0 ? 'paid-full' : '' }}">
            @if($invoice['invoiceData']['balance_remaining'] > 0)
                <div style="font-size: 14px; margin-bottom: 10px;">AMOUNT REMAINING TO PAY:</div>
                <div class="balance-amount">TSh {{ number_format($invoice['invoiceData']['balance_remaining'], 2) }}</div>
                <div style="margin-top: 10px; font-size: 11px; color: #666;">
                    Please ensure payment is made by the deadline date(s) indicated above.
                </div>
            @else
                <div style="font-size: 16px; color: #155724; font-weight: bold;">✅ ALL FEES PAID IN FULL</div>
                <div style="margin-top: 5px; font-size: 11px; color: #155724;">
                    Thank you for your prompt payment!
                </div>
            @endif
        </div>

        <div class="footer">
            Generated by Darasa Finance System | {{ date('l, F j, Y \a\t g:i A') }}<br>
            For inquiries, please contact the school office.
        </div>
    @endforeach
</body>
</html>

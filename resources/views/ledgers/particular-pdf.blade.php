<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Particular Ledger - {{ $particular->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .school-info {
            font-size: 10px;
            color: #666;
        }
        .ledger-title {
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0;
            text-align: center;
            text-decoration: underline;
        }
        .particular-info {
            margin-bottom: 15px;
            text-align: center;
        }
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .ledger-table th {
            background-color: #f0f0f0;
            border: 1px solid #333;
            padding: 6px;
            font-size: 9px;
            font-weight: bold;
        }
        .ledger-table td {
            border: 1px solid #999;
            padding: 5px;
            font-size: 9px;
        }
        .amount-debit {
            text-align: right;
            color: #d9534f;
        }
        .amount-credit {
            text-align: right;
            color: #5cb85c;
        }
        .amount-balance {
            text-align: right;
            font-weight: bold;
            color: #0275d8;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #999;
            font-size: 9px;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">{{ $school->school_name ?? 'Darasa Secondary School' }}</div>
        <div class="school-info">
            {{ $school->po_box ?? 'P.O. Box 12345' }} | {{ $school->region ?? 'Dar es Salaam' }} | Tel: {{ $school->phone ?? '+255 123 456 789' }}
        </div>
    </div>

    <div class="ledger-title">PARTICULAR LEDGER</div>

    <div class="particular-info">
        <strong>Particular:</strong> {{ $particular->name }}
        @if($particular->amount)
        | <strong>Standard Amount:</strong> TSh {{ number_format($particular->amount, 2) }}
        @endif
        <br>
        <strong>Date Range:</strong> {{ $dateRange }}
    </div>

    <div style="background-color: #e3f2fd; border: 2px solid #2196f3; padding: 10px; margin-bottom: 15px; text-align: center;">
        <strong style="font-size: 12px;">OPENING BALANCE: TSh {{ number_format($openingBalance, 2) }}</strong>
    </div>

    <table class="ledger-table">
        <thead>
            <tr>
                <th style="width: 8%;">Date</th>
                <th style="width: 18%;">Student</th>
                <th style="width: 10%;">Reg No</th>
                <th style="width: 8%;">Class</th>
                <th style="width: 12%;">Book</th>
                <th style="width: 8%;">Type</th>
                <th style="width: 10%;">DR (TSh)</th>
                <th style="width: 10%;">CR (TSh)</th>
                <th style="width: 16%;">Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vouchers as $voucher)
            <tr>
                <td>{{ $voucher->date->format('d/m/Y') }}</td>
                <td>{{ $voucher->student ? $voucher->student->name : 'N/A' }}</td>
                <td>{{ $voucher->student ? $voucher->student->student_reg_no : 'N/A' }}</td>
                <td>{{ $voucher->student ? ($voucher->student->schoolClass->name ?? 'N/A') : 'N/A' }}</td>
                <td>{{ $voucher->book ? $voucher->book->name : 'N/A' }}</td>
                <td>{{ $voucher->voucher_type }}</td>
                <td class="amount-debit">{{ number_format($voucher->debit, 2) }}</td>
                <td class="amount-credit">{{ number_format($voucher->credit, 2) }}</td>
                <td style="font-size: 8px;">{{ $voucher->notes ?? '' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px;">No transactions found</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f9f9f9; font-weight: bold;">
                <td colspan="6" style="text-align: right; padding-right: 10px;"><strong>TOTALS:</strong></td>
                <td class="amount-debit"><strong>{{ number_format($totalDebit, 2) }}</strong></td>
                <td class="amount-credit"><strong>{{ number_format($totalCredit, 2) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 20px; padding: 10px; background-color: #e3f2fd; border: 2px solid #2196f3; text-align: center;">
        <strong style="font-size: 12px;">CLOSING BALANCE: TSh {{ number_format($balance, 2) }}</strong>
    </div>

    <div class="footer">
        Generated by Darasa Finance System | {{ date('l, F j, Y \a\t g:i A') }}
    </div>
</body>
</html>

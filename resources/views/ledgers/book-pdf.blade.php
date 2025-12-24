<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Book Ledger - {{ $book->name }}</title>
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
        .book-info {
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

    <div class="ledger-title">BOOK LEDGER</div>

    <div class="book-info">
        <strong>Book:</strong> {{ $book->name }}
        @if($book->bank_account_number)
        | <strong>Account:</strong> {{ $book->bank_account_number }}
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
                <th style="width: 7%;">Date</th>
                <th style="width: 16%;">Student</th>
                <th style="width: 15%;">Particular</th>
                <th style="width: 9%;">Type</th>
                <th style="width: 10%;">Voucher No.</th>
                <th style="width: 10%;">DR-In (TSh)</th>
                <th style="width: 10%;">CR-Out (TSh)</th>
                <th style="width: 10%;">Balance (TSh)</th>
                <th style="width: 13%;">Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ledgerData as $entry)
            <tr>
                <td>{{ $entry['date'] }}</td>
                <td>{{ $entry['student'] }}</td>
                <td>{{ $entry['particular'] }}</td>
                <td>{{ $entry['voucher_type'] }}</td>
                <td>{{ $entry['voucher_number'] }}</td>
                <td class="amount-debit">{{ number_format($entry['debit'], 2) }}</td>
                <td class="amount-credit">{{ number_format($entry['credit'], 2) }}</td>
                <td class="amount-balance">{{ number_format($entry['balance'], 2) }}</td>
                <td style="font-size: 8px;">{{ $entry['notes'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px;">No transactions found</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f9f9f9; font-weight: bold;">
                <td colspan="5" style="text-align: right; padding-right: 10px;"><strong>TOTALS:</strong></td>
                <td class="amount-debit"><strong>{{ number_format($totalReceipts, 2) }}</strong></td>
                <td class="amount-credit"><strong>{{ number_format($totalPayments, 2) }}</strong></td>
                <td class="amount-balance"><strong>{{ number_format($balance, 2) }}</strong></td>
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

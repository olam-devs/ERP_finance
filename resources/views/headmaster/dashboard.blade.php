<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Headmaster Dashboard - {{ $settings->school_name ?? 'Darasa Finance' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Top Header -->
    <nav class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white shadow-2xl sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    @if($settings->logo_path && file_exists(public_path('storage/' . $settings->logo_path)))
                        <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="School Logo" class="w-12 h-12 rounded-lg bg-white p-1 object-contain">
                    @else
                        <div class="bg-white bg-opacity-20 p-2 rounded-lg">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold">{{ $settings->school_name ?? 'Darasa Finance' }}</h1>
                        <p class="text-xs text-indigo-100">Headmaster Portal - Read Only</p>
                    </div>
                </div>
                <div class="flex gap-4 items-center">
                    <div class="text-sm bg-white bg-opacity-20 px-3 py-1.5 rounded-lg">
                        👤 {{ session('headmaster_name') }}
                    </div>
                    <form method="POST" action="{{ route('headmaster.logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg transition shadow-lg font-semibold">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl shadow-2xl p-8 mb-8 text-white">
            <h2 class="text-3xl font-bold mb-2">Welcome Back, {{ session('headmaster_name') }}! 👋</h2>
            <p class="text-indigo-100">Here's an overview of the school's financial performance.</p>
        </div>

        <!-- Summary Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Students -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Students</p>
                        <p class="text-3xl font-bold text-gray-800">{{ number_format($totalStudents) }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Expected -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Expected Fees</p>
                        <p class="text-2xl font-bold text-gray-800">TSH {{ number_format($totalFeesExpected, 0) }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Collected -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Collected Fees</p>
                        <p class="text-2xl font-bold text-gray-800">TSH {{ number_format($totalFeesCollected, 0) }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Collection Rate -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Collection Rate</p>
                        <p class="text-3xl font-bold text-gray-800">{{ number_format($collectionRate, 1) }}%</p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">📊 Financial Reports</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('headmaster.ledgers') }}" class="group bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl transition border-l-4 border-blue-500">
                    <div class="text-4xl mb-3">📊</div>
                    <h3 class="text-lg font-bold text-blue-600 mb-2">Student Ledgers</h3>
                    <p class="text-sm text-gray-600">View individual student payment records</p>
                </a>

                <a href="{{ route('headmaster.particular-ledger') }}" class="group bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl transition border-l-4 border-green-500">
                    <div class="text-4xl mb-3">📋</div>
                    <h3 class="text-lg font-bold text-green-600 mb-2">Particular Ledger</h3>
                    <p class="text-sm text-gray-600">Fee type collection reports</p>
                </a>

                <a href="{{ route('headmaster.overdue') }}" class="group bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl transition border-l-4 border-red-500">
                    <div class="text-4xl mb-3">💸</div>
                    <h3 class="text-lg font-bold text-red-600 mb-2">Overdue Payments</h3>
                    <p class="text-sm text-gray-600">Track outstanding fees</p>
                </a>

                <a href="{{ route('headmaster.invoices') }}" class="group bg-white rounded-xl shadow-lg p-6 hover:shadow-2xl transition border-l-4 border-purple-500">
                    <div class="text-4xl mb-3">📄</div>
                    <h3 class="text-lg font-bold text-purple-600 mb-2">Student Invoices</h3>
                    <p class="text-sm text-gray-600">View and download invoices</p>
                </a>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Transactions</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Student</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Particular</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Book</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Amount</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Type</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($recentTransactions as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $transaction->date }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $transaction->student->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $transaction->particular->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $transaction->book->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-right">TSH {{ number_format($transaction->amount, 0) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $transaction->voucher_type == 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($transaction->voucher_type) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No recent transactions</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Info Note -->
        <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Read-Only Access:</strong> You have view-only access to financial reports and summaries. Contact the school accountant for any changes or updates.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12 py-6">
        <div class="container mx-auto px-6 text-center">
            <p>&copy; {{ date('Y') }} {{ $settings->school_name ?? 'Darasa Finance' }}. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

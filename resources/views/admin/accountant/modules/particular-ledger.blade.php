<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Particular Ledger - Darasa Finance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-100">
    @include('components.sidebar')

    <!-- Header -->
    <nav class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-4 shadow-lg mb-6 sticky top-0 z-40">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <!-- Menu Button -->
                <button onclick="toggleSidebar()" class="hover:bg-white hover:bg-opacity-20 p-2 rounded transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <!-- Clickable Logo -->
                <a href="{{ route('accountant.dashboard') }}" class="flex items-center gap-2 hover:opacity-80 transition">
                    @if($settings->logo_path && file_exists(public_path('storage/' . $settings->logo_path)))
                        <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="School Logo" class="w-10 h-10 rounded-lg bg-white p-1 object-contain">
                    @else
                        <div class="bg-white bg-opacity-20 p-2 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    @endif
                    <h1 class="text-2xl font-bold">📋 Particular Ledger</h1>
                </a>
            </div>
            <div class="flex gap-3 items-center">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded transition">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Module Content -->
    <div class="container mx-auto p-6">
        <div>
            <h2 class="text-3xl font-bold text-teal-600 mb-6">📋 Particular Ledger</h2>
            <p class="text-gray-600 mb-6">Select a particular/fee type to view all related transactions across all students.</p>

            <div id="particularsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6"></div>
            <div id="particularDetailsSection"></div>
        </div>
    </div>

    <!-- Module Scripts -->
    <script>
        const API_BASE = '/api';

        // Configure axios
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        axios.defaults.headers.common['Accept'] = 'application/json';
        axios.defaults.withCredentials = true;

        // Format amount in Tanzania Shillings
        function formatTSh(amount) {
            return 'TSh ' + parseFloat(amount).toLocaleString('en-TZ', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Load particulars on page load
        document.addEventListener('DOMContentLoaded', async function() {
            await loadParticulars();
        });

        async function loadParticulars() {
            try {
                const response = await axios.get(`${API_BASE}/particulars`);
                const particulars = response.data;

                let html = '';
                particulars.forEach(particular => {
                    html += `
                        <div onclick="loadParticularDetails(${particular.id})" class="bg-teal-50 border-2 border-teal-300 rounded-lg p-4 hover:bg-teal-100 hover:border-teal-500 transition cursor-pointer">
                            <h4 class="font-bold text-lg text-teal-800">${particular.name}</h4>
                            <p class="text-xs text-gray-600 mt-2">Click to view transactions</p>
                        </div>
                    `;
                });

                document.getElementById('particularsGrid').innerHTML = html;
            } catch (error) {
                alert('Error loading particulars: ' + error.message);
            }
        }

        async function loadParticularDetails(particularId, fromDate = '', toDate = '') {
            try {
                let url = `${API_BASE}/ledgers/particular/${particularId}`;
                if (fromDate && toDate) {
                    url += `?from_date=${fromDate}&to_date=${toDate}`;
                }

                const response = await axios.get(url);
                const data = response.data;

                let html = `
                    <div class="bg-white border-2 border-teal-300 rounded-lg p-6">
                        <div class="mb-6 border-b-2 border-teal-300 pb-4">
                            <h3 class="text-2xl font-bold text-teal-700">${data.particular.name}</h3>
                            <p class="text-sm text-gray-600 mt-2">${data.date_range}</p>

                            <!-- Date Range Filter -->
                            <div class="mt-4 bg-blue-50 border-2 border-blue-300 rounded-lg p-3">
                                <h4 class="text-sm font-bold text-blue-700 mb-2">Filter by Date Range</h4>
                                <div class="grid grid-cols-4 gap-3">
                                    <div>
                                        <label class="text-xs font-bold text-gray-700">From:</label>
                                        <input type="date" id="particularFromDate" value="${fromDate}" class="w-full border rounded px-2 py-1">
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-gray-700">To:</label>
                                        <input type="date" id="particularToDate" value="${toDate}" class="w-full border rounded px-2 py-1">
                                    </div>
                                    <div class="flex items-end">
                                        <button onclick="applyParticularDateFilter(${particularId})" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-1 rounded w-full text-sm">
                                            Apply
                                        </button>
                                    </div>
                                    <div class="flex items-end">
                                        <button onclick="loadParticularDetails(${particularId})" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-1 rounded w-full text-sm">
                                            Clear
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Cards -->
                `;

                // Show opening balance if it exists
                if (data.summary.opening_balance !== undefined && data.summary.opening_balance !== 0) {
                    html += `
                        <div class="grid grid-cols-4 gap-4 mb-6">
                            <div class="bg-purple-50 border-2 border-purple-300 rounded-lg p-4 text-center">
                                <p class="text-xs text-gray-600">Opening Balance</p>
                                <p class="text-2xl font-bold text-purple-700">${formatTSh(data.summary.opening_balance)}</p>
                            </div>
                            <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-4 text-center">
                                <p class="text-xs text-gray-600">Total Debit</p>
                                <p class="text-2xl font-bold text-blue-700">${formatTSh(data.summary.total_debit)}</p>
                            </div>
                            <div class="bg-green-50 border-2 border-green-300 rounded-lg p-4 text-center">
                                <p class="text-xs text-gray-600">Total Credit</p>
                                <p class="text-2xl font-bold text-green-700">${formatTSh(data.summary.total_credit)}</p>
                            </div>
                            <div class="bg-red-50 border-2 border-red-300 rounded-lg p-4 text-center">
                                <p class="text-xs text-gray-600">Closing Balance</p>
                                <p class="text-2xl font-bold text-red-700">${formatTSh(data.summary.balance)}</p>
                            </div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-4 text-center">
                                <p class="text-xs text-gray-600">Total Debit</p>
                                <p class="text-2xl font-bold text-blue-700">${formatTSh(data.summary.total_debit)}</p>
                            </div>
                            <div class="bg-green-50 border-2 border-green-300 rounded-lg p-4 text-center">
                                <p class="text-xs text-gray-600">Total Credit</p>
                                <p class="text-2xl font-bold text-green-700">${formatTSh(data.summary.total_credit)}</p>
                            </div>
                            <div class="bg-red-50 border-2 border-red-300 rounded-lg p-4 text-center">
                                <p class="text-xs text-gray-600">Balance</p>
                                <p class="text-2xl font-bold text-red-700">${formatTSh(data.summary.balance)}</p>
                            </div>
                        </div>
                    `;
                }

                html += `

                        <!-- Transactions Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full border-2 border-gray-300 bg-white">
                                <thead class="bg-teal-100">
                                    <tr>
                                        <th class="p-3 text-left">Date</th>
                                        <th class="p-3 text-left">Student</th>
                                        <th class="p-3 text-left">Class</th>
                                        <th class="p-3 text-left">Book</th>
                                        <th class="p-3 text-left">Type</th>
                                        <th class="p-3 text-right">Debit</th>
                                        <th class="p-3 text-right">Credit</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                data.entries.forEach(entry => {
                    html += `
                        <tr class="border-t hover:bg-teal-50">
                            <td class="p-3">${entry.date}</td>
                            <td class="p-3 font-semibold">${entry.student}</td>
                            <td class="p-3">${entry.class}</td>
                            <td class="p-3">${entry.book}</td>
                            <td class="p-3"><span class="px-2 py-1 rounded text-xs font-bold ${
                                entry.voucher_type === 'Sales' ? 'bg-red-200 text-red-800' :
                                entry.voucher_type === 'Receipt' ? 'bg-green-200 text-green-800' :
                                'bg-blue-200 text-blue-800'
                            }">${entry.voucher_type}</span></td>
                            <td class="p-3 text-right font-bold text-red-600">${formatTSh(entry.debit)}</td>
                            <td class="p-3 text-right font-bold text-green-600">${formatTSh(entry.credit)}</td>
                        </tr>
                    `;
                });

                html += `
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 text-center">
                `;

                // Add date params to PDF URL if filter is applied
                let pdfUrl = `${API_BASE}/ledgers/particular/${particularId}/pdf`;
                if (fromDate && toDate) {
                    pdfUrl += `?from_date=${fromDate}&to_date=${toDate}`;
                }

                html += `
                            <a href="${pdfUrl}" target="_blank" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded inline-flex items-center transition">
                                📄 Download PDF
                            </a>
                        </div>
                    </div>
                `;

                document.getElementById('particularDetailsSection').innerHTML = html;
            } catch (error) {
                alert('Error loading particular details: ' + error.message);
            }
        }

        function applyParticularDateFilter(particularId) {
            const fromDate = document.getElementById('particularFromDate').value;
            const toDate = document.getElementById('particularToDate').value;

            if (fromDate && toDate) {
                loadParticularDetails(particularId, fromDate, toDate);
            } else {
                alert('Please select both From and To dates');
            }
        }
    </script>
</body>
</html>

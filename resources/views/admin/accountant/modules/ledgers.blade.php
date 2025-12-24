<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ledgers - Darasa Finance</title>
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
                    <h1 class="text-2xl font-bold">📊 Ledgers</h1>
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
        <div id="moduleContent">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-orange-600">📊 Ledgers</h2>
                <div class="flex gap-3">
                    <a href="/api/ledgers/all-students/pdf" target="_blank"
                        class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded inline-flex items-center transition">
                        📑 Download All Students Ledgers (PDF)
                    </a>
                </div>
            </div>

            <!-- Date Range Filter Section -->
            <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-4 mb-6">
                <h4 class="text-lg font-bold text-blue-800 mb-3">📅 Date Range Filter (Optional)</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm font-bold text-gray-700 block mb-1">From Date:</label>
                        <input type="date" id="ledgerFromDate" class="w-full border-2 border-gray-300 rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm font-bold text-gray-700 block mb-1">To Date:</label>
                        <input type="date" id="ledgerToDate" class="w-full border-2 border-gray-300 rounded px-3 py-2">
                    </div>
                    <div class="flex items-end">
                        <button onclick="clearDateFilter()" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded w-full transition">
                            Clear Dates
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-600 mt-2">💡 Leave empty to view all records, or select dates to filter transactions within a specific period.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-orange-50 border-2 border-orange-300 rounded-lg p-6">
                    <h3 class="text-xl font-bold mb-4">👤 Student Ledger</h3>
                    <input type="text" id="studentLedgerSearch" onkeyup="searchStudentsForLedger()"
                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 mb-2"
                        placeholder="Type student name...">
                    <div id="studentLedgerSearchResults" class="mb-3"></div>
                    <p class="text-xs text-gray-500 mb-2">OR</p>
                    <select id="studentLedgerClass" onchange="loadStudentsForLedger()"
                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 mb-2">
                        <option value="">-- Select Class --</option>
                    </select>
                    <div id="studentLedgerClassResults"></div>
                </div>

                <div class="bg-orange-100 border-2 border-orange-400 rounded-lg p-6">
                    <h3 class="text-xl font-bold mb-4">👥 Class Ledger</h3>
                    <select id="classLedgerSelect"
                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 mb-4">
                        <option value="">-- Select Class --</option>
                    </select>
                    <button onclick="viewClassLedger()"
                        class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg w-full transition">
                        View Class Ledger
                    </button>
                </div>

                <div class="bg-orange-200 border-2 border-orange-500 rounded-lg p-6">
                    <h3 class="text-xl font-bold mb-4">🏦 Book Ledger</h3>
                    <select id="bookLedgerSelect"
                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 mb-4">
                        <option value="">-- Select Book --</option>
                    </select>
                    <button onclick="viewBookLedger()"
                        class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg w-full transition">
                        View Book Ledger
                    </button>
                </div>
            </div>

            <div id="ledgerContent" class="bg-white border-2 border-gray-300 rounded-lg p-6">
                <p class="text-center text-gray-500">Select a ledger type above to view reports</p>
            </div>
        </div>
    </div>

    <!-- Module Scripts -->
    <script>
        const API_BASE = '/api';
        let allBooks = [];
        let allStudents = [];
        let allClasses = [];

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

        // Load initial data on page load
        document.addEventListener('DOMContentLoaded', async function() {
            await loadInitialData();
            populateDropdowns();
        });

        async function loadInitialData() {
            try {
                const [booksResponse, studentsResponse, classesResponse] = await Promise.all([
                    axios.get(`${API_BASE}/books`),
                    axios.get(`${API_BASE}/students`),
                    axios.get(`${API_BASE}/classes`)
                ]);

                allBooks = booksResponse.data;
                allStudents = studentsResponse.data.students || studentsResponse.data;
                allClasses = classesResponse.data;
            } catch (error) {
                console.error('Error loading initial data:', error);
            }
        }

        function populateDropdowns() {
            // Populate class dropdowns
            const classOptions = allClasses.map(cls => `<option value="${cls.id}">${cls.name}</option>`).join('');
            document.getElementById('studentLedgerClass').innerHTML = '<option value="">-- Select Class --</option>' + classOptions;
            document.getElementById('classLedgerSelect').innerHTML = '<option value="">-- Select Class --</option>' + classOptions;

            // Populate book dropdown
            const bookOptions = allBooks.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
            document.getElementById('bookLedgerSelect').innerHTML = '<option value="">-- Select Book --</option>' + bookOptions;
        }

        function searchStudentsForLedger() {
            const searchTerm = document.getElementById('studentLedgerSearch').value.toLowerCase();
            if (searchTerm.length < 2) {
                document.getElementById('studentLedgerSearchResults').innerHTML = '';
                return;
            }

            const matches = allStudents.filter(s =>
                s.name.toLowerCase().includes(searchTerm)
            ).slice(0, 5);

            let html = '<div class="space-y-1 max-h-48 overflow-y-auto">';
            matches.forEach(student => {
                const className = student.school_class?.name || 'N/A';
                html += `
                    <div onclick="viewStudentLedger(${student.id})"
                        class="p-2 bg-white border rounded cursor-pointer hover:bg-orange-100">
                        <p class="font-bold text-sm">${student.name}</p>
                        <p class="text-xs text-gray-500">${student.student_reg_no} - ${className}</p>
                    </div>
                `;
            });
            html += '</div>';
            document.getElementById('studentLedgerSearchResults').innerHTML = html;
        }

        function loadStudentsForLedger() {
            const selectedClass = document.getElementById('studentLedgerClass').value;
            if (!selectedClass) {
                document.getElementById('studentLedgerClassResults').innerHTML = '';
                return;
            }

            const classStudents = allStudents.filter(s => s.class_id == selectedClass);

            let html = '<select class="w-full border-2 border-gray-300 rounded px-2 py-2 text-sm mt-2" onchange="if(this.value) viewStudentLedger(this.value)">';
            html += '<option value="">-- Select Student --</option>';
            classStudents.forEach(student => {
                html += `<option value="${student.id}">${student.name} (${student.student_reg_no})</option>`;
            });
            html += '</select>';
            document.getElementById('studentLedgerClassResults').innerHTML = html;
        }

        function clearDateFilter() {
            document.getElementById('ledgerFromDate').value = '';
            document.getElementById('ledgerToDate').value = '';
        }

        function getDateFilterParams() {
            const fromDate = document.getElementById('ledgerFromDate').value;
            const toDate = document.getElementById('ledgerToDate').value;
            let params = '';
            if (fromDate && toDate) {
                params = `?from_date=${fromDate}&to_date=${toDate}`;
            }
            return params;
        }

        async function viewStudentLedger(studentId) {
            try {
                const dateParams = getDateFilterParams();
                const response = await axios.get(`${API_BASE}/ledgers/student/${studentId}${dateParams}`);
                const data = response.data;

                const dateRangeText = data.date_range.from && data.date_range.to
                    ? `From: ${data.date_range.from} To: ${data.date_range.to}`
                    : 'All Transactions';

                let html = `
                    <div class="border-2 border-orange-300 rounded-lg p-6 bg-orange-50">
                        <div class="mb-6 text-center border-b-2 border-orange-300 pb-4">
                            <h3 class="text-2xl font-bold text-orange-700">STUDENT LEDGER</h3>
                            <p class="text-lg font-bold mt-2">${data.student.name}</p>
                            <p class="text-sm text-gray-600">${data.student.student_reg_no} - ${data.student.class}</p>
                            <p class="text-sm font-bold text-blue-600 mt-1">${dateRangeText}</p>
                            <div class="mt-4 flex justify-center gap-3">
                                <a href="${API_BASE}/ledgers/student/${studentId}/pdf${dateParams}" target="_blank" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded inline-flex items-center transition">
                                    📄 Download PDF
                                </a>
                                <a href="${API_BASE}/ledgers/student/${studentId}/csv${dateParams}" target="_blank" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded inline-flex items-center transition">
                                    📊 Download CSV
                                </a>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full border-2 border-gray-300 bg-white">
                                <thead class="bg-red-100">
                                    <tr>
                                        <th colspan="8" class="p-3 text-left text-lg font-bold text-red-700">SALES ASSIGNED</th>
                                    </tr>
                                    <tr class="bg-orange-200">
                                        <th class="p-3 text-left">Date</th>
                                        <th class="p-3 text-left">Particular</th>
                                        <th class="p-3 text-left">Type</th>
                                        <th class="p-3 text-left">Voucher #</th>
                                        <th class="p-3 text-right">Debit (DR)</th>
                                        <th class="p-3 text-right">Credit (CR)</th>
                                        <th class="p-3 text-right">Balance</th>
                                        <th class="p-3 text-left">Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                // Add Opening Balance row if date filter is applied
                if (data.summary.opening_balance !== undefined && data.summary.opening_balance !== 0) {
                    html += `
                        <tr class="bg-gray-200 font-bold border-2 border-gray-400">
                            <td colspan="4" class="p-3 text-left text-lg">OPENING BALANCE</td>
                            <td class="p-3 text-right">-</td>
                            <td class="p-3 text-right">-</td>
                            <td class="p-3 text-right text-blue-900 text-lg">${formatTSh(data.summary.opening_balance)}</td>
                            <td></td>
                        </tr>
                    `;
                }

                // Sales entries grouped on top
                data.sales.forEach(sale => {
                    html += `
                        <tr class="border-t bg-red-50">
                            <td class="p-3">${sale.date}</td>
                            <td class="p-3 font-bold">${sale.particular}</td>
                            <td class="p-3"><span class="px-2 py-1 rounded text-xs font-bold bg-red-200 text-red-800">${sale.voucher_type}</span></td>
                            <td class="p-3 font-mono text-sm">${sale.voucher_number}</td>
                            <td class="p-3 text-right font-bold text-red-600">${formatTSh(sale.debit)}</td>
                            <td class="p-3 text-right font-bold text-green-600">${formatTSh(sale.credit)}</td>
                            <td class="p-3 text-right font-bold text-blue-700">${formatTSh(sale.balance)}</td>
                            <td class="p-3 text-sm">${sale.notes || '-'}</td>
                        </tr>
                    `;
                });

                html += `
                                </tbody>
                                <thead class="bg-green-100">
                                    <tr>
                                        <th colspan="8" class="p-3 text-left text-lg font-bold text-green-700">PAYMENT ENTRIES</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                // Other entries (receipts, payments)
                data.entries.forEach(entry => {
                    html += `
                        <tr class="border-t">
                            <td class="p-3">${entry.date}</td>
                            <td class="p-3">${entry.particular}</td>
                            <td class="p-3"><span class="px-2 py-1 rounded text-xs font-bold ${
                                entry.voucher_type === 'Receipt' ? 'bg-green-200 text-green-800' :
                                'bg-blue-200 text-blue-800'
                            }">${entry.voucher_type}</span></td>
                            <td class="p-3 font-mono text-sm">${entry.voucher_number}</td>
                            <td class="p-3 text-right font-bold text-red-600">${formatTSh(entry.debit)}</td>
                            <td class="p-3 text-right font-bold text-green-600">${formatTSh(entry.credit)}</td>
                            <td class="p-3 text-right font-bold text-blue-700">${formatTSh(entry.balance)}</td>
                            <td class="p-3 text-sm">${entry.notes || '-'}</td>
                        </tr>
                    `;
                });

                html += `
                                </tbody>
                                <tfoot class="bg-orange-100 font-bold">
                                    <tr>
                                        <td colspan="4" class="p-3 text-right">TOTAL:</td>
                                        <td class="p-3 text-right text-red-600">${formatTSh(data.summary.total_debit)}</td>
                                        <td class="p-3 text-right text-green-600">${formatTSh(data.summary.total_credit)}</td>
                                        <td class="p-3 text-right text-blue-700 text-lg">${formatTSh(data.summary.closing_balance)}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="bg-blue-100 border-2 border-blue-300 rounded p-4 mt-4">
                `;

                // Show opening balance if it exists
                if (data.summary.opening_balance !== undefined && data.summary.opening_balance !== 0) {
                    html += `
                            <div class="text-center mb-3 pb-3 border-b-2 border-blue-300">
                                <p class="text-sm font-semibold text-gray-600">Opening Balance</p>
                                <p class="text-lg font-bold text-blue-700">${formatTSh(data.summary.opening_balance)}</p>
                            </div>
                    `;
                }

                html += `
                            <div class="text-center">
                                <p class="text-sm font-semibold text-gray-600">Closing Balance (Outstanding)</p>
                                <p class="text-2xl font-bold text-blue-800">${formatTSh(data.summary.closing_balance)}</p>
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('ledgerContent').innerHTML = html;
            } catch (error) {
                alert('Error loading ledger: ' + error.message);
            }
        }

        async function viewClassLedger() {
            const classId = document.getElementById('classLedgerSelect').value;
            if (!classId) {
                alert('⚠️ Please select a class');
                return;
            }

            try {
                const dateParams = getDateFilterParams();
                const response = await axios.get(`${API_BASE}/ledgers/class/${classId}${dateParams}`);
                const data = response.data;

                const dateRangeText = data.date_range.from && data.date_range.to
                    ? `From: ${data.date_range.from} To: ${data.date_range.to}`
                    : 'All Transactions';

                let html = `
                    <div class="border-2 border-orange-400 rounded-lg p-6 bg-orange-100">
                        <div class="mb-6 text-center border-b-2 border-orange-400 pb-4">
                            <h3 class="text-2xl font-bold text-orange-700">CLASS LEDGER</h3>
                            <p class="text-xl font-bold mt-2">${data.class}</p>
                            <p class="text-sm font-bold text-blue-600 mt-1">${dateRangeText}</p>
                            <div class="mt-4 flex justify-center gap-3">
                                <a href="${API_BASE}/ledgers/class/${classId}/pdf${dateParams}" target="_blank" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded inline-flex items-center transition">
                                    📄 Download PDF
                                </a>
                                <a href="${API_BASE}/ledgers/class/${classId}/csv${dateParams}" target="_blank" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded inline-flex items-center transition">
                                    📊 Download CSV
                                </a>
                            </div>
                        </div>

                        <!-- CLASS SUMMARY -->
                        <div class="bg-blue-50 border-2 border-blue-300 rounded p-4 mb-4">
                            <h4 class="text-lg font-bold text-blue-800 mb-3">CLASS SUMMARY</h4>
                `;

                // Show opening balance if it exists
                if (data.summary.opening_balance !== undefined && data.summary.opening_balance !== 0) {
                    html += `
                            <div class="grid grid-cols-4 gap-4 text-center">
                                <div>
                                    <p class="text-sm text-gray-600">Opening Balance</p>
                                    <p class="text-xl font-bold text-purple-600">${formatTSh(data.summary.opening_balance)}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Total Sales (DR)</p>
                                    <p class="text-xl font-bold text-red-600">${formatTSh(data.summary.total_sales)}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Total Receipts (CR)</p>
                                    <p class="text-xl font-bold text-green-600">${formatTSh(data.summary.total_receipts)}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Closing Balance</p>
                                    <p class="text-xl font-bold text-blue-700">${formatTSh(data.summary.total_balance)}</p>
                                </div>
                            </div>
                    `;
                } else {
                    html += `
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <p class="text-sm text-gray-600">Total Sales (DR)</p>
                                    <p class="text-xl font-bold text-red-600">${formatTSh(data.summary.total_sales)}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Total Receipts (CR)</p>
                                    <p class="text-xl font-bold text-green-600">${formatTSh(data.summary.total_receipts)}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Total Balance</p>
                                    <p class="text-xl font-bold text-blue-700">${formatTSh(data.summary.total_balance)}</p>
                                </div>
                            </div>
                    `;
                }

                html += `
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full border-2 border-gray-300 bg-white">
                                <thead class="bg-orange-200">
                                    <tr>
                                        <th class="p-3 text-left">Student Name</th>
                                        <th class="p-3 text-left">Reg No</th>
                `;

                // Add opening balance column if it exists
                if (data.summary.opening_balance !== undefined && data.summary.opening_balance !== 0) {
                    html += `<th class="p-3 text-right">Opening Balance</th>`;
                }

                html += `
                                        <th class="p-3 text-right">Sales (DR)</th>
                                        <th class="p-3 text-right">Receipts (CR)</th>
                                        <th class="p-3 text-right">Closing Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                data.students.forEach(entry => {
                    html += `
                        <tr class="border-t hover:bg-orange-50">
                            <td class="p-3 font-bold">${entry.student.name}</td>
                            <td class="p-3">${entry.student.student_reg_no}</td>
                    `;

                    // Add opening balance if it exists
                    if (data.summary.opening_balance !== undefined && data.summary.opening_balance !== 0) {
                        html += `<td class="p-3 text-right font-bold text-purple-600">${formatTSh(entry.opening_balance || 0)}</td>`;
                    }

                    html += `
                            <td class="p-3 text-right font-bold text-red-600">${formatTSh(entry.total_debit)}</td>
                            <td class="p-3 text-right font-bold text-green-600">${formatTSh(entry.total_credit)}</td>
                            <td class="p-3 text-right font-bold text-blue-700">${formatTSh(entry.balance)}</td>
                        </tr>
                    `;
                });

                html += `
                                </tbody>
                                <tfoot class="bg-orange-100 font-bold">
                                    <tr>
                                        <td colspan="2" class="p-3 text-right">GRAND TOTAL:</td>
                `;

                // Add opening balance total if it exists
                if (data.summary.opening_balance !== undefined && data.summary.opening_balance !== 0) {
                    html += `<td class="p-3 text-right text-purple-600 text-lg">${formatTSh(data.summary.opening_balance)}</td>`;
                }

                html += `
                                        <td class="p-3 text-right text-red-600">${formatTSh(data.summary.total_sales)}</td>
                                        <td class="p-3 text-right text-green-600">${formatTSh(data.summary.total_receipts)}</td>
                                        <td class="p-3 text-right text-blue-700 text-lg">${formatTSh(data.summary.total_balance)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                `;

                document.getElementById('ledgerContent').innerHTML = html;
            } catch (error) {
                alert('Error loading ledger: ' + error.message);
            }
        }

        async function viewBookLedger() {
            const bookId = document.getElementById('bookLedgerSelect').value;
            if (!bookId) {
                alert('⚠️ Please select a book');
                return;
            }

            try {
                const dateParams = getDateFilterParams();
                const response = await axios.get(`${API_BASE}/ledgers/book/${bookId}${dateParams}`);
                const data = response.data;

                const dateRangeText = data.date_range.from && data.date_range.to
                    ? `From: ${data.date_range.from} To: ${data.date_range.to}`
                    : 'All Transactions';

                let html = `
                    <div class="border-2 border-orange-500 rounded-lg p-6 bg-orange-200">
                        <div class="mb-6 text-center border-b-2 border-orange-500 pb-4">
                            <h3 class="text-2xl font-bold text-orange-800">BOOK LEDGER</h3>
                            <p class="text-xl font-bold mt-2">${data.book.name}</p>
                            ${data.book.bank_account_number ? `<p class="text-sm text-gray-600">Account: ${data.book.bank_account_number}</p>` : ''}
                            <p class="text-sm font-bold text-blue-600 mt-1">${dateRangeText}</p>
                            <div class="mt-4 flex justify-center gap-3">
                                <a href="${API_BASE}/ledgers/book/${bookId}/pdf" target="_blank" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded inline-flex items-center transition">
                                    📄 Download PDF
                                </a>
                                <a href="${API_BASE}/ledgers/book/${bookId}/csv" target="_blank" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded inline-flex items-center transition">
                                    📊 Download CSV
                                </a>
                            </div>
                        </div>

                        <!-- OPENING BALANCE -->
                        <div class="bg-blue-100 border-2 border-blue-300 rounded p-3 mb-4 text-center">
                            <p class="text-lg font-bold text-blue-800">OPENING BALANCE: ${formatTSh(data.summary.opening_balance)}</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full border-2 border-gray-300 bg-white">
                                <thead class="bg-orange-200">
                                    <tr>
                                        <th class="p-3 text-left">Date</th>
                                        <th class="p-3 text-left">Student</th>
                                        <th class="p-3 text-left">Particular</th>
                                        <th class="p-3 text-left">Type</th>
                                        <th class="p-3 text-left">Voucher #</th>
                                        <th class="p-3 text-right">DR (In)</th>
                                        <th class="p-3 text-right">CR (Out)</th>
                                        <th class="p-3 text-right">Balance</th>
                                        <th class="p-3 text-left">Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                data.ledger.forEach(entry => {
                    html += `
                        <tr class="border-t hover:bg-orange-50">
                            <td class="p-3">${entry.date}</td>
                            <td class="p-3 font-bold">${entry.student}</td>
                            <td class="p-3">${entry.particular}</td>
                            <td class="p-3"><span class="px-2 py-1 rounded text-xs font-bold ${
                                entry.voucher_type === 'Receipt' ? 'bg-green-200 text-green-800' :
                                'bg-blue-200 text-blue-800'
                            }">${entry.voucher_type}</span></td>
                            <td class="p-3 font-mono text-sm">${entry.voucher_number}</td>
                            <td class="p-3 text-right font-bold text-green-600">${formatTSh(entry.debit)}</td>
                            <td class="p-3 text-right font-bold text-red-600">${formatTSh(entry.credit)}</td>
                            <td class="p-3 text-right font-bold text-blue-700">${formatTSh(entry.balance)}</td>
                            <td class="p-3 text-sm">${entry.notes || '-'}</td>
                        </tr>
                    `;
                });

                html += `
                                </tbody>
                                <tfoot class="bg-orange-100 font-bold">
                                    <tr>
                                        <td colspan="5" class="p-3 text-right">TOTALS:</td>
                                        <td class="p-3 text-right text-green-600">${formatTSh(data.summary.total_receipts)}</td>
                                        <td class="p-3 text-right text-red-600">${formatTSh(data.summary.total_payments)}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- CLOSING BALANCE -->
                        <div class="bg-blue-100 border-2 border-blue-300 rounded p-3 mt-4 text-center">
                            <p class="text-lg font-bold text-blue-800">CLOSING BALANCE: ${formatTSh(data.summary.closing_balance)}</p>
                        </div>

                        <!-- SUSPENSE ACCOUNTS SECTION -->
                        ${data.suspense_accounts && data.suspense_accounts.length > 0 ? `
                            <div class="mt-6 p-4 bg-amber-50 border-2 border-amber-300 rounded-lg">
                                <h4 class="text-xl font-bold text-amber-800 mb-3">⏳ Suspense Accounts in this Book</h4>
                                ${data.summary.total_suspense_unresolved > 0 ? `
                                    <div class="bg-yellow-100 border border-yellow-400 rounded p-2 mb-3 text-center">
                                        <p class="font-bold text-yellow-800">Unresolved Suspense Amount: ${formatTSh(data.summary.total_suspense_unresolved)}</p>
                                    </div>
                                ` : ''}
                                <div class="overflow-x-auto">
                                    <table class="w-full border border-gray-300 bg-white">
                                        <thead class="bg-amber-200">
                                            <tr>
                                                <th class="p-2 text-left">Date</th>
                                                <th class="p-2 text-left">Reference</th>
                                                <th class="p-2 text-left">Description</th>
                                                <th class="p-2 text-right">Total Amount</th>
                                                <th class="p-2 text-right">Resolved</th>
                                                <th class="p-2 text-right">Remaining</th>
                                                <th class="p-2 text-left">Status</th>
                                                <th class="p-2 text-left">Resolved Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                ` : ''}

                ${data.suspense_accounts ? data.suspense_accounts.map(suspense => {
                    const resolvedAmount = suspense.resolved_amount || 0;
                    const remainingAmount = suspense.remaining_amount || (suspense.amount - resolvedAmount);
                    const status = suspense.status || (suspense.resolved ? 'Fully Resolved' : (resolvedAmount > 0 ? 'Partially Resolved' : 'Unresolved'));

                    let statusClass = 'bg-yellow-200 text-yellow-800';
                    if (status === 'Fully Resolved') {
                        statusClass = 'bg-green-200 text-green-800';
                    } else if (status === 'Partially Resolved') {
                        statusClass = 'bg-orange-200 text-orange-800';
                    }

                    return `
                        <tr class="border-t hover:bg-amber-50">
                            <td class="p-2">${suspense.date}</td>
                            <td class="p-2 font-mono text-sm">${suspense.reference_number || 'N/A'}</td>
                            <td class="p-2 text-sm">${suspense.description}</td>
                            <td class="p-2 text-right font-bold text-gray-700">${formatTSh(suspense.amount)}</td>
                            <td class="p-2 text-right font-bold text-green-600">${formatTSh(resolvedAmount)}</td>
                            <td class="p-2 text-right font-bold text-amber-700">${formatTSh(remainingAmount)}</td>
                            <td class="p-2">
                                <span class="px-2 py-1 rounded text-xs font-bold ${statusClass}">
                                    ${status}
                                </span>
                            </td>
                            <td class="p-2 text-sm">${suspense.resolved_at || '-'}</td>
                        </tr>
                    `;
                }).join('') : ''}

                ${data.suspense_accounts && data.suspense_accounts.length > 0 ? `
                                        </tbody>
                                    </table>
                                </div>
                                <p class="text-xs text-gray-600 mt-2">
                                    💡 Suspense accounts hold unallocated payments until they can be assigned to students.
                                    <a href="/accountant/suspense" class="text-blue-600 underline">Manage Suspense Accounts</a>
                                </p>
                            </div>
                        ` : ''}
                    </div>
                `;

                document.getElementById('ledgerContent').innerHTML = html;
            } catch (error) {
                alert('Error loading ledger: ' + error.message);
            }
        }
    </script>
</body>
</html>

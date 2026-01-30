<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Fee Entry - Darasa Finance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
                    <h1 class="text-2xl font-bold">💰 Fee Entry</h1>
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
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-purple-600">💰 Fee Entry</h2>
                <div class="flex gap-3">
                    <button onclick="showScholarshipsManager()" class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-lg shadow transition">
                        🎓 Scholarships
                    </button>
                    <button onclick="showCreateVoucherForm()" class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-lg shadow transition">
                        ➕ Record New Entry
                    </button>
                </div>
            </div>
            <div id="vouchersList" class="mt-4"></div>
            <div id="voucherFormContainer"></div>
        </div>
    </div>

    <!-- Module Scripts -->
    <script>
        const API_BASE = '/api';
        let allBooks = [];
        let allParticulars = [];
        let allStudents = [];
        let allClasses = [];
        let currentVoucherPage = 1;
        let voucherDateFilters = { from: '', to: '' };
        let filteredStudentsForVoucher = [];

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
            await loadVouchers(1);
        });

        async function loadInitialData() {
            try {
                const [booksResponse, particularsResponse, studentsResponse, classesResponse] = await Promise.all([
                    axios.get(`${API_BASE}/books`),
                    axios.get(`${API_BASE}/particulars`),
                    axios.get(`${API_BASE}/students`),
                    axios.get(`${API_BASE}/classes`)
                ]);

                allBooks = booksResponse.data;
                allParticulars = particularsResponse.data;
                allStudents = studentsResponse.data.students || studentsResponse.data;
                allClasses = classesResponse.data;
            } catch (error) {
                console.error('Error loading initial data:', error);
            }
        }

        async function loadVouchers(page = 1) {
            try {
                currentVoucherPage = page;
                let url = `${API_BASE}/vouchers?page=${page}`;

                // Add date filters if set
                if (voucherDateFilters.from && voucherDateFilters.to) {
                    url += `&from_date=${voucherDateFilters.from}&to_date=${voucherDateFilters.to}`;
                }

                const response = await axios.get(url);
                const paginationData = response.data;
                const vouchers = paginationData.data || response.data;

                let html = `
                    <!-- Date Range Filter -->
                    <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-4 mb-4">
                        <h4 class="text-md font-bold text-blue-800 mb-3">📅 Filter by Date Range</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div>
                                <label class="text-sm font-bold text-gray-700 block mb-1">From:</label>
                                <input type="date" id="voucherFromDate" value="${voucherDateFilters.from}"
                                    class="w-full border-2 border-gray-300 rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm font-bold text-gray-700 block mb-1">To:</label>
                                <input type="date" id="voucherToDate" value="${voucherDateFilters.to}"
                                    class="w-full border-2 border-gray-300 rounded px-3 py-2">
                            </div>
                            <div class="flex items-end">
                                <button onclick="applyVoucherDateFilter()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded w-full transition">
                                    Apply Filter
                                </button>
                            </div>
                            <div class="flex items-end">
                                <button onclick="clearVoucherDateFilter()" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded w-full transition">
                                    Clear Filter
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-2 border-gray-300 rounded-lg">
                            <thead class="bg-purple-100">
                                <tr>
                                    <th class="p-3 text-left">Date</th>
                                    <th class="p-3 text-left">Student</th>
                                    <th class="p-3 text-left">Particular</th>
                                    <th class="p-3 text-left">Type</th>
                                    <th class="p-3 text-left">Voucher #</th>
                                    <th class="p-3 text-right">Debit</th>
                                    <th class="p-3 text-right">Credit</th>
                                    <th class="p-3 text-left">Notes</th>
                                    <th class="p-3 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                vouchers.forEach(voucher => {
                    // Use display names from API (with fallbacks for expense/suspense entries)
                    const studentName = voucher.display_student_name || (voucher.student ? voucher.student.name : '-');
                    const particularName = voucher.display_particular_name || (voucher.particular ? voucher.particular.name : '-');

                    // Different styling for expense and suspense entries
                    const isExpense = particularName === 'Expense';
                    const isSuspense = particularName.includes('Suspense');
                    const rowClass = isExpense ? 'bg-orange-50' : (isSuspense ? 'bg-amber-50' : '');

                    html += `
                        <tr class="border-t hover:bg-purple-50 ${rowClass}">
                            <td class="p-3">${voucher.date}</td>
                            <td class="p-3 font-semibold">${studentName}</td>
                            <td class="p-3">
                                ${isExpense ? '<span class="px-2 py-1 rounded text-xs font-bold bg-orange-200 text-orange-800">' + particularName + '</span>' :
                                  isSuspense ? '<span class="px-2 py-1 rounded text-xs font-bold bg-amber-200 text-amber-800">' + particularName + '</span>' :
                                  particularName}
                            </td>
                            <td class="p-3"><span class="px-2 py-1 rounded text-xs font-bold ${
                                voucher.voucher_type === 'Sales' ? 'bg-red-200 text-red-800' :
                                voucher.voucher_type === 'Receipt' ? 'bg-green-200 text-green-800' :
                                'bg-blue-200 text-blue-800'
                            }">${voucher.voucher_type}</span></td>
                            <td class="p-3 font-mono text-sm">${voucher.voucher_number}</td>
                            <td class="p-3 text-right font-bold text-red-600">${formatTSh(voucher.debit)}</td>
                            <td class="p-3 text-right font-bold text-green-600">${formatTSh(voucher.credit)}</td>
                            <td class="p-3 text-xs text-gray-600">${voucher.notes || '-'}</td>
                            <td class="p-3">
                                <div class="flex gap-2">
                                    <button onclick="deleteVoucher(${voucher.id})" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                                        🗑️ Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });

                html += '</tbody></table></div>';

                // Add pagination controls
                if (paginationData.last_page && paginationData.last_page > 1) {
                    html += `
                        <div class="mt-6 flex justify-center items-center gap-2">
                            <button onclick="loadVouchers(${paginationData.current_page - 1})"
                                ${paginationData.current_page <= 1 ? 'disabled' : ''}
                                class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition">
                                Previous
                            </button>

                            <div class="flex gap-1">
                    `;

                    // Show page numbers
                    const startPage = Math.max(1, paginationData.current_page - 2);
                    const endPage = Math.min(paginationData.last_page, paginationData.current_page + 2);

                    if (startPage > 1) {
                        html += `
                            <button onclick="loadVouchers(1)" class="px-3 py-2 border rounded hover:bg-purple-100 transition">1</button>
                            ${startPage > 2 ? '<span class="px-2 py-2">...</span>' : ''}
                        `;
                    }

                    for (let i = startPage; i <= endPage; i++) {
                        html += `
                            <button onclick="loadVouchers(${i})"
                                class="px-3 py-2 border rounded ${i === paginationData.current_page ? 'bg-purple-500 text-white font-bold' : 'hover:bg-purple-100'} transition">
                                ${i}
                            </button>
                        `;
                    }

                    if (endPage < paginationData.last_page) {
                        html += `
                            ${endPage < paginationData.last_page - 1 ? '<span class="px-2 py-2">...</span>' : ''}
                            <button onclick="loadVouchers(${paginationData.last_page})" class="px-3 py-2 border rounded hover:bg-purple-100 transition">${paginationData.last_page}</button>
                        `;
                    }

                    html += `
                            </div>

                            <button onclick="loadVouchers(${paginationData.current_page + 1})"
                                ${paginationData.current_page >= paginationData.last_page ? 'disabled' : ''}
                                class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition">
                                Next
                            </button>
                        </div>

                        <div class="mt-3 text-center text-sm text-gray-600">
                            Showing ${paginationData.from || 0} to ${paginationData.to || 0} of ${paginationData.total || 0} entries
                        </div>
                    `;
                }

                document.getElementById('vouchersList').innerHTML = html;
            } catch (error) {
                alert('Error loading vouchers: ' + error.message);
            }
        }

        function applyVoucherDateFilter() {
            const fromDate = document.getElementById('voucherFromDate').value;
            const toDate = document.getElementById('voucherToDate').value;

            if (fromDate && toDate) {
                voucherDateFilters.from = fromDate;
                voucherDateFilters.to = toDate;
                loadVouchers(1); // Reset to page 1 when filtering
            } else {
                alert('⚠️ Please select both From and To dates');
            }
        }

        function clearVoucherDateFilter() {
            voucherDateFilters.from = '';
            voucherDateFilters.to = '';
            document.getElementById('voucherFromDate').value = '';
            document.getElementById('voucherToDate').value = '';
            loadVouchers(1);
        }

        async function showCreateVoucherForm() {
            // Load particulars first
            if (allParticulars.length === 0) {
                const response = await axios.get(`${API_BASE}/particulars`);
                allParticulars = response.data;
            }

            const particularsOptions = allParticulars.map(p =>
                `<option value="${p.id}">${p.name}</option>`
            ).join('');

            const classOptions = allClasses.map(cls =>
                `<option value="${cls.id}">${cls.name}</option>`
            ).join('');

            const formHtml = `
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto p-2">
                    <div class="bg-white rounded-lg p-4 max-w-4xl w-full shadow-2xl my-2 max-h-[95vh] overflow-y-auto">
                        <h3 class="text-xl font-bold mb-3 text-purple-600">Record New Fee Entry</h3>
                        <form onsubmit="createVoucher(event)" class="space-y-3">
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-bold mb-1">Date *</label>
                                    <input type="text" id="voucherDate" required
                                        class="w-full border-2 border-gray-300 rounded px-3 py-1.5 text-sm focus:border-purple-500 focus:outline-none"
                                        placeholder="Select date">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold mb-1">Particular *</label>
                                    <select id="voucherParticular" required onchange="loadParticularStudents()"
                                        class="w-full border-2 border-gray-300 rounded px-3 py-1.5 text-sm focus:border-purple-500 focus:outline-none">
                                        <option value="">-- Select Particular --</option>
                                        ${particularsOptions}
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold mb-1">Voucher Type *</label>
                                    <select id="voucherType" required onchange="updateVoucherTypeFields()"
                                        class="w-full border-2 border-gray-300 rounded px-3 py-1.5 text-sm focus:border-purple-500 focus:outline-none">
                                        <option value="">-- Select Type --</option>
                                        <option value="Sales">Sales (Charge Fee)</option>
                                        <option value="Receipt">Receipt (Payment)</option>
                                        <option value="Payment">Payment (Refund)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="border-2 border-blue-200 rounded p-3 bg-blue-50">
                                <h4 class="text-sm font-bold mb-2">Select Student</h4>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold mb-1">Search by Name</label>
                                        <input type="text" id="studentSearch" onkeyup="searchStudentsByName()"
                                            class="w-full border-2 border-gray-300 rounded px-3 py-1.5 text-sm focus:border-purple-500 focus:outline-none"
                                            placeholder="Type student name...">
                                        <div id="studentSearchResults" class="mt-1"></div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold mb-1">Select by Class</label>
                                        <select id="voucherClass" onchange="loadStudentsByClassForVoucher()"
                                            class="w-full border-2 border-gray-300 rounded px-3 py-1.5 text-sm focus:border-purple-500 focus:outline-none">
                                            <option value="">-- Select Class --</option>
                                            ${classOptions}
                                        </select>
                                        <div id="classStudentsResults" class="mt-1"></div>
                                    </div>
                                </div>
                                <input type="hidden" id="selectedStudentId" required>
                                <div id="selectedStudentDisplay" class="mt-2 p-2 bg-white rounded border-2 border-green-500 hidden">
                                    <p class="text-xs font-bold text-green-600">Selected Student:</p>
                                    <p id="selectedStudentName" class="font-bold text-sm"></p>
                                </div>
                            </div>

                            <div id="amountSection" class="hidden">
                                <!-- Payment Info Display -->
                                <div id="paymentInfoDisplay" class="bg-green-50 border-2 border-green-300 rounded p-2 mb-2 hidden">
                                    <h4 class="text-xs font-bold text-green-700 mb-2">💰 Payment Information</h4>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div class="bg-white p-2 rounded border">
                                            <p class="text-xs text-gray-600">Supposed Amount:</p>
                                            <p id="supposedAmount" class="text-sm font-bold text-blue-700">TSh 0.00</p>
                                        </div>
                                        <div class="bg-white p-2 rounded border">
                                            <p class="text-xs text-gray-600">Already Paid:</p>
                                            <p id="alreadyPaidAmount" class="text-sm font-bold text-green-700">TSh 0.00</p>
                                        </div>
                                        <div class="bg-yellow-50 p-2 rounded border border-yellow-300">
                                            <p class="text-xs text-gray-600">Outstanding:</p>
                                            <p id="outstandingBalance" class="text-sm font-bold text-red-700">TSh 0.00</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold mb-1">Amount (TSh) *</label>
                                        <input type="number" step="0.01" id="voucherAmount" required
                                            class="w-full border-2 border-gray-300 rounded px-3 py-1.5 text-sm focus:border-purple-500 focus:outline-none"
                                            placeholder="0.00">
                                    </div>
                                    <div id="bookSelection" class="hidden">
                                        <label class="block text-xs font-bold mb-1">Book/Account *</label>
                                        <select id="voucherBook"
                                            class="w-full border-2 border-gray-300 rounded px-3 py-1.5 text-sm focus:border-purple-500 focus:outline-none">
                                            <option value="">-- Select Book --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold mb-1">Notes (Optional)</label>
                                <textarea id="voucherNotes" rows="2"
                                    class="w-full border-2 border-gray-300 rounded px-3 py-1.5 text-sm focus:border-purple-500 focus:outline-none"
                                    placeholder="Add notes..."></textarea>
                            </div>

                            <div class="flex gap-3 pt-3 border-t-2">
                                <button type="submit" class="flex-1 bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded font-bold transition text-sm">
                                    💾 Save Entry
                                </button>
                                <button type="button" onclick="closeVoucherForm()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded font-bold transition text-sm">
                                    ✖️ Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            document.getElementById('voucherFormContainer').innerHTML = formHtml;

            // Initialize date picker
            flatpickr("#voucherDate", {
                dateFormat: "Y-m-d",
                defaultDate: "today"
            });
        }

        function updateVoucherTypeFields() {
            const voucherType = document.getElementById('voucherType').value;
            const amountSection = document.getElementById('amountSection');
            const bookSelection = document.getElementById('bookSelection');

            if (voucherType) {
                amountSection.classList.remove('hidden');

                if (voucherType === 'Receipt' || voucherType === 'Payment') {
                    bookSelection.classList.remove('hidden');
                    document.getElementById('voucherBook').required = true;

                    // Populate books dropdown
                    const bookSelect = document.getElementById('voucherBook');
                    bookSelect.innerHTML = '<option value="">-- Select Book --</option>' +
                        allBooks.map(b => `<option value="${b.id}">${b.name}</option>`).join('');
                } else {
                    bookSelection.classList.add('hidden');
                    document.getElementById('voucherBook').required = false;
                }
            } else {
                amountSection.classList.add('hidden');
            }
        }

        async function loadParticularStudents() {
            const particularId = document.getElementById('voucherParticular').value;
            if (!particularId) return;

            try {
                const response = await axios.get(`${API_BASE}/particulars/${particularId}`);
                const particular = response.data;
                filteredStudentsForVoucher = particular.students || [];

                // Reload payment info if student is already selected
                const studentId = document.getElementById('selectedStudentId').value;
                if (studentId) {
                    await loadPaymentInfo();
                }
            } catch (error) {
                console.error('Error loading particular students:', error);
            }
        }

        function searchStudentsByName() {
            const searchTerm = document.getElementById('studentSearch').value.toLowerCase();
            if (searchTerm.length < 2) {
                document.getElementById('studentSearchResults').innerHTML = '';
                return;
            }

            const matches = allStudents.filter(s =>
                s.name.toLowerCase().includes(searchTerm)
            ).slice(0, 5);

            let html = '<div class="space-y-1 max-h-32 overflow-y-auto">';
            matches.forEach(student => {
                html += `
                    <div onclick="selectStudent(${student.id}, '${student.name}')"
                        class="p-1.5 bg-white border rounded cursor-pointer hover:bg-purple-100">
                        <p class="font-bold text-xs">${student.name}</p>
                        <p class="text-xs text-gray-500">${student.student_reg_no} - ${student.class}</p>
                    </div>
                `;
            });
            html += '</div>';
            document.getElementById('studentSearchResults').innerHTML = html;
        }

        function loadStudentsByClassForVoucher() {
            const selectedClass = document.getElementById('voucherClass').value;
            if (!selectedClass) {
                document.getElementById('classStudentsResults').innerHTML = '';
                return;
            }

            const classStudents = allStudents.filter(s => s.class_id == selectedClass);

            let html = '<select class="w-full border-2 border-gray-300 rounded px-2 py-2 text-sm" onchange="selectStudentFromClass(this.value)">';
            html += '<option value="">-- Select Student --</option>';
            classStudents.forEach(student => {
                html += `<option value="${student.id}">${student.name} (${student.student_reg_no})</option>`;
            });
            html += '</select>';
            document.getElementById('classStudentsResults').innerHTML = html;
        }

        function selectStudentFromClass(studentId) {
            if (!studentId) return;
            const student = allStudents.find(s => s.id == studentId);
            if (student) {
                selectStudent(student.id, student.name);
            }
        }

        async function selectStudent(studentId, studentName) {
            document.getElementById('selectedStudentId').value = studentId;
            document.getElementById('selectedStudentName').textContent = studentName;
            document.getElementById('selectedStudentDisplay').classList.remove('hidden');
            document.getElementById('studentSearch').value = '';
            document.getElementById('studentSearchResults').innerHTML = '';

            // Load payment information for this student and particular
            await loadPaymentInfo();
        }

        async function loadPaymentInfo() {
            const studentId = document.getElementById('selectedStudentId').value;
            const particularId = document.getElementById('voucherParticular').value;

            if (!studentId || !particularId) {
                document.getElementById('paymentInfoDisplay').classList.add('hidden');
                return;
            }

            try {
                // Get particular details to find sales amount
                const particularResponse = await axios.get(`${API_BASE}/particulars/${particularId}`);
                const particular = particularResponse.data;

                // Find this student in the particular's students
                const studentInParticular = particular.students?.find(s => s.id == studentId);

                let supposedAmount = 0;
                let alreadyPaid = 0;

                if (studentInParticular) {
                    supposedAmount = studentInParticular.pivot.sales || 0;
                    alreadyPaid = studentInParticular.pivot.credit || 0;
                }

                const outstandingBalance = supposedAmount - alreadyPaid;

                // Display the information
                document.getElementById('supposedAmount').textContent = formatTSh(supposedAmount);
                document.getElementById('alreadyPaidAmount').textContent = formatTSh(alreadyPaid);
                document.getElementById('outstandingBalance').textContent = formatTSh(outstandingBalance);
                document.getElementById('paymentInfoDisplay').classList.remove('hidden');
            } catch (error) {
                console.error('Error loading payment info:', error);
                document.getElementById('paymentInfoDisplay').classList.add('hidden');
            }
        }

        async function createVoucher(event) {
            event.preventDefault();

            const date = document.getElementById('voucherDate').value;
            const studentId = document.getElementById('selectedStudentId').value;
            const particularId = document.getElementById('voucherParticular').value;
            const voucherType = document.getElementById('voucherType').value;
            const amount = parseFloat(document.getElementById('voucherAmount').value);
            const notes = document.getElementById('voucherNotes').value;
            const bookId = document.getElementById('voucherBook').value || null;

            let debit = 0, credit = 0;
            if (voucherType === 'Sales') {
                debit = amount;
            } else {
                credit = amount;
            }

            try {
                await axios.post(`${API_BASE}/vouchers`, {
                    date,
                    student_id: parseInt(studentId),
                    particular_id: parseInt(particularId),
                    book_id: bookId ? parseInt(bookId) : null,
                    voucher_type: voucherType,
                    debit,
                    credit,
                    notes
                });
                alert('✅ Voucher created successfully!');
                closeVoucherForm();
                loadVouchers();
            } catch (error) {
                alert('❌ Error: ' + (error.response?.data?.message || error.message));
            }
        }

        async function deleteVoucher(id) {
            if (confirm('⚠️ Are you sure you want to delete this voucher?')) {
                try {
                    await axios.delete(`${API_BASE}/vouchers/${id}`);
                    alert('✅ Voucher deleted successfully!');
                    loadVouchers(currentVoucherPage);
                } catch (error) {
                    alert('❌ Error: ' + (error.response?.data?.message || error.message));
                }
            }
        }

        function closeVoucherForm() {
            document.getElementById('voucherFormContainer').innerHTML = '';
        }

        // Scholarship Management Functions
        let academicYears = [];
        let selectedStudentForScholarship = null;
        let studentParticularsData = {};

        async function showScholarshipsManager() {
            try {
                // Load academic years first
                const yearsResponse = await axios.get(`${API_BASE}/academic-years`);
                academicYears = yearsResponse.data;

                const response = await axios.get(`${API_BASE}/scholarships`);
                const data = response.data;
                const scholarships = data.scholarships?.data || [];
                const summary = data.summary || {};

                const classOptions = allClasses.map(cls =>
                    `<option value="${cls.id}">${cls.name}</option>`
                ).join('');

                let html = `
                    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto p-2">
                        <div class="bg-white rounded-lg p-4 max-w-6xl w-full shadow-2xl my-2 max-h-[95vh] overflow-y-auto">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-bold text-amber-600">🎓 Scholarship Management</h3>
                                <button onclick="closeScholarshipManager()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                            </div>

                            <!-- Summary Cards -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div class="bg-amber-100 border-2 border-amber-400 rounded-lg p-4 text-center">
                                    <p class="text-sm text-amber-700">Total Active Scholarships</p>
                                    <p class="text-2xl font-bold text-amber-800">${summary.total_scholarships || 0}</p>
                                </div>
                                <div class="bg-green-100 border-2 border-green-400 rounded-lg p-4 text-center">
                                    <p class="text-sm text-green-700">Total Amount Forgiven</p>
                                    <p class="text-2xl font-bold text-green-800">${formatTSh(summary.total_forgiven_amount || 0)}</p>
                                </div>
                            </div>

                            <!-- Add New Scholarship Button -->
                            <div class="mb-4">
                                <button onclick="showAddScholarshipForm()" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg shadow transition">
                                    ➕ Assign Scholarship to Student
                                </button>
                            </div>

                            <!-- Add Scholarship Form (Hidden by default) -->
                            <div id="addScholarshipForm" class="hidden bg-amber-50 border-2 border-amber-300 rounded-lg p-4 mb-4">
                                <h4 class="text-lg font-bold text-amber-700 mb-3">Assign Scholarship to Student</h4>

                                <!-- Step 1: Select Student -->
                                <div class="bg-white border-2 border-gray-200 rounded-lg p-3 mb-4">
                                    <h5 class="font-bold text-gray-700 mb-2">Step 1: Select Student</h5>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-bold mb-1">Select Class</label>
                                            <select id="scholarshipClass" onchange="loadStudentsForScholarship()"
                                                class="w-full border-2 border-gray-300 rounded px-3 py-2 text-sm">
                                                <option value="">-- Select Class --</option>
                                                ${classOptions}
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold mb-1">Select Student *</label>
                                            <select id="scholarshipStudent" onchange="onStudentSelectedForScholarship()"
                                                class="w-full border-2 border-gray-300 rounded px-3 py-2 text-sm">
                                                <option value="">-- Select Student --</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 2: Student Fee Details by Academic Year -->
                                <div id="studentScholarshipDetails" class="hidden">
                                    <div id="selectedStudentInfo" class="bg-blue-50 border-2 border-blue-300 rounded-lg p-3 mb-4">
                                        <!-- Student info will be displayed here -->
                                    </div>

                                    <!-- Academic Years with Fees -->
                                    <div id="academicYearFees" class="space-y-4">
                                        <!-- Fees by academic year will be loaded here -->
                                    </div>

                                    <!-- Scholarship Name and Notes -->
                                    <div class="bg-white border-2 border-gray-200 rounded-lg p-3 mt-4">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-bold mb-1">Scholarship Name (Optional)</label>
                                                <input type="text" id="scholarshipName"
                                                    class="w-full border-2 border-gray-300 rounded px-3 py-2 text-sm"
                                                    placeholder="e.g., Academic Excellence Award, Government Scholarship">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold mb-1">Notes (Optional)</label>
                                                <input type="text" id="scholarshipNotes"
                                                    class="w-full border-2 border-gray-300 rounded px-3 py-2 text-sm"
                                                    placeholder="Additional notes...">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-3 pt-3">
                                    <button type="button" onclick="hideAddScholarshipForm()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded font-bold transition text-sm">
                                        ✖️ Cancel
                                    </button>
                                </div>
                            </div>

                            <!-- Scholarships List Grouped by Student -->
                            <div class="overflow-x-auto">
                                <h4 class="text-md font-bold text-gray-700 mb-2">📋 Students with Scholarships</h4>
                                <div id="scholarshipsGroupedList">
                `;

                // Group scholarships by student
                const groupedByStudent = {};
                scholarships.forEach(s => {
                    const studentId = s.student_id;
                    if (!groupedByStudent[studentId]) {
                        groupedByStudent[studentId] = {
                            student: s.student,
                            scholarships: []
                        };
                    }
                    groupedByStudent[studentId].scholarships.push(s);
                });

                if (Object.keys(groupedByStudent).length > 0) {
                    Object.values(groupedByStudent).forEach(group => {
                        // Group by academic year
                        const byYear = {};
                        group.scholarships.forEach(s => {
                            const yearId = s.academic_year_id || 'none';
                            const yearName = s.academic_year?.name || 'Unassigned';
                            if (!byYear[yearId]) {
                                byYear[yearId] = { name: yearName, items: [] };
                            }
                            byYear[yearId].items.push(s);
                        });

                        html += `
                            <div class="bg-white border-2 border-gray-200 rounded-lg mb-3 overflow-hidden">
                                <div class="bg-amber-100 p-3 flex justify-between items-center">
                                    <div>
                                        <span class="font-bold text-lg">${group.student?.name || 'N/A'}</span>
                                        <span class="text-sm text-gray-600 ml-2">(${group.student?.student_reg_no || 'N/A'})</span>
                                        <span class="text-sm text-gray-600 ml-2">- ${group.student?.school_class?.name || 'N/A'}</span>
                                    </div>
                                    <span class="bg-amber-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                        ${group.scholarships.filter(s => s.is_active).length} Active
                                    </span>
                                </div>
                                <div class="p-3">
                        `;

                        Object.values(byYear).forEach(year => {
                            html += `
                                <div class="mb-3">
                                    <div class="bg-blue-100 px-3 py-2 rounded-t font-bold text-blue-800 text-sm">
                                        📅 ${year.name}
                                    </div>
                                    <table class="w-full text-sm border">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="p-2 text-left border">Fee Type</th>
                                                <th class="p-2 text-right border">Original</th>
                                                <th class="p-2 text-right border">Forgiven</th>
                                                <th class="p-2 text-right border">Pays</th>
                                                <th class="p-2 text-center border">Type</th>
                                                <th class="p-2 text-center border">Status</th>
                                                <th class="p-2 text-center border">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                            `;

                            year.items.forEach(s => {
                                const statusClass = s.is_active ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-600';
                                const statusText = s.is_active ? 'Active' : 'Removed';
                                const typeClass = s.scholarship_type === 'full' ? 'bg-purple-200 text-purple-800' : 'bg-blue-200 text-blue-800';

                                html += `
                                    <tr class="border-t ${!s.is_active ? 'bg-gray-100 opacity-60' : ''}">
                                        <td class="p-2 border font-medium">${s.particular?.name || 'N/A'}</td>
                                        <td class="p-2 border text-right text-blue-600">${formatTSh(s.original_amount)}</td>
                                        <td class="p-2 border text-right text-red-600 font-bold">${formatTSh(s.forgiven_amount)}</td>
                                        <td class="p-2 border text-right text-green-600 font-bold">${formatTSh(s.remaining_amount)}</td>
                                        <td class="p-2 border text-center">
                                            <span class="px-2 py-1 rounded text-xs font-bold ${typeClass}">
                                                ${s.scholarship_type === 'full' ? 'Full' : 'Partial'}
                                            </span>
                                        </td>
                                        <td class="p-2 border text-center">
                                            <span class="px-2 py-1 rounded text-xs font-bold ${statusClass}">${statusText}</span>
                                        </td>
                                        <td class="p-2 border text-center">
                                            ${s.is_active ? `
                                                <button onclick="deactivateScholarship(${s.id})" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                                                    Remove
                                                </button>
                                            ` : '-'}
                                        </td>
                                    </tr>
                                `;
                            });

                            html += `
                                        </tbody>
                                    </table>
                                </div>
                            `;
                        });

                        html += `
                                </div>
                            </div>
                        `;
                    });
                } else {
                    html += `
                        <div class="bg-gray-100 p-8 text-center rounded-lg">
                            <p class="text-gray-500 text-lg">No scholarships assigned yet.</p>
                            <p class="text-gray-400 text-sm mt-2">Click "Assign Scholarship to Student" to add one.</p>
                        </div>
                    `;
                }

                html += `
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t flex justify-end">
                                <button onclick="closeScholarshipManager()" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg transition">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('voucherFormContainer').innerHTML = html;
            } catch (error) {
                alert('Error loading scholarships: ' + error.message);
                console.error(error);
            }
        }

        function showAddScholarshipForm() {
            document.getElementById('addScholarshipForm').classList.remove('hidden');
        }

        function hideAddScholarshipForm() {
            document.getElementById('addScholarshipForm').classList.add('hidden');
            document.getElementById('studentScholarshipDetails').classList.add('hidden');
            selectedStudentForScholarship = null;
            studentParticularsData = {};
        }

        function loadStudentsForScholarship() {
            const classId = document.getElementById('scholarshipClass').value;
            const studentSelect = document.getElementById('scholarshipStudent');

            if (!classId) {
                studentSelect.innerHTML = '<option value="">-- Select Student --</option>';
                return;
            }

            const classStudents = allStudents.filter(s => s.class_id == classId);

            let html = '<option value="">-- Select Student --</option>';
            classStudents.forEach(student => {
                html += `<option value="${student.id}">${student.name} (${student.student_reg_no})</option>`;
            });
            studentSelect.innerHTML = html;
        }

        async function onStudentSelectedForScholarship() {
            const studentId = document.getElementById('scholarshipStudent').value;
            if (!studentId) {
                document.getElementById('studentScholarshipDetails').classList.add('hidden');
                return;
            }

            try {
                // Get student details with particulars and existing scholarships
                const response = await axios.get(`${API_BASE}/scholarships/student/${studentId}/details`);
                const data = response.data;

                selectedStudentForScholarship = data.student;
                studentParticularsData = data.particulars_by_year || {};

                // Show student info
                document.getElementById('selectedStudentInfo').innerHTML = `
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="font-bold text-lg text-blue-800">${data.student.name}</span>
                            <span class="text-sm text-gray-600 ml-2">(${data.student.student_reg_no})</span>
                            <span class="text-sm text-gray-600 ml-2">- ${data.student.school_class?.name || 'N/A'}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-sm text-gray-600">Existing Scholarships: </span>
                            <span class="font-bold text-amber-600">${data.existing_scholarships?.length || 0}</span>
                        </div>
                    </div>
                `;

                // Render fees by academic year
                renderAcademicYearFees(data);

                document.getElementById('studentScholarshipDetails').classList.remove('hidden');
            } catch (error) {
                console.error('Error loading student details:', error);
                alert('Error loading student details: ' + (error.response?.data?.error || error.message));
            }
        }

        function renderAcademicYearFees(data) {
            const container = document.getElementById('academicYearFees');
            const particularsByYear = data.particulars_by_year || {};
            const existingScholarships = data.existing_scholarships || [];

            // Create a map of existing active scholarships
            const activeScholarshipMap = {};
            existingScholarships.filter(s => s.is_active).forEach(s => {
                const key = `${s.academic_year_id || 'none'}_${s.particular_id}`;
                activeScholarshipMap[key] = s;
            });

            let html = '';

            // Available academic years dropdown for adding more
            const yearOptions = academicYears.map(y => `<option value="${y.id}">${y.name}${y.is_current ? ' (Current)' : ''}</option>`).join('');

            Object.keys(particularsByYear).forEach(yearId => {
                const yearData = particularsByYear[yearId];

                html += `
                    <div class="bg-white border-2 border-blue-200 rounded-lg overflow-hidden">
                        <div class="bg-blue-100 px-4 py-2 flex justify-between items-center">
                            <span class="font-bold text-blue-800">📅 ${yearData.year_name}</span>
                            <span class="text-sm text-blue-600">${yearData.particulars.length} Fee Items</span>
                        </div>
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="p-2 text-left border">Fee Type</th>
                                    <th class="p-2 text-right border">Amount Required</th>
                                    <th class="p-2 text-right border">Amount Paid</th>
                                    <th class="p-2 text-right border">Balance</th>
                                    <th class="p-2 text-center border">Scholarship Status</th>
                                    <th class="p-2 text-center border">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                yearData.particulars.forEach(p => {
                    const scholarshipKey = `${yearId === 'none' ? 'none' : yearId}_${p.particular_id}`;
                    const existingScholarship = activeScholarshipMap[scholarshipKey];
                    const balance = p.sales - p.credit;

                    if (existingScholarship) {
                        // Has active scholarship
                        html += `
                            <tr class="border-t bg-amber-50">
                                <td class="p-2 border font-medium">${p.particular_name}</td>
                                <td class="p-2 border text-right text-gray-600 line-through">${formatTSh(existingScholarship.original_amount)}</td>
                                <td class="p-2 border text-right text-green-600">${formatTSh(p.credit)}</td>
                                <td class="p-2 border text-right font-bold ${balance > 0 ? 'text-red-600' : 'text-green-600'}">${formatTSh(balance)}</td>
                                <td class="p-2 border text-center">
                                    <span class="px-2 py-1 rounded text-xs font-bold bg-amber-200 text-amber-800">
                                        🎓 ${existingScholarship.scholarship_type === 'full' ? 'Full' : 'Partial'}: ${formatTSh(existingScholarship.forgiven_amount)}
                                    </span>
                                </td>
                                <td class="p-2 border text-center">
                                    <button onclick="removeScholarshipFromParticular(${existingScholarship.id}, '${p.particular_name}')"
                                        class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                                        ❌ Unassign
                                    </button>
                                </td>
                            </tr>
                        `;
                    } else {
                        // No scholarship - show assign buttons
                        html += `
                            <tr class="border-t hover:bg-gray-50">
                                <td class="p-2 border font-medium">${p.particular_name}</td>
                                <td class="p-2 border text-right text-blue-600">${formatTSh(p.sales)}</td>
                                <td class="p-2 border text-right text-green-600">${formatTSh(p.credit)}</td>
                                <td class="p-2 border text-right font-bold ${balance > 0 ? 'text-red-600' : 'text-green-600'}">${formatTSh(balance)}</td>
                                <td class="p-2 border text-center">
                                    <span class="text-gray-400 text-xs">No Scholarship</span>
                                </td>
                                <td class="p-2 border text-center">
                                    <div class="flex gap-1 justify-center">
                                        <button onclick="assignScholarship(${selectedStudentForScholarship.id}, ${p.particular_id}, ${yearId === 'none' ? 'null' : yearId}, ${p.sales}, 'full')"
                                            class="bg-purple-500 hover:bg-purple-600 text-white px-2 py-1 rounded text-xs" title="100% Scholarship">
                                            Full
                                        </button>
                                        <button onclick="showPartialScholarshipForm(${selectedStudentForScholarship.id}, ${p.particular_id}, ${yearId === 'none' ? 'null' : yearId}, ${p.sales}, '${p.particular_name}')"
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs" title="Partial Scholarship">
                                            Partial
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            });

            // Add "Add Another Academic Year" section
            html += `
                <div class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                    <p class="text-gray-600 mb-2">Need to assign scholarship for another academic year?</p>
                    <select id="addAnotherYearSelect" class="border-2 border-gray-300 rounded px-3 py-2 text-sm mr-2">
                        <option value="">-- Select Academic Year --</option>
                        ${yearOptions}
                    </select>
                    <button onclick="loadFeesForYear()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
                        ➕ Load Fees for Year
                    </button>
                </div>
            `;

            container.innerHTML = html;
        }

        async function assignScholarship(studentId, particularId, academicYearId, originalAmount, type) {
            const scholarshipName = document.getElementById('scholarshipName')?.value || null;
            const notes = document.getElementById('scholarshipNotes')?.value || null;

            const forgivenAmount = type === 'full' ? originalAmount : 0;

            try {
                await axios.post(`${API_BASE}/scholarships`, {
                    student_id: studentId,
                    particular_id: particularId,
                    academic_year_id: academicYearId,
                    original_amount: originalAmount,
                    forgiven_amount: forgivenAmount,
                    scholarship_type: type,
                    scholarship_name: scholarshipName,
                    applied_date: new Date().toISOString().split('T')[0],
                    notes: notes
                });

                alert('✅ Scholarship assigned successfully!');
                onStudentSelectedForScholarship(); // Refresh the view
            } catch (error) {
                alert('❌ Error: ' + (error.response?.data?.error || error.message));
            }
        }

        function showPartialScholarshipForm(studentId, particularId, academicYearId, originalAmount, particularName) {
            const amount = prompt(`Enter scholarship amount for "${particularName}" (Original: ${formatTSh(originalAmount)}):`);

            if (amount === null) return;

            const forgivenAmount = parseFloat(amount);
            if (isNaN(forgivenAmount) || forgivenAmount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            if (forgivenAmount > originalAmount) {
                alert('Scholarship amount cannot exceed the original fee amount');
                return;
            }

            const scholarshipName = document.getElementById('scholarshipName')?.value || null;
            const notes = document.getElementById('scholarshipNotes')?.value || null;

            axios.post(`${API_BASE}/scholarships`, {
                student_id: studentId,
                particular_id: particularId,
                academic_year_id: academicYearId,
                original_amount: originalAmount,
                forgiven_amount: forgivenAmount,
                scholarship_type: 'partial',
                scholarship_name: scholarshipName,
                applied_date: new Date().toISOString().split('T')[0],
                notes: notes
            }).then(() => {
                alert('✅ Partial scholarship assigned successfully!');
                onStudentSelectedForScholarship();
            }).catch(error => {
                alert('❌ Error: ' + (error.response?.data?.error || error.message));
            });
        }

        async function removeScholarshipFromParticular(scholarshipId, particularName) {
            if (!confirm(`⚠️ Remove scholarship from "${particularName}"? The original fee amount will be restored.`)) {
                return;
            }

            try {
                await axios.post(`${API_BASE}/scholarships/${scholarshipId}/deactivate`);
                alert('✅ Scholarship removed successfully!');
                onStudentSelectedForScholarship();
            } catch (error) {
                alert('❌ Error: ' + (error.response?.data?.error || error.message));
            }
        }

        async function loadFeesForYear() {
            const yearId = document.getElementById('addAnotherYearSelect').value;
            if (!yearId) {
                alert('Please select an academic year');
                return;
            }

            if (!selectedStudentForScholarship) {
                alert('Please select a student first');
                return;
            }

            try {
                // This would need a new API endpoint to assign fees for a year
                alert('Feature: This would load/assign fees for the selected academic year. The student needs to have fees assigned for this year first.');
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        async function deactivateScholarship(scholarshipId) {
            if (!confirm('⚠️ Are you sure you want to remove this scholarship? The original fee amount will be restored.')) {
                return;
            }

            try {
                await axios.post(`${API_BASE}/scholarships/${scholarshipId}/deactivate`);
                alert('✅ Scholarship removed successfully!');
                showScholarshipsManager(); // Refresh the list
            } catch (error) {
                alert('❌ Error: ' + (error.response?.data?.error || error.message));
            }
        }

        function closeScholarshipManager() {
            document.getElementById('voucherFormContainer').innerHTML = '';
            selectedStudentForScholarship = null;
            studentParticularsData = {};
        }
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payroll Management - Darasa Finance</title>
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
                    <h1 class="text-2xl font-bold">👥 Payroll Management</h1>
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
                <h2 class="text-3xl font-bold text-violet-600">👥 Payroll Management</h2>
                <div class="flex gap-3">
                    <a href="/api/staff/csv/template" download class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded transition">
                        📥 Download CSV Template
                    </a>
                    <button onclick="showUploadCsvModal()" class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded transition">
                        📤 Upload Staff CSV
                    </button>
                    <button onclick="showAddStaffForm()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition">
                        ➕ Add Staff
                    </button>
                    <button onclick="showProcessPayrollForm()" class="bg-violet-500 hover:bg-violet-600 text-white px-4 py-2 rounded transition">
                        💰 Process Payroll
                    </button>
                </div>
            </div>

            <!-- Staff List -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-xl font-bold text-gray-700">Staff Members</h3>
                    <input type="text" id="staffSearch" placeholder="Search staff..." onkeyup="filterStaff()" class="border-2 border-gray-300 rounded-lg px-4 py-2 w-64">
                </div>
                <div id="staffGrid" class="overflow-x-auto"></div>
            </div>

            <!-- Recent Payroll Entries -->
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-700 mb-3">Recent Payroll</h3>
                <div id="payrollTable"></div>
            </div>
        </div>
    </div>

    <!-- Add Staff Modal -->
    <div id="addStaffModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg p-6 max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <h3 class="text-2xl font-bold text-blue-600 mb-4">➕ Add Staff Member</h3>
            <form id="addStaffForm" onsubmit="submitAddStaffForm(event)">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold mb-2">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" id="staff_name" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Staff ID <span class="text-red-500">*</span></label>
                        <input type="text" id="staff_id" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Position <span class="text-red-500">*</span></label>
                        <input type="text" id="staff_position" required placeholder="e.g., Teacher, Accountant" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Department</label>
                        <input type="text" id="staff_department" placeholder="e.g., Finance, Academic" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Monthly Salary (TSh) <span class="text-red-500">*</span></label>
                        <input type="number" id="staff_salary" required step="0.01" min="0" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Date Joined <span class="text-red-500">*</span></label>
                        <input type="date" id="staff_date_joined" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Phone</label>
                        <input type="tel" id="staff_phone" placeholder="+255..." class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Email</label>
                        <input type="email" id="staff_email" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Bank Name</label>
                        <input type="text" id="staff_bank_name" placeholder="e.g., CRDB, NMB" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Bank Account Number</label>
                        <input type="text" id="staff_bank_account" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block font-bold mb-2">Notes</label>
                    <textarea id="staff_notes" rows="2" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2"></textarea>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded font-bold transition">
                        ✅ Add Staff Member
                    </button>
                    <button type="button" onclick="closeAddStaffModal()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded font-bold transition">
                        ❌ Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Process Payroll Modal -->
    <div id="processPayrollModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg p-6 max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            <h3 class="text-2xl font-bold text-violet-600 mb-4">💰 Process Payroll</h3>
            <form id="processPayrollForm" onsubmit="submitProcessPayrollForm(event)">
                <!-- Staff Search Bar -->
                <div class="mb-4 col-span-2">
                    <label class="block font-bold mb-2">Search Staff</label>
                    <input type="text" id="payroll_staff_search"
                           placeholder="Search by name or staff ID..."
                           onkeyup="filterPayrollStaffList()"
                           class="w-full border-2 border-blue-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold mb-2">Staff Member <span class="text-red-500">*</span></label>
                        <select id="payroll_staff_id" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                            <option value="">Select Staff...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Amount (TSh) <span class="text-red-500">*</span></label>
                        <input type="number" id="payroll_amount" required step="0.01" min="0" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Month <span class="text-red-500">*</span></label>
                        <select id="payroll_month" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                            <option value="">Select Month...</option>
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Year <span class="text-red-500">*</span></label>
                        <input type="number" id="payroll_year" required min="2020" max="2099" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Payment Date <span class="text-red-500">*</span></label>
                        <input type="date" id="payroll_payment_date" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Book <span class="text-red-500">*</span></label>
                        <select id="payroll_book_id" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                            <option value="">Select Book...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Payment Method <span class="text-red-500">*</span></label>
                        <select id="payroll_payment_method" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-2">Reference Number</label>
                        <input type="text" id="payroll_reference" placeholder="Transaction ref" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block font-bold mb-2">Notes</label>
                    <textarea id="payroll_notes" rows="2" class="w-full border-2 border-gray-300 rounded-lg px-4 py-2"></textarea>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded font-bold transition">
                        ✅ Process Payroll
                    </button>
                    <button type="button" onclick="closeProcessPayrollModal()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded font-bold transition">
                        ❌ Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Upload CSV Modal -->
    <div id="uploadCsvModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-2xl font-bold text-teal-600 mb-4">📤 Upload Staff CSV</h3>
            <form id="uploadCsvForm" onsubmit="submitUploadCsvForm(event)">
                <div class="mb-4">
                    <label class="block font-bold mb-2">Select CSV File <span class="text-red-500">*</span></label>
                    <input type="file" id="csv_file" accept=".csv" required class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                    <p class="text-xs text-gray-500 mt-2">Format: name, staff_id, position, department, monthly_salary, phone, email, bank_name, bank_account, date_joined, notes</p>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded font-bold transition">
                        ✅ Upload CSV
                    </button>
                    <button type="button" onclick="closeUploadCsvModal()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded font-bold transition">
                        ❌ Cancel
                    </button>
                </div>
            </form>
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

        // Load payroll data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadPayrollData();
        });

        async function loadPayrollData() {
            try {
                const [staffResponse, payrollResponse] = await Promise.all([
                    axios.get(`${API_BASE}/staff`),
                    axios.get(`${API_BASE}/payroll`)
                ]);

                const staffData = staffResponse.data;
                const payrollData = payrollResponse.data;
                const staff = staffData.staff?.data || staffData.staff || [];
                const payroll = payrollData.payroll_entries?.data || payrollData.payroll_entries || [];

                // Display Staff in table format for better scalability
                let staffHtml = '';
                if (staff.length === 0) {
                    staffHtml = '<p class="text-center text-gray-500 p-6">No staff members found. Click "Add Staff" to add one.</p>';
                } else {
                    staffHtml = `
                        <table class="w-full border-2 border-gray-300 rounded-lg" id="staffTable">
                            <thead class="bg-violet-100">
                                <tr>
                                    <th class="p-3 text-left">Staff ID</th>
                                    <th class="p-3 text-left">Name</th>
                                    <th class="p-3 text-left">Position</th>
                                    <th class="p-3 text-left">Department</th>
                                    <th class="p-3 text-right">Monthly Salary</th>
                                    <th class="p-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    staff.forEach(member => {
                        const statusBadge = member.status === 'active'
                            ? '<span class="px-2 py-1 bg-green-200 text-green-800 rounded text-xs font-bold">Active</span>'
                            : '<span class="px-2 py-1 bg-gray-200 text-gray-800 rounded text-xs font-bold">Inactive</span>';

                        staffHtml += `
                            <tr class="border-t hover:bg-violet-50">
                                <td class="p-3 font-mono text-sm">${member.staff_id}</td>
                                <td class="p-3 font-semibold">${member.name}</td>
                                <td class="p-3">${member.position}</td>
                                <td class="p-3">${member.department || 'N/A'}</td>
                                <td class="p-3 text-right font-bold text-violet-700">${formatTSh(member.monthly_salary || 0)}</td>
                                <td class="p-3">${statusBadge}</td>
                            </tr>
                        `;
                    });
                    staffHtml += `
                            </tbody>
                        </table>
                    `;
                }
                document.getElementById('staffGrid').innerHTML = staffHtml;

                // Display Payroll Table
                let payrollHtml = `
                    <div class="overflow-x-auto">
                        <table class="w-full border-2 border-gray-300 rounded-lg">
                            <thead class="bg-violet-100">
                                <tr>
                                    <th class="p-3 text-left">Period</th>
                                    <th class="p-3 text-left">Staff</th>
                                    <th class="p-3 text-right">Gross Salary</th>
                                    <th class="p-3 text-right">Deductions</th>
                                    <th class="p-3 text-right">Net Pay</th>
                                    <th class="p-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                if (payroll.length === 0) {
                    payrollHtml += `
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-500">No payroll entries found. Click "Process Payroll" to create one.</td>
                        </tr>
                    `;
                } else {
                    const monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June',
                                        'July', 'August', 'September', 'October', 'November', 'December'];

                    payroll.forEach(entry => {
                        const statusBadge = entry.payment_status === 'paid'
                            ? '<span class="px-2 py-1 bg-green-200 text-green-800 rounded text-xs font-bold">Paid</span>'
                            : '<span class="px-2 py-1 bg-yellow-200 text-yellow-800 rounded text-xs font-bold">Pending</span>';

                        const staffName = entry.staff?.name || 'N/A';
                        const amount = entry.amount || 0;
                        const monthDisplay = monthNames[entry.month] || entry.month;
                        const yearDisplay = entry.year || '';

                        payrollHtml += `
                            <tr class="border-t hover:bg-violet-50">
                                <td class="p-3">${monthDisplay} ${yearDisplay}</td>
                                <td class="p-3 font-semibold">${staffName}</td>
                                <td class="p-3 text-right font-bold">${formatTSh(amount)}</td>
                                <td class="p-3 text-right text-red-600">TSh 0.00</td>
                                <td class="p-3 text-right font-bold text-green-700">${formatTSh(amount)}</td>
                                <td class="p-3">${statusBadge}</td>
                            </tr>
                        `;
                    });
                }

                payrollHtml += `
                            </tbody>
                        </table>
                    </div>
                `;

                document.getElementById('payrollTable').innerHTML = payrollHtml;
            } catch (error) {
                alert('Error loading payroll data: ' + error.message);
            }
        }

        let allStaff = [];
        let allBooks = [];

        async function loadBooksAndStaff() {
            try {
                const booksResponse = await axios.get(`${API_BASE}/books`);
                allBooks = booksResponse.data;
            } catch (error) {
                console.error('Error loading books:', error);
            }
        }

        function showAddStaffForm() {
            document.getElementById('addStaffModal').classList.remove('hidden');
            // Set today's date as default
            document.getElementById('staff_date_joined').valueAsDate = new Date();
        }

        function closeAddStaffModal() {
            document.getElementById('addStaffModal').classList.add('hidden');
            document.getElementById('addStaffForm').reset();
        }

        async function submitAddStaffForm(event) {
            event.preventDefault();

            const formData = {
                name: document.getElementById('staff_name').value,
                staff_id: document.getElementById('staff_id').value,
                position: document.getElementById('staff_position').value,
                department: document.getElementById('staff_department').value,
                monthly_salary: parseFloat(document.getElementById('staff_salary').value),
                date_joined: document.getElementById('staff_date_joined').value,
                phone: document.getElementById('staff_phone').value,
                email: document.getElementById('staff_email').value,
                bank_name: document.getElementById('staff_bank_name').value,
                bank_account: document.getElementById('staff_bank_account').value,
                notes: document.getElementById('staff_notes').value,
                status: 'active'
            };

            try {
                await axios.post(`${API_BASE}/staff`, formData);
                alert('✅ Staff member added successfully');
                closeAddStaffModal();
                loadPayrollData(); // Reload the list
            } catch (error) {
                alert('❌ Error adding staff: ' + (error.response?.data?.message || error.message));
            }
        }

        async function showProcessPayrollForm() {
            await loadBooksAndStaff();

            // Load staff data fresh
            try {
                const staffResponse = await axios.get(`${API_BASE}/staff`);
                const staffData = staffResponse.data;
                allStaff = staffData.staff?.data || staffData.staff || [];
            } catch (error) {
                console.error('Error loading staff:', error);
            }

            // Populate staff dropdown
            const staffSelect = document.getElementById('payroll_staff_id');
            staffSelect.innerHTML = '<option value="">Select Staff...</option>';
            allStaff.forEach(staff => {
                staffSelect.innerHTML += `<option value="${staff.id}" data-salary="${staff.monthly_salary}">${staff.name} - ${staff.position}</option>`;
            });

            // Populate books dropdown
            const bookSelect = document.getElementById('payroll_book_id');
            bookSelect.innerHTML = '<option value="">Select Book...</option>';
            allBooks.forEach(book => {
                bookSelect.innerHTML += `<option value="${book.id}">${book.name}</option>`;
            });

            // Set defaults
            const now = new Date();
            document.getElementById('payroll_month').value = now.getMonth() + 1; // JavaScript months are 0-indexed
            document.getElementById('payroll_year').value = now.getFullYear();
            document.getElementById('payroll_payment_date').valueAsDate = now;

            // Auto-fill salary when staff is selected
            staffSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const salary = selectedOption.getAttribute('data-salary');
                if (salary) {
                    document.getElementById('payroll_amount').value = salary;
                }
            });

            document.getElementById('processPayrollModal').classList.remove('hidden');
        }

        function filterPayrollStaffList() {
            const searchValue = document.getElementById('payroll_staff_search').value.toLowerCase();
            const staffSelect = document.getElementById('payroll_staff_id');
            const options = staffSelect.getElementsByTagName('option');

            for (let i = 0; i < options.length; i++) {
                const text = options[i].text.toLowerCase();
                const value = options[i].value;

                // Always show the first "Select Staff..." option
                if (value === '' || text.includes(searchValue)) {
                    options[i].style.display = '';
                } else {
                    options[i].style.display = 'none';
                }
            }
        }

        function closeProcessPayrollModal() {
            document.getElementById('processPayrollModal').classList.add('hidden');
            document.getElementById('processPayrollForm').reset();
        }

        async function submitProcessPayrollForm(event) {
            event.preventDefault();

            const formData = {
                staff_id: parseInt(document.getElementById('payroll_staff_id').value),
                amount: parseFloat(document.getElementById('payroll_amount').value),
                month: document.getElementById('payroll_month').value,
                year: parseInt(document.getElementById('payroll_year').value),
                payment_date: document.getElementById('payroll_payment_date').value,
                book_id: parseInt(document.getElementById('payroll_book_id').value),
                payment_method: document.getElementById('payroll_payment_method').value,
                reference_number: document.getElementById('payroll_reference').value,
                notes: document.getElementById('payroll_notes').value
            };

            try {
                await axios.post(`${API_BASE}/payroll`, formData);
                alert('✅ Payroll processed successfully');
                closeProcessPayrollModal();
                loadPayrollData(); // Reload the list
            } catch (error) {
                alert('❌ Error processing payroll: ' + (error.response?.data?.message || error.message));
            }
        }

        // Filter staff table based on search input
        function filterStaff() {
            const searchValue = document.getElementById('staffSearch').value.toLowerCase();
            const table = document.getElementById('staffTable');

            if (!table) return;

            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (let row of rows) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            }
        }

        // Show upload CSV modal
        function showUploadCsvModal() {
            document.getElementById('uploadCsvModal').classList.remove('hidden');
        }

        // Close upload CSV modal
        function closeUploadCsvModal() {
            document.getElementById('uploadCsvModal').classList.add('hidden');
            document.getElementById('uploadCsvForm').reset();
        }

        // Submit CSV upload
        async function submitUploadCsvForm(event) {
            event.preventDefault();

            const fileInput = document.getElementById('csv_file');
            const file = fileInput.files[0];

            if (!file) {
                alert('⚠️ Please select a CSV file');
                return;
            }

            const formData = new FormData();
            formData.append('csv_file', file);

            try {
                const response = await axios.post(`${API_BASE}/staff/csv/upload`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });

                const data = response.data;
                let message = `✅ ${data.message}`;

                if (data.errors && data.errors.length > 0) {
                    message += '\n\nErrors:\n' + data.errors.join('\n');
                }

                alert(message);
                closeUploadCsvModal();
                loadPayrollData(); // Reload the staff list
            } catch (error) {
                alert('❌ Error uploading CSV: ' + (error.response?.data?.message || error.message));
            }
        }
    </script>
</body>
</html>

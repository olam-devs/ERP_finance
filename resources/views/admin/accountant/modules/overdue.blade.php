<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Overdue Payments - Darasa Finance</title>
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
                    <h1 class="text-2xl font-bold">⚠️ Overdue Payments</h1>
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
                <h2 class="text-3xl font-bold text-red-600">⚠️ Overdue Payments</h2>
                <div class="flex gap-3">
                    <button onclick="sendOverdueReminders()" class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-2 rounded font-bold transition">
                        📱 Send SMS Reminders
                    </button>
                    <button onclick="loadOverdueData()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition">
                        🔄 Refresh
                    </button>
                </div>
            </div>

            <!-- SMS Reminder Modal -->
            <div id="smsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
                    <h3 class="text-2xl font-bold text-purple-600 mb-4">📱 Send Overdue Payment Reminders</h3>

                    <div class="mb-4">
                        <label class="block font-bold mb-2">Select Language:</label>
                        <div class="flex gap-4">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="language" value="english" checked class="mr-2">
                                <span>English</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="language" value="swahili" class="mr-2">
                                <span>Swahili</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block font-bold mb-2">Message Template (You can edit):</label>
                        <textarea id="messagePreview" class="w-full bg-gray-50 p-4 rounded border border-gray-300 text-sm" rows="12"></textarea>
                        <p class="text-xs text-gray-600 mt-1">
                            <strong>Variables:</strong> {student_name}, {particulars}, {total}
                        </p>
                    </div>

                    <div class="mb-4 bg-blue-50 border-2 border-blue-300 rounded p-4">
                        <p class="text-sm text-blue-800">
                            <strong>Note:</strong> SMS will only be sent to students whose parents have phone numbers saved in the system.
                            You will receive a report showing which students were skipped due to missing numbers.
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button onclick="confirmSendReminders()" class="flex-1 bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded font-bold transition">
                            ✅ Confirm & Send
                        </button>
                        <button onclick="closeSmsModal()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded font-bold transition">
                            ❌ Cancel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Result Modal -->
            <div id="resultModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
                    <h3 class="text-2xl font-bold text-green-600 mb-4">📊 SMS Sending Report</h3>
                    <div id="resultContent"></div>
                    <button onclick="closeResultModal()" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded font-bold transition mt-4">
                        Close
                    </button>
                </div>
            </div>

            <div id="overdueContent"></div>
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

        // Load overdue data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadOverdueData();
            updateMessagePreview();

            // Update preview when language changes
            document.querySelectorAll('input[name="language"]').forEach(radio => {
                radio.addEventListener('change', updateMessagePreview);
            });
        });

        let overdueStudentsData = [];

        function updateMessagePreview() {
            const language = document.querySelector('input[name="language"]:checked').value;

            const templates = {
                english: `Dear Parent of {student_name},

This is a reminder that your child has overdue fee payments.

Overdue Fees:
{particulars}

Total Amount Due: TSH {total}

Please make payment as soon as possible to avoid inconvenience.

Thank you.
Darasa Finance`,
                swahili: `Mzazi Mpendwa wa {student_name},

Hii ni ukumbusho kwamba mtoto wako ana malipo ya ada yaliyopita muda.

Ada Zilizopita Muda:
{particulars}

Jumla ya Kiasi: TSH {total}

Tafadhali fanya malipo haraka iwezekanavyo ili kuepuka usumbufu.

Asante.
Darasa Finance`
            };

            document.getElementById('messagePreview').value = templates[language];
        }

        function sendOverdueReminders() {
            if (overdueStudentsData.length === 0) {
                alert('No overdue students found. Please refresh the data first.');
                return;
            }

            document.getElementById('smsModal').classList.remove('hidden');
        }

        function closeSmsModal() {
            document.getElementById('smsModal').classList.add('hidden');
        }

        function closeResultModal() {
            document.getElementById('resultModal').classList.add('hidden');
        }

        async function confirmSendReminders() {
            const language = document.querySelector('input[name="language"]:checked').value;
            const customTemplate = document.getElementById('messagePreview').value;

            closeSmsModal();

            // Show loading message
            document.getElementById('resultContent').innerHTML = '<p class="text-center py-4">Sending SMS reminders... Please wait.</p>';
            document.getElementById('resultModal').classList.remove('hidden');

            try {
                const response = await axios.post('/accountant/sms/send-overdue-reminders', {
                    language: language,
                    custom_template: customTemplate,
                    students: overdueStudentsData
                });

                const result = response.data;

                let html = `
                    <div class="mb-4 bg-green-50 border-2 border-green-300 rounded p-4">
                        <h4 class="font-bold text-green-800 mb-2">✅ Successfully Sent</h4>
                        <p class="text-green-700">${result.success_count} SMS messages sent successfully</p>
                    </div>
                `;

                if (result.skipped_count > 0) {
                    html += `
                        <div class="mb-4 bg-yellow-50 border-2 border-yellow-300 rounded p-4">
                            <h4 class="font-bold text-yellow-800 mb-2">⚠️ Skipped (No Phone Number)</h4>
                            <p class="text-yellow-700 mb-2">${result.skipped_count} students skipped due to missing parent phone numbers:</p>
                            <div class="bg-white rounded p-3 max-h-40 overflow-y-auto">
                                <ul class="list-disc list-inside text-sm">
                    `;

                    result.skipped_students.forEach(student => {
                        html += `<li>${student.name} (${student.class})</li>`;
                    });

                    html += `
                                </ul>
                            </div>
                            <p class="text-sm text-yellow-700 mt-2">
                                <strong>Action:</strong> Add phone numbers for these students in
                                <a href="{{ route('accountant.phone-numbers') }}" class="text-blue-600 underline">Manage Phone Numbers</a>
                            </p>
                        </div>
                    `;
                }

                if (result.failed_count > 0) {
                    html += `
                        <div class="bg-red-50 border-2 border-red-300 rounded p-4">
                            <h4 class="font-bold text-red-800 mb-2">❌ Failed</h4>
                            <p class="text-red-700">${result.failed_count} SMS messages failed to send</p>
                        </div>
                    `;
                }

                document.getElementById('resultContent').innerHTML = html;
            } catch (error) {
                document.getElementById('resultContent').innerHTML = `
                    <div class="bg-red-50 border-2 border-red-300 rounded p-4">
                        <h4 class="font-bold text-red-800 mb-2">❌ Error</h4>
                        <p class="text-red-700">${error.response?.data?.message || error.message}</p>
                    </div>
                `;
            }
        }

        async function loadOverdueData() {
            try {
                const response = await axios.get(`${API_BASE}/overdue-amounts`);
                const data = response.data;

                // Store overdue students data for SMS functionality
                overdueStudentsData = data.overdue_by_student;

                let html = `
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-red-50 border-2 border-red-300 rounded-lg p-4">
                            <h4 class="text-sm font-bold text-red-700 mb-2">Total Overdue Amount</h4>
                            <p class="text-3xl font-bold text-red-600">${formatTSh(data.summary.total_overdue_amount)}</p>
                        </div>
                        <div class="bg-orange-50 border-2 border-orange-300 rounded-lg p-4">
                            <h4 class="text-sm font-bold text-orange-700 mb-2">Students with Overdue</h4>
                            <p class="text-3xl font-bold text-orange-600">${Math.round(data.summary.total_students_with_overdue)}</p>
                        </div>
                        <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-4">
                            <h4 class="text-sm font-bold text-yellow-700 mb-2">Overdue Particulars</h4>
                            <p class="text-3xl font-bold text-yellow-600">${Math.round(data.summary.total_overdue_particulars)}</p>
                        </div>
                    </div>

                    <!-- By Particular Summary -->
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-700 mb-3">📋 Overdue by Particular</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                `;

                data.overdue_by_particular.forEach(item => {
                    html += `
                        <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-4">
                            <h4 class="font-bold text-lg text-yellow-800">${item.particular.name}</h4>
                            <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <p class="text-gray-600">Students Overdue:</p>
                                    <p class="font-bold text-lg">${item.total_students_overdue}</p>
                                </div>
                                <div>
                                    <p class="text-gray-600">Amount Overdue:</p>
                                    <p class="font-bold text-lg text-red-600">${formatTSh(item.total_amount_overdue)}</p>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += `
                        </div>
                    </div>

                    <!-- Detailed Student List -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="text-xl font-bold text-gray-700">👥 Students with Overdue Payments</h3>
                            <input type="text" id="studentSearch" placeholder="🔍 Search student by name, reg no, or class..."
                                   class="border-2 border-gray-300 rounded-lg px-4 py-2 w-64 md:w-96"
                                   onkeyup="filterStudents()">
                        </div>
                        <div class="overflow-x-auto">
                            <table id="overdueTable" class="w-full border-2 border-gray-300 rounded-lg">
                                <thead class="bg-red-100">
                                    <tr>
                                        <th class="p-3 text-left">Student</th>
                                        <th class="p-3 text-left">Class</th>
                                        <th class="p-3 text-left">Overdue Particulars</th>
                                        <th class="p-3 text-right">Total Overdue</th>
                                    </tr>
                                </thead>
                                <tbody id="overdueTableBody">
                `;

                data.overdue_by_student.forEach(student => {
                    const particularsDetails = student.overdue_particulars.map(p =>
                        `${p.particular_name}: ${formatTSh(p.amount_due)} (${p.days_overdue} days overdue)`
                    ).join('<br>');

                    html += `
                        <tr class="border-t hover:bg-red-50">
                            <td class="p-3">
                                <p class="font-bold">${student.student.name}</p>
                                <p class="text-xs text-gray-500">${student.student.reg_no}</p>
                            </td>
                            <td class="p-3">${student.student.class}</td>
                            <td class="p-3 text-sm">${particularsDetails}</td>
                            <td class="p-3 text-right font-bold text-red-600 text-lg">${formatTSh(student.total_overdue)}</td>
                        </tr>
                    `;
                });

                html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;

                document.getElementById('overdueContent').innerHTML = html;
            } catch (error) {
                alert('Error loading overdue data: ' + error.message);
            }
        }

        // Filter students based on search input
        function filterStudents() {
            const searchValue = document.getElementById('studentSearch')?.value.toLowerCase() || '';
            const table = document.getElementById('overdueTableBody');

            if (!table) return;

            const rows = table.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const text = row.textContent.toLowerCase();

                if (text.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Particulars Management - Darasa Finance</title>
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
                    <h1 class="text-2xl font-bold">📋 Particulars Management</h1>
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
                <h2 class="text-3xl font-bold text-green-600">📋 Particulars Management</h2>
                <button onclick="showCreateParticularForm()" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg shadow transition">
                    ➕ Create New Particular
                </button>
            </div>
            <div id="particularsList" class="mt-4"></div>
            <div id="particularFormContainer"></div>
        </div>
    </div>

    <!-- Module Scripts -->
    <script>
        const API_BASE = '/api';
        let allBooks = [];
        let allParticulars = [];
        let allStudents = [];
        let allClasses = [];

        // Configure axios
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        axios.defaults.headers.common['Accept'] = 'application/json';
        axios.defaults.withCredentials = true;

        // Load initial data on page load
        document.addEventListener('DOMContentLoaded', async function() {
            await loadInitialData();
            await loadParticulars();
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

        async function loadParticulars() {
            try {
                const response = await axios.get(`${API_BASE}/particulars`);
                allParticulars = response.data;

                let html = '<div class="space-y-4">';
                allParticulars.forEach(particular => {
                    html += `
                        <div class="border-2 border-green-300 rounded-lg p-6 bg-green-50">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="font-bold text-xl text-green-700">${particular.name}</h3>
                                    <p class="text-sm text-gray-600 mt-2">📚 Books: ${particular.book_ids?.length || 0} assigned</p>
                                    <p class="text-sm text-gray-600">👥 Students: ${particular.students?.length || 0} assigned</p>
                                </div>
                                <div class="flex gap-2">
                                    <button onclick='showEditParticularForm(${JSON.stringify(particular)})'
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition">
                                        ✏️ Edit
                                    </button>
                                    <button onclick="showAssignStudentsForm(${particular.id}, '${particular.name}')"
                                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition">
                                        👥 Assign New
                                    </button>
                                    <button onclick="showExistingAssignmentsForm(${particular.id}, '${particular.name}')"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
                                        📋 Existing Assignments
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                document.getElementById('particularsList').innerHTML = html;
            } catch (error) {
                alert('Error loading particulars: ' + error.message);
            }
        }

        function showCreateParticularForm() {
            const booksCheckboxes = allBooks.map(book =>
                `<label class="flex items-center gap-2 p-3 bg-gray-50 hover:bg-blue-50 rounded border-2 border-gray-300 hover:border-blue-400 cursor-pointer transition">
                    <input type="checkbox" class="book-checkbox w-5 h-5 rounded border-gray-300" value="${book.id}">
                    <span class="font-semibold">${book.name}</span>
                    ${book.is_cash_book ? '<span class="text-xs text-green-600 font-bold ml-auto">💵 Cash Book</span>' : '<span class="text-xs text-blue-600 font-bold ml-auto">🏦 Bank</span>'}
                </label>`
            ).join('');

            const formHtml = `
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
                    <div class="bg-white rounded-lg p-8 max-w-2xl w-full shadow-2xl m-4">
                        <h3 class="text-2xl font-bold mb-6 text-green-600">Create New Particular</h3>
                        <form onsubmit="createParticular(event)" class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold mb-2">Particular Name *</label>
                                <input type="text" id="particularName" required
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-green-500 focus:outline-none"
                                    placeholder="e.g., Tuition Fees, Food Fees, Transport">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2">Assign Books * (Check to select)</label>
                                <div class="border-2 border-gray-300 rounded-lg p-4 max-h-64 overflow-y-auto space-y-2">
                                    ${booksCheckboxes}
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Select one or more books where this fee can be paid</p>
                            </div>
                            <div class="flex gap-3 pt-4">
                                <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-bold transition">
                                    💾 Save Particular
                                </button>
                                <button type="button" onclick="closeParticularForm()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-lg font-bold transition">
                                    ✖️ Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            document.getElementById('particularFormContainer').innerHTML = formHtml;
        }

        function closeParticularForm() {
            document.getElementById('particularFormContainer').innerHTML = '';
        }

        async function createParticular(event) {
            event.preventDefault();
            const name = document.getElementById('particularName').value;
            const bookCheckboxes = document.querySelectorAll('.book-checkbox:checked');
            const bookIds = Array.from(bookCheckboxes).map(cb => parseInt(cb.value));

            if (bookIds.length === 0) {
                alert('⚠️ Please select at least one book');
                return;
            }

            try {
                await axios.post(`${API_BASE}/particulars`, {
                    name: name,
                    book_ids: bookIds,
                    class_names: []
                });
                alert('✅ Particular created successfully!');
                closeParticularForm();
                loadParticulars();
            } catch (error) {
                alert('❌ Error: ' + (error.response?.data?.message || error.message));
            }
        }

        function showEditParticularForm(particular) {
            const booksCheckboxes = allBooks.map(book => {
                const isChecked = particular.book_ids?.includes(book.id) ? 'checked' : '';
                return `<label class="flex items-center gap-2 p-3 bg-gray-50 hover:bg-blue-50 rounded border-2 border-gray-300 hover:border-blue-400 cursor-pointer transition">
                    <input type="checkbox" class="book-checkbox w-5 h-5 rounded border-gray-300" value="${book.id}" ${isChecked}>
                    <span class="font-semibold">${book.name}</span>
                    ${book.is_cash_book ? '<span class="text-xs text-green-600 font-bold ml-auto">💵 Cash Book</span>' : '<span class="text-xs text-blue-600 font-bold ml-auto">🏦 Bank</span>'}
                </label>`;
            }).join('');

            const formHtml = `
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
                    <div class="bg-white rounded-lg p-8 max-w-2xl w-full shadow-2xl m-4">
                        <h3 class="text-2xl font-bold mb-6 text-yellow-600">✏️ Edit Particular</h3>
                        <form onsubmit="updateParticular(event, ${particular.id})" class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold mb-2">Particular Name *</label>
                                <input type="text" id="editParticularName" required
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-yellow-500 focus:outline-none"
                                    placeholder="e.g., Tuition Fees, Food Fees, Transport"
                                    value="${particular.name}">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2">Assign Books * (Check to select)</label>
                                <div class="border-2 border-gray-300 rounded-lg p-4 max-h-64 overflow-y-auto space-y-2">
                                    ${booksCheckboxes}
                                </div>
                                <p class="text-xs text-gray-500 mt-2">Select one or more books where this fee can be paid</p>
                                <p class="text-xs text-orange-600 font-semibold mt-2">⚠️ Note: Unchecking a book will not delete existing entries but will prevent future entries to that book.</p>
                            </div>
                            <div class="flex gap-3 pt-4">
                                <button type="submit" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg font-bold transition">
                                    💾 Update Particular
                                </button>
                                <button type="button" onclick="closeParticularForm()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-lg font-bold transition">
                                    ✖️ Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            document.getElementById('particularFormContainer').innerHTML = formHtml;
        }

        async function updateParticular(event, particularId) {
            event.preventDefault();
            const name = document.getElementById('editParticularName').value;
            const bookCheckboxes = document.querySelectorAll('.book-checkbox:checked');
            const bookIds = Array.from(bookCheckboxes).map(cb => parseInt(cb.value));

            if (bookIds.length === 0) {
                alert('⚠️ Please select at least one book');
                return;
            }

            try {
                await axios.put(`${API_BASE}/particulars/${particularId}`, {
                    name: name,
                    book_ids: bookIds
                });
                alert('✅ Particular updated successfully!');
                closeParticularForm();
                loadParticulars();
            } catch (error) {
                alert('❌ Error: ' + (error.response?.data?.message || error.message));
            }
        }

        // ===== ASSIGN STUDENTS TO PARTICULAR =====
        async function showAssignStudentsForm(particularId, particularName) {
            // Always reload classes and students fresh
            if (allClasses.length === 0) {
                try {
                    const classesResponse = await axios.get(`${API_BASE}/classes`);
                    allClasses = classesResponse.data;
                } catch (error) {
                    console.error('Error loading classes:', error);
                    allClasses = [];
                }
            }

            const classButtons = allClasses.map(cls =>
                `<button type="button" onclick="loadStudentsByClass(${particularId}, ${cls.id}, '${cls.name}')"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-bold transition transform hover:scale-105">
                    ${cls.name}
                </button>`
            ).join('');

            const formHtml = `
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
                    <div class="bg-white rounded-lg p-8 max-w-6xl w-full shadow-2xl m-4 max-h-[90vh] overflow-y-auto">
                        <h3 class="text-2xl font-bold mb-4 text-green-600">Assign Students to: ${particularName}</h3>

                        <div class="mb-6 p-6 bg-green-50 rounded-lg border-2 border-green-300">
                            <label class="block text-lg font-bold mb-4 text-center">📚 Select Class by Clicking Below:</label>
                            <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                ${classButtons}
                            </div>
                        </div>

                        <div id="studentsListContainer" class="mb-6"></div>

                        <div id="assignActionsContainer"></div>

                        <div class="flex gap-3 pt-4 border-t-2">
                            <button type="button" onclick="closeAssignForm()"
                                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-lg font-bold transition">
                                ✖️ Close
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('particularFormContainer').innerHTML = formHtml;
        }

        async function loadStudentsByClass(particularId, classId, className) {
            if (!classId) {
                document.getElementById('studentsListContainer').innerHTML = '';
                document.getElementById('assignActionsContainer').innerHTML = '';
                return;
            }

            // Show loading message
            document.getElementById('studentsListContainer').innerHTML = '<p class="text-center text-blue-600 p-4">⏳ Loading students...</p>';

            // Load existing assignments for this particular
            let existingAssignments = {};
            try {
                const particularResponse = await axios.get(`${API_BASE}/particulars/${particularId}`);
                const particularData = particularResponse.data;
                if (particularData.students) {
                    particularData.students.forEach(student => {
                        existingAssignments[student.id] = {
                            sales: student.pivot.sales || 0,
                            deadline: student.pivot.deadline || '',
                            isAssigned: true
                        };
                    });
                }
            } catch (error) {
                console.error('Error loading existing assignments:', error);
            }

            const classStudents = allStudents.filter(s => s.class_id == classId);

            if (classStudents.length === 0) {
                document.getElementById('studentsListContainer').innerHTML = '<p class="text-center text-gray-500 p-4">No students found in this class.</p>';
                return;
            }

            let html = `
                <div class="border-2 border-gray-300 rounded-lg p-4 bg-gray-50">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-bold text-lg">Students in ${className}</h4>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()"
                                class="w-5 h-5 rounded border-gray-300">
                            <span class="font-bold text-sm">Select All</span>
                        </label>
                    </div>
                    <div class="space-y-2 max-h-96 overflow-y-auto" id="studentsList">
            `;

            classStudents.forEach(student => {
                const isAssigned = existingAssignments[student.id]?.isAssigned || false;
                const salesAmount = existingAssignments[student.id]?.sales || 0;
                const checkedAttr = isAssigned ? 'checked' : '';
                const bgClass = isAssigned ? 'bg-green-50 border-green-400' : 'bg-white';
                const deadlineValue = existingAssignments[student.id]?.deadline || '';
                const checkmark = isAssigned ? '✅ ' : '';

                html += `
                    <div class="flex items-center gap-2 p-3 ${bgClass} rounded border hover:border-green-500">
                        <input type="checkbox" class="student-checkbox w-5 h-5 rounded border-gray-300"
                            data-student-id="${student.id}" ${checkedAttr}>
                        <div class="flex-1">
                            <p class="font-bold">${checkmark}${student.name}</p>
                            <p class="text-xs text-gray-500">${student.student_reg_no}</p>
                        </div>
                        <div class="flex gap-2 items-center">
                            <span class="text-xs font-bold">Sales:</span>
                            <input type="number" step="0.01" value="${salesAmount}"
                                class="sales-amount w-28 border-2 border-gray-300 rounded px-2 py-1 text-sm"
                                data-student-id="${student.id}" placeholder="TSh">
                        </div>
                        <div class="flex gap-2 items-center">
                            <span class="text-xs font-bold">Deadline:</span>
                            <input type="date" value="${deadlineValue}"
                                class="deadline-date w-36 border-2 border-gray-300 rounded px-2 py-1 text-sm"
                                data-student-id="${student.id}">
                        </div>
                    </div>
                `;
            });

            html += `
                    </div>
                </div>
            `;

            document.getElementById('studentsListContainer').innerHTML = html;

            // Show action buttons
            const actionsHtml = `
                <div class="bg-blue-50 p-4 rounded-lg border-2 border-blue-300 space-y-3">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center gap-2">
                            <label class="font-bold text-sm">Bulk Sales Amount:</label>
                            <input type="number" step="0.01" id="bulkSalesAmount"
                                class="border-2 border-gray-300 rounded px-3 py-2 w-32"
                                placeholder="TSh 0.00">
                            <button onclick="applyBulkSales()"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded transition text-sm">
                                Apply
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="font-bold text-sm">Bulk Deadline:</label>
                            <input type="date" id="bulkDeadlineDate"
                                class="border-2 border-gray-300 rounded px-3 py-2 w-36">
                            <button onclick="applyBulkDeadline()"
                                class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-2 rounded transition text-sm">
                                Apply
                            </button>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="assignSelectedStudents(${particularId})"
                            class="flex-1 bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-bold transition">
                            💾 Save & Exit
                        </button>
                        <button onclick="assignAndSelectAnother(${particularId})"
                            class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg font-bold transition">
                            💾 Save & Select Another Class
                        </button>
                    </div>
                </div>
            `;
            document.getElementById('assignActionsContainer').innerHTML = actionsHtml;
        }

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll').checked;
            document.querySelectorAll('.student-checkbox').forEach(cb => {
                cb.checked = selectAll;
            });
        }

        function applyBulkSales() {
            const bulkAmount = document.getElementById('bulkSalesAmount').value;
            if (!bulkAmount) {
                alert('⚠️ Please enter a sales amount');
                return;
            }

            document.querySelectorAll('.student-checkbox:checked').forEach(cb => {
                const studentId = cb.getAttribute('data-student-id');
                const input = document.querySelector(`.sales-amount[data-student-id="${studentId}"]`);
                if (input) {
                    input.value = bulkAmount;
                }
            });
            alert('✅ Sales amount applied to selected students');
        }

        function applyBulkDeadline() {
            const bulkDeadline = document.getElementById('bulkDeadlineDate').value;
            if (!bulkDeadline) {
                alert('⚠️ Please select a deadline date');
                return;
            }

            document.querySelectorAll('.student-checkbox:checked').forEach(cb => {
                const studentId = cb.getAttribute('data-student-id');
                const input = document.querySelector(`.deadline-date[data-student-id="${studentId}"]`);
                if (input) {
                    input.value = bulkDeadline;
                }
            });
            alert('✅ Deadline applied to selected students');
        }

        async function assignSelectedStudents(particularId, selectAnother = false) {
            const selectedStudents = [];
            document.querySelectorAll('.student-checkbox:checked').forEach(cb => {
                const studentId = cb.getAttribute('data-student-id');
                const salesAmount = document.querySelector(`.sales-amount[data-student-id="${studentId}"]`).value || 0;
                const deadline = document.querySelector(`.deadline-date[data-student-id="${studentId}"]`).value || null;

                selectedStudents.push({
                    student_id: parseInt(studentId),
                    sales: parseFloat(salesAmount),
                    debit: 0,
                    credit: 0,
                    deadline: deadline
                });
            });

            if (selectedStudents.length === 0) {
                alert('⚠️ Please select at least one student');
                return;
            }

            try {
                await axios.post(`${API_BASE}/particulars/${particularId}/assign-students`, {
                    students: selectedStudents
                });
                alert(`✅ ${selectedStudents.length} student(s) assigned successfully!`);

                if (selectAnother) {
                    // Clear selections and reset form
                    document.getElementById('studentsListContainer').innerHTML = '';
                    document.getElementById('assignActionsContainer').innerHTML = '';
                } else {
                    closeAssignForm();
                    loadParticulars();
                }
            } catch (error) {
                alert('❌ Error: ' + (error.response?.data?.message || error.message));
            }
        }

        function assignAndSelectAnother(particularId) {
            assignSelectedStudents(particularId, true);
        }

        function closeAssignForm() {
            document.getElementById('particularFormContainer').innerHTML = '';
            loadParticulars();
        }

        // ===== EXISTING ASSIGNMENTS MANAGEMENT =====
        let currentParticularForExisting = null;
        let currentClassForExisting = null;

        async function showExistingAssignmentsForm(particularId, particularName) {
            currentParticularForExisting = { id: particularId, name: particularName };
            currentClassForExisting = null;

            // Always reload classes fresh
            if (allClasses.length === 0) {
                try {
                    const classesResponse = await axios.get(`${API_BASE}/classes`);
                    allClasses = classesResponse.data;
                } catch (error) {
                    console.error('Error loading classes:', error);
                }
            }

            const classButtons = allClasses.map(cls =>
                `<button type="button" onclick="loadExistingAssignmentsByClass(${particularId}, ${cls.id}, '${cls.name}')"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-bold transition transform hover:scale-105">
                    ${cls.name}
                </button>`
            ).join('');

            const formHtml = `
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
                    <div class="bg-white rounded-lg p-8 max-w-6xl w-full shadow-2xl m-4 max-h-[90vh] overflow-y-auto">
                        <h3 class="text-2xl font-bold mb-4 text-blue-600">📋 Existing Assignments for: ${particularName}</h3>

                        <div class="mb-6 p-6 bg-blue-50 rounded-lg border-2 border-blue-300">
                            <label class="block text-lg font-bold mb-4 text-center">📚 Select Class to View Assignments:</label>
                            <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                ${classButtons}
                            </div>
                        </div>

                        <div id="existingAssignmentsContainer" class="mb-6"></div>

                        <div class="flex gap-3 pt-4 border-t-2">
                            <button type="button" onclick="closeExistingAssignmentsForm()"
                                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-lg font-bold transition">
                                ✖️ Close
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('particularFormContainer').innerHTML = formHtml;
        }

        async function loadExistingAssignmentsByClass(particularId, classId, className) {
            if (!classId) {
                document.getElementById('existingAssignmentsContainer').innerHTML = '';
                return;
            }

            // Store current class for reload purposes
            currentClassForExisting = { id: classId, name: className };

            // Show loading message
            document.getElementById('existingAssignmentsContainer').innerHTML = '<p class="text-center text-blue-600 p-4">⏳ Loading assignments...</p>';

            try {
                const response = await axios.get(`${API_BASE}/particulars/${particularId}/existing-assignments?class_id=${classId}`);
                const data = response.data;

                if (!data.assignments || data.assignments.length === 0) {
                    document.getElementById('existingAssignmentsContainer').innerHTML = '<p class="text-center text-gray-500 p-4">No assignments found for this class.</p>';
                    return;
                }

                let html = `
                    <div class="border-2 border-gray-300 rounded-lg p-4 bg-gray-50">
                        <h4 class="font-bold text-lg mb-4">Students in ${className} with Assignments</h4>
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                `;

                data.assignments.forEach(assignment => {
                    const deadlineDisplay = assignment.deadline ? new Date(assignment.deadline).toLocaleDateString() : 'No deadline';

                    html += `
                        <div class="flex items-center gap-3 p-4 bg-white rounded border-2 border-gray-300 hover:border-blue-500">
                            <div class="flex-1">
                                <p class="font-bold">${assignment.student_name}</p>
                                <p class="text-xs text-gray-500">${assignment.student_reg_no}</p>
                            </div>
                            <div class="flex gap-2 items-center">
                                <div>
                                    <p class="text-xs font-bold text-gray-600">Amount:</p>
                                    <p class="font-bold text-lg text-green-600">TSh ${parseFloat(assignment.sales).toLocaleString()}</p>
                                </div>
                            </div>
                            <div class="flex gap-2 items-center">
                                <div>
                                    <p class="text-xs font-bold text-gray-600">Deadline:</p>
                                    <p class="font-bold text-sm">${deadlineDisplay}</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="showEditAssignmentModal(${particularId}, ${assignment.student_id}, '${assignment.student_name}', ${assignment.sales}, '${assignment.deadline || ''}')"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded transition text-sm">
                                    ✏️ Edit
                                </button>
                                <button onclick="deleteAssignment(${particularId}, ${assignment.student_id}, '${assignment.student_name}')"
                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded transition text-sm">
                                    🗑️ Delete
                                </button>
                            </div>
                        </div>
                    `;
                });

                html += `
                        </div>
                    </div>
                `;

                document.getElementById('existingAssignmentsContainer').innerHTML = html;
            } catch (error) {
                alert('Error loading assignments: ' + error.message);
                document.getElementById('existingAssignmentsContainer').innerHTML = '<p class="text-center text-red-500 p-4">Failed to load assignments.</p>';
            }
        }

        function showEditAssignmentModal(particularId, studentId, studentName, currentSales, currentDeadline) {
            const editModalHtml = `
                <div id="editAssignmentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-2xl m-4">
                        <h3 class="text-xl font-bold mb-4 text-yellow-600">✏️ Edit Assignment</h3>
                        <p class="mb-4"><strong>Student:</strong> ${studentName}</p>

                        <form onsubmit="updateAssignment(event, ${particularId}, ${studentId})" class="space-y-4">
                            <div>
                                <label class="block font-bold mb-2">Amount (TSh) <span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" id="edit_sales" value="${currentSales}" required
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-2"
                                    placeholder="TSh 0.00">
                            </div>
                            <div>
                                <label class="block font-bold mb-2">Deadline Date</label>
                                <input type="date" id="edit_deadline" value="${currentDeadline}"
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-2">
                            </div>
                            <div class="flex gap-3">
                                <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-bold transition">
                                    💾 Save Changes
                                </button>
                                <button type="button" onclick="closeEditAssignmentModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-lg font-bold transition">
                                    ✖️ Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;

            // Append to body
            const modalDiv = document.createElement('div');
            modalDiv.innerHTML = editModalHtml;
            document.body.appendChild(modalDiv);
        }

        function closeEditAssignmentModal() {
            const modal = document.getElementById('editAssignmentModal');
            if (modal) {
                modal.parentElement.remove();
            }
        }

        async function updateAssignment(event, particularId, studentId) {
            event.preventDefault();

            const sales = parseFloat(document.getElementById('edit_sales').value);
            const deadline = document.getElementById('edit_deadline').value || null;

            if (!sales || sales <= 0) {
                alert('⚠️ Please enter a valid amount');
                return;
            }

            try {
                await axios.put(`${API_BASE}/particulars/${particularId}/assignments/${studentId}`, {
                    sales: sales,
                    deadline: deadline
                });

                alert('✅ Assignment updated successfully!');
                closeEditAssignmentModal();

                // Reload the existing assignments for the current class
                if (currentParticularForExisting && currentClassForExisting) {
                    await loadExistingAssignmentsByClass(particularId, currentClassForExisting.id, currentClassForExisting.name);
                }
            } catch (error) {
                alert('❌ Error updating assignment: ' + (error.response?.data?.message || error.message));
            }
        }

        async function deleteAssignment(particularId, studentId, studentName) {
            if (!confirm(`Are you sure you want to delete the assignment for ${studentName}? This will remove the assignment and associated Sales voucher.`)) {
                return;
            }

            try {
                await axios.delete(`${API_BASE}/particulars/${particularId}/assignments/${studentId}`);
                alert('✅ Assignment deleted successfully!');

                // Reload the existing assignments for the current class
                if (currentParticularForExisting && currentClassForExisting) {
                    await loadExistingAssignmentsByClass(particularId, currentClassForExisting.id, currentClassForExisting.name);
                }
            } catch (error) {
                alert('❌ Error deleting assignment: ' + (error.response?.data?.message || error.message));
            }
        }

        function closeExistingAssignmentsForm() {
            document.getElementById('particularFormContainer').innerHTML = '';
            currentParticularForExisting = null;
            loadParticulars();
        }
    </script>
</body>
</html>

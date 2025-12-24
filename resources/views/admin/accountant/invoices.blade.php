<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Student Invoices - Darasa Finance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-purple-600">📄 Download Student Invoices</h1>
                    <p class="text-gray-600 mt-2">Generate and download fee statements for parents</p>
                </div>
                <a href="/accountant-dashboard" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition">
                    ← Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Selection Options -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-700 mb-4">Select Students</h2>

            <!-- Quick Options -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <button onclick="showAllStudentsInvoices()" class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-4 rounded-lg text-lg font-bold transition">
                    📥 Download All Students
                </button>
                <button onclick="showClassSelection()" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-4 rounded-lg text-lg font-bold transition">
                    🎓 Select by Class
                </button>
                <button onclick="showStudentSearch()" class="bg-green-500 hover:bg-green-600 text-white px-6 py-4 rounded-lg text-lg font-bold transition">
                    🔍 Select Specific Student
                </button>
            </div>

            <!-- Class Selection Area -->
            <div id="classSelectionArea" class="hidden">
                <h3 class="text-xl font-bold text-gray-700 mb-4">Select Classes:</h3>
                <div class="flex gap-3 mb-4">
                    <button onclick="selectAllClasses()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                        ✅ Select All
                    </button>
                    <button onclick="deselectAllClasses()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                        ❌ Deselect All
                    </button>
                    <button onclick="downloadSelectedClassInvoices()" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded font-bold">
                        📥 Download Invoices
                    </button>
                </div>
                <div id="classList" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <!-- Classes will be loaded here -->
                </div>
            </div>

            <!-- Student Search Area -->
            <div id="studentSearchArea" class="hidden">
                <h3 class="text-xl font-bold text-gray-700 mb-4">Search for Student:</h3>
                <div class="flex gap-3 mb-4">
                    <input type="text" id="studentSearchInput" placeholder="Enter student name or ID..."
                        class="flex-1 border-2 border-gray-300 rounded px-4 py-2"
                        onkeyup="searchStudents()">
                    <button onclick="downloadSelectedStudent()" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded font-bold">
                        📥 Download Invoice
                    </button>
                </div>
                <div id="studentSearchResults" class="max-h-96 overflow-y-auto">
                    <!-- Search results will appear here -->
                </div>
            </div>
        </div>

        <!-- Preview Information -->
        <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-6">
            <h3 class="text-xl font-bold text-blue-700 mb-3">📋 Invoice Format</h3>
            <p class="text-gray-700 mb-2">Each invoice will be printed on a separate page and will include:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-1 ml-4">
                <li>Student name and class</li>
                <li>List of all fees with amounts</li>
                <li>Total fees assigned</li>
                <li>Amount already paid</li>
                <li>Balance remaining (in parent-friendly language)</li>
                <li>Payment deadlines (if applicable)</li>
            </ul>
        </div>
    </div>

    <script>
        const API_BASE = '/accountant/api';
        let allStudents = [];
        let allClasses = [];
        let selectedStudentId = null;

        // Load initial data
        async function loadInitialData() {
            try {
                const response = await axios.get(`${API_BASE}/students`);
                allStudents = response.data.data || response.data;
                allClasses = [...new Set(allStudents.map(s => s.class))].sort();
            } catch (error) {
                console.error('Error loading data:', error);
            }
        }

        function showAllStudentsInvoices() {
            window.open('/accountant/invoices/all-students/pdf', '_blank');
        }

        function showClassSelection() {
            document.getElementById('classSelectionArea').classList.remove('hidden');
            document.getElementById('studentSearchArea').classList.add('hidden');

            let html = '';
            allClasses.forEach(className => {
                const studentCount = allStudents.filter(s => s.class === className).length;
                html += `
                    <div class="bg-gray-50 border-2 border-gray-300 rounded-lg p-3 hover:bg-purple-50 hover:border-purple-400 transition">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" class="class-checkbox w-5 h-5 mr-2" value="${className}">
                            <div>
                                <p class="font-bold">${className}</p>
                                <p class="text-xs text-gray-500">${studentCount} students</p>
                            </div>
                        </label>
                    </div>
                `;
            });
            document.getElementById('classList').innerHTML = html;
        }

        function showStudentSearch() {
            document.getElementById('classSelectionArea').classList.add('hidden');
            document.getElementById('studentSearchArea').classList.remove('hidden');
        }

        function selectAllClasses() {
            document.querySelectorAll('.class-checkbox').forEach(cb => cb.checked = true);
        }

        function deselectAllClasses() {
            document.querySelectorAll('.class-checkbox').forEach(cb => cb.checked = false);
        }

        function downloadSelectedClassInvoices() {
            const selectedClasses = [];
            document.querySelectorAll('.class-checkbox:checked').forEach(cb => {
                selectedClasses.push(cb.value);
            });

            if (selectedClasses.length === 0) {
                alert('⚠️ Please select at least one class');
                return;
            }

            if (selectedClasses.length === allClasses.length) {
                window.open('/accountant/invoices/all-students/pdf', '_blank');
            } else if (selectedClasses.length === 1) {
                window.open(`/accountant/invoices/all-students/pdf?class=${encodeURIComponent(selectedClasses[0])}`, '_blank');
            } else {
                const classesParam = selectedClasses.map(c => `classes[]=${encodeURIComponent(c)}`).join('&');
                window.open(`/accountant/invoices/all-students/pdf?${classesParam}`, '_blank');
            }
        }

        function searchStudents() {
            const searchTerm = document.getElementById('studentSearchInput').value.toLowerCase();
            const filtered = allStudents.filter(s =>
                s.name.toLowerCase().includes(searchTerm) ||
                s.student_reg_no.toLowerCase().includes(searchTerm)
            );

            let html = '';
            filtered.slice(0, 20).forEach(student => {
                html += `
                    <div onclick="selectStudentForInvoice(${student.id}, '${student.name}')"
                        class="p-3 border-b hover:bg-purple-50 cursor-pointer">
                        <p class="font-bold">${student.name}</p>
                        <p class="text-sm text-gray-600">${student.student_reg_no} - ${student.class}</p>
                    </div>
                `;
            });

            if (filtered.length === 0) {
                html = '<p class="p-4 text-center text-gray-500">No students found</p>';
            }

            document.getElementById('studentSearchResults').innerHTML = html;
        }

        function selectStudentForInvoice(studentId, studentName) {
            selectedStudentId = studentId;
            document.getElementById('studentSearchInput').value = studentName;
            document.getElementById('studentSearchResults').innerHTML =
                `<div class="p-4 bg-green-50 border border-green-300 rounded">Selected: <strong>${studentName}</strong></div>`;
        }

        function downloadSelectedStudent() {
            if (!selectedStudentId) {
                alert('⚠️ Please search and select a student first');
                return;
            }
            window.open(`/accountant/invoices/student/${selectedStudentId}/pdf`, '_blank');
        }

        // Load data on page load
        loadInitialData();
    </script>
</body>
</html>

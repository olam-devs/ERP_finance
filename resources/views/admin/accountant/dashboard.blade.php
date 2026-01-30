<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - Darasa Finance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        /* Skeleton Loading Animation */
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        .skeleton {
            animation: shimmer 2s infinite linear;
            background: linear-gradient(to right, #f0f0f0 4%, #e0e0e0 25%, #f0f0f0 36%);
            background-size: 1000px 100%;
        }

        /* Smooth transitions */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Card hover effects */
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Hide scrollbar but keep functionality */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Sidebar -->
    <div id="sidebar" class="fixed left-0 top-0 h-full w-72 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 shadow-2xl transform -translate-x-full transition-transform duration-300 z-50 overflow-y-auto">
        <!-- Sidebar Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6">
            <div class="flex justify-between items-center mb-2">
                <div class="flex items-center gap-3">
                    <div class="bg-white p-2 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white">Darasa ERP</h2>
                        <p class="text-xs text-blue-100">Navigation Menu</p>
                    </div>
                </div>
                <button onclick="toggleSidebar()" class="text-white hover:bg-white hover:bg-opacity-20 p-1 rounded transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Sidebar Content -->
        <div class="p-4">
            <!-- Sidebar Categories -->
            <nav class="space-y-1">
                <!-- Dashboard -->
                <div class="sidebar-item mb-3">
                    <a href="{{ route('accountant.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                        <span class="text-xl">🏠</span>
                        <span class="font-semibold">Dashboard</span>
                    </a>
                </div>

                <!-- Finance Management -->
                <div class="sidebar-category">
                    <button onclick="toggleCategory('finance')" class="w-full flex items-center justify-between px-4 py-3 rounded-lg bg-slate-700 hover:bg-slate-600 text-white transition">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">💰</span>
                            <span class="font-semibold">Finance</span>
                        </div>
                        <svg class="w-4 h-4 transform transition-transform" id="finance-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="finance-submenu" class="ml-6 mt-1 space-y-1 hidden">
                        <a href="{{ route('accountant.fee-entry') }}" class="block px-4 py-2 text-sm text-gray-300 hover:text-blue-400 hover:bg-slate-700 rounded transition">Fee Entry</a>
                        <a href="{{ route('accountant.ledgers') }}" class="block px-4 py-2 text-sm text-gray-300 hover:text-blue-400 hover:bg-slate-700 rounded transition">Ledgers</a>
                        <a href="{{ route('accountant.particular-ledger') }}" class="block px-4 py-2 text-sm text-gray-300 hover:text-blue-400 hover:bg-slate-700 rounded transition">Particular Ledger</a>
                        <a href="{{ route('accountant.overdue') }}" class="block px-4 py-2 text-sm text-gray-300 hover:text-blue-400 hover:bg-slate-700 rounded transition">Overdue Payments</a>
                        <a href="{{ route('accountant.suspense') }}" class="block px-4 py-2 text-sm text-gray-300 hover:text-blue-400 hover:bg-slate-700 rounded transition">Suspense Accounts</a>
                    </div>
                </div>

                <!-- Books & Accounting -->
                <div class="sidebar-category">
                    <button onclick="toggleCategory('books')" class="w-full flex items-center justify-between px-4 py-3 rounded-lg bg-slate-700 hover:bg-slate-600 text-white transition">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">📚</span>
                            <span class="font-semibold">Books & Accounting</span>
                        </div>
                        <svg class="w-4 h-4 transform transition-transform" id="books-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="books-submenu" class="ml-6 mt-1 space-y-1 hidden">
                        <a href="{{ route('accountant.books') }}" class="block px-4 py-2 text-sm text-gray-300 hover:text-blue-400 hover:bg-slate-700 rounded transition">Books Management</a>
                        <a href="{{ route('accountant.particulars') }}" class="block px-4 py-2 text-sm text-gray-300 hover:text-blue-400 hover:bg-slate-700 rounded transition">Particulars</a>
                    </div>
                </div>

                <!-- Expenses & Payroll -->
                <div class="sidebar-category">
                    <button onclick="toggleCategory('expenses')" class="w-full flex items-center justify-between px-4 py-3 rounded-lg bg-slate-700 hover:bg-slate-600 text-white transition">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">💳</span>
                            <span class="font-semibold">Expenses & Payroll</span>
                        </div>
                        <svg class="w-4 h-4 transform transition-transform" id="expenses-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="expenses-submenu" class="ml-6 mt-1 space-y-1 hidden">
                        <a href="{{ route('accountant.payroll') }}" class="block px-4 py-2 text-sm text-gray-300 hover:text-blue-400 hover:bg-slate-700 rounded transition">Payroll</a>
                        <a href="{{ route('accountant.expenses') }}" class="block px-4 py-2 text-sm text-gray-300 hover:text-blue-400 hover:bg-slate-700 rounded transition">Expenses</a>
                    </div>
                </div>

                <!-- Student Management -->
                <div class="sidebar-category">
                    <button onclick="toggleCategory('students')" class="w-full flex items-center justify-between px-4 py-3 rounded-lg bg-slate-700 hover:bg-slate-600 text-white transition">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">👨‍🎓</span>
                            <span class="font-semibold">Students</span>
                        </div>
                        <svg class="w-4 h-4 transform transition-transform" id="students-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="students-submenu" class="ml-6 mt-1 space-y-1 hidden">
                        <a href="{{ route('accountant.students') }}" class="block px-4 py-2 text-sm text-gray-300 hover:text-blue-400 hover:bg-slate-700 rounded transition">Student Management</a>
                        <a href="{{ route('accountant.classes') }}" class="block px-4 py-2 text-sm text-gray-300 hover:text-blue-400 hover:bg-slate-700 rounded transition">Class Management</a>
                        <a href="{{ route('students.promotion-page') }}" class="block px-4 py-2 text-sm text-gray-300 hover:text-blue-400 hover:bg-slate-700 rounded transition">Student Promotion</a>
                        <a href="{{ route('accountant.invoices-page') }}" class="block px-4 py-2 text-sm text-gray-300 hover:text-blue-400 hover:bg-slate-700 rounded transition">Student Invoices</a>
                    </div>
                </div>

                <!-- Communication -->
                <div class="sidebar-category">
                    <button onclick="toggleCategory('communication')" class="w-full flex items-center justify-between px-4 py-3 rounded-lg bg-slate-700 hover:bg-slate-600 text-white transition">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">📱</span>
                            <span class="font-semibold">Communication</span>
                        </div>
                        <svg class="w-4 h-4 transform transition-transform" id="communication-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="communication-submenu" class="ml-6 mt-1 space-y-1 hidden">
                        <a href="{{ route('accountant.sms') }}" class="block px-4 py-2 text-sm text-gray-300 hover:text-blue-400 hover:bg-slate-700 rounded transition">Send SMS</a>
                        <a href="{{ route('accountant.phone-numbers') }}" class="block px-4 py-2 text-sm text-gray-300 hover:text-blue-400 hover:bg-slate-700 rounded transition">Phone Numbers</a>
                        <a href="{{ route('accountant.sms-logs') }}" class="block px-4 py-2 text-sm text-gray-300 hover:text-blue-400 hover:bg-slate-700 rounded transition">SMS Logs</a>
                    </div>
                </div>

                <!-- Integrations -->
                <div class="sidebar-item mt-2">
                    <a href="{{ route('accountant.bank-api') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 text-white hover:from-green-500 hover:to-emerald-500 shadow-md transition-all">
                        <span class="text-xl">🏦</span>
                        <span class="font-semibold">Bank Integration</span>
                    </a>
                </div>

                <!-- Settings -->
                <div class="sidebar-item">
                    <a href="{{ route('accountant.settings') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-slate-700 hover:bg-slate-600 text-gray-300 hover:text-white transition">
                        <span class="text-xl">⚙️</span>
                        <span class="font-semibold">Settings</span>
                    </a>
                </div>
            </nav>
        </div>
    </div>

    <!-- Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40" onclick="toggleSidebar()"></div>

    <!-- Top Header -->
    <nav class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white shadow-2xl sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
                <div class="flex items-center gap-3">
                    <!-- Menu Button -->
                    <button onclick="toggleSidebar()" class="bg-white bg-opacity-20 hover:bg-opacity-30 p-2 rounded-lg transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <!-- Clickable Logo and Title -->
                    <a href="{{ route('accountant.dashboard') }}" class="flex items-center gap-3 hover:opacity-90 transition">
                        @if($settings->logo_path && file_exists(public_path('storage/' . $settings->logo_path)))
                            <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="School Logo" class="w-12 h-12 md:w-16 md:h-16 rounded-lg bg-white p-1 object-contain">
                        @else
                            <div class="bg-white bg-opacity-20 p-2 rounded-lg">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold">{{ $settings->school_name ?? 'Darasa Finance ERP' }}</h1>
                            <p class="text-xs md:text-sm text-blue-100">Financial Management System</p>
                        </div>
                    </a>
                </div>
                <div class="flex flex-col md:flex-row gap-2 md:gap-4 items-start md:items-center">
                    <div class="text-xs md:text-sm bg-white bg-opacity-20 px-3 py-1.5 rounded-lg">
                        <span class="hidden md:inline">📅 </span>{{ \Carbon\Carbon::now()->format('l, F j, Y') }}
                    </div>
                    <div class="text-xs md:text-sm bg-white bg-opacity-20 px-3 py-1.5 rounded-lg">
                        👤 Accountant
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg transition shadow-lg text-sm md:text-base font-semibold">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-3 md:px-6 py-6 md:py-8">
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl shadow-2xl p-6 md:p-8 mb-6 md:mb-8 text-white fade-in">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold mb-2">Welcome Back! 👋</h2>
                    <p class="text-blue-100 text-sm md:text-base">Here's what's happening with your school finances today.</p>
                </div>
                <div class="flex gap-2 md:gap-3 flex-wrap">
                    <button onclick="window.location.reload()" class="bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-lg transition text-sm md:text-base">
                        🔄 Refresh
                    </button>
                    <a href="{{ route('accountant.fee-entry') }}" class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg transition font-bold text-sm md:text-base">
                        + New Entry
                    </a>
                </div>
            </div>
        </div>

        <!-- Advanced Analytics Section -->
        <div class="fade-in mb-6 md:mb-10">
            <div class="flex items-center gap-3 mb-4 md:mb-6">
                <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-2 md:p-3 rounded-xl shadow-lg">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800">Advanced Analytics & Reports</h2>
            </div>

            <!-- Time Period Selector -->
            <div class="flex flex-wrap gap-2 md:gap-3 mb-4 md:mb-6">
                <button onclick="loadAnalytics('today')" id="btn-today" class="analytics-btn px-4 md:px-6 py-2 md:py-3 rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition font-semibold text-sm md:text-base shadow-md">
                    Today
                </button>
                <button onclick="loadAnalytics('weekly')" id="btn-weekly" class="analytics-btn px-4 md:px-6 py-2 md:py-3 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition font-semibold text-sm md:text-base shadow-md">
                    This Week
                </button>
                <button onclick="loadAnalytics('monthly')" id="btn-monthly" class="analytics-btn px-4 md:px-6 py-2 md:py-3 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition font-semibold text-sm md:text-base shadow-md">
                    This Month
                </button>
                <button onclick="loadAnalytics('yearly')" id="btn-yearly" class="analytics-btn px-4 md:px-6 py-2 md:py-3 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition font-semibold text-sm md:text-base shadow-md">
                    This Year
                </button>
                <button onclick="showCustomDatePicker()" id="btn-custom" class="analytics-btn px-4 md:px-6 py-2 md:py-3 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition font-semibold text-sm md:text-base shadow-md">
                    Custom Date
                </button>
            </div>

            <!-- Custom Date Range Picker (Initially Hidden) -->
            <div id="custom-date-picker" class="hidden mb-4 md:mb-6 bg-white p-4 rounded-lg shadow-md">
                <h3 class="text-lg font-bold text-gray-800 mb-3">Select Custom Date Range</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                        <input type="text" id="custom-from-date" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2" placeholder="Select start date">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                        <input type="text" id="custom-to-date" class="w-full border-2 border-gray-300 rounded-lg px-3 py-2" placeholder="Select end date">
                    </div>
                    <div class="flex items-end gap-2">
                        <button onclick="applyCustomDateRange()" class="flex-1 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition font-semibold">
                            Apply
                        </button>
                        <button onclick="hideCustomDatePicker()" class="flex-1 bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg transition font-semibold">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Analytics Summary Cards -->
            <div id="analytics-cards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6 mb-6">
                <!-- Skeleton loaders will be replaced -->
                <div class="skeleton rounded-xl h-32 md:h-36"></div>
                <div class="skeleton rounded-xl h-32 md:h-36"></div>
                <div class="skeleton rounded-xl h-32 md:h-36"></div>
                <div class="skeleton rounded-xl h-32 md:h-36"></div>
            </div>

            <!-- Charts Row: Line Graph and Books Pie Chart -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
                    <h3 class="text-base md:text-lg font-bold text-gray-800 mb-4">Fee Collection Trend</h3>
                    <div id="chart-container-1" class="relative" style="height: 250px;">
                        <canvas id="collectionChart"></canvas>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
                    <h3 class="text-base md:text-lg font-bold text-gray-800 mb-4">Books Distribution (Fee Collections)</h3>
                    <div id="chart-container-2" class="relative" style="height: 250px;">
                        <canvas id="paymentMethodsChart"></canvas>
                    </div>
                    <div id="books-legend" class="mt-4 grid grid-cols-2 gap-2 text-xs"></div>
                </div>
            </div>

            <!-- Particulars Bar Graph: Expected vs Collected -->
            <div class="bg-white rounded-xl shadow-lg p-4 md:p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg md:text-xl font-bold text-gray-800">Fee Collection by Particular (Expected vs Collected)</h3>
                    <div class="flex gap-2">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" id="select-all-particulars" onchange="toggleAllParticulars()" checked>
                            <span class="text-sm">Select All</span>
                        </label>
                    </div>
                </div>
                <div id="particular-checkboxes" class="flex flex-wrap gap-3 mb-4">
                    <!-- Particular checkboxes will be populated here -->
                </div>
                <div id="chart-container-particulars" class="relative" style="height: 350px;">
                    <canvas id="particularsChart"></canvas>
                </div>
            </div>

            <!-- Student Completion Summary -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Student Payment Completion Status</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Total Students</p>
                        <p class="text-2xl font-bold text-blue-600" id="total-students">0</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Completed Payments</p>
                        <p class="text-2xl font-bold text-green-600" id="completed-students">0</p>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Incomplete Payments</p>
                        <p class="text-2xl font-bold text-red-600" id="incomplete-students">0</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Completion Rate</p>
                        <p class="text-2xl font-bold text-purple-600" id="completion-rate">0%</p>
                    </div>
                </div>

                <!-- Filters Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Filter by Class</label>
                        <select id="class-filter" class="px-4 py-2 border border-gray-300 rounded-lg w-full" onchange="filterClassStats()">
                            <option value="">All Classes</option>
                            <option value="Grade 1">Grade 1</option>
                            <option value="Grade 2">Grade 2</option>
                            <option value="Grade 3">Grade 3</option>
                            <option value="Grade 4">Grade 4</option>
                            <option value="Grade 5">Grade 5</option>
                            <option value="Grade 6">Grade 6</option>
                            <option value="Form 1">Form 1</option>
                            <option value="Form 2">Form 2</option>
                            <option value="Form 3">Form 3</option>
                            <option value="Form 4">Form 4</option>
                        </select>
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Search Student</label>
                        <input type="text" id="student-search" placeholder="Search by name or registration number..." class="px-4 py-2 border border-gray-300 rounded-lg w-full" oninput="showAutocomplete()" autocomplete="off">
                        <div id="autocomplete-dropdown" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Class</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Completed/Total Students</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Expected Amount</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Collected Amount</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Collection Rate</th>
                            </tr>
                        </thead>
                        <tbody id="class-stats-table">
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">Loading class statistics...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Actions Section -->
        <div class="mb-6 md:mb-10 fade-in">
            <div class="flex items-center gap-3 mb-4 md:mb-6">
                <div class="bg-gradient-to-r from-purple-500 to-pink-500 p-2 md:p-3 rounded-xl shadow-lg">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800">Quick Actions</h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4">
                <a href="{{ route('accountant.fee-entry') }}" class="group bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-4 md:p-6 hover-lift text-white">
                    <div class="text-3xl md:text-4xl mb-2 md:mb-3 transform group-hover:scale-110 transition">💰</div>
                    <h3 class="font-bold text-sm md:text-base mb-1">Record Fee</h3>
                    <p class="text-xs text-purple-100 hidden md:block">Create entries</p>
                </a>
                <a href="{{ route('accountant.ledgers') }}" class="group bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-4 md:p-6 hover-lift text-white">
                    <div class="text-3xl md:text-4xl mb-2 md:mb-3 transform group-hover:scale-110 transition">📊</div>
                    <h3 class="font-bold text-sm md:text-base mb-1">View Ledgers</h3>
                    <p class="text-xs text-blue-100 hidden md:block">Reports & stats</p>
                </a>
                <a href="{{ route('accountant.sms') }}" class="group bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-4 md:p-6 hover-lift text-white">
                    <div class="text-3xl md:text-4xl mb-2 md:mb-3 transform group-hover:scale-110 transition">📱</div>
                    <h3 class="font-bold text-sm md:text-base mb-1">Send SMS</h3>
                    <p class="text-xs text-green-100 hidden md:block">Notify parents</p>
                </a>
                <a href="{{ route('accountant.overdue') }}" class="group bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-4 md:p-6 hover-lift text-white">
                    <div class="text-3xl md:text-4xl mb-2 md:mb-3 transform group-hover:scale-110 transition">💸</div>
                    <h3 class="font-bold text-sm md:text-base mb-1">Overdues</h3>
                    <p class="text-xs text-red-100 hidden md:block">Track payments</p>
                </a>
                <a href="{{ route('accountant.invoices-page') }}" class="group bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg p-4 md:p-6 hover-lift text-white">
                    <div class="text-3xl md:text-4xl mb-2 md:mb-3 transform group-hover:scale-110 transition">📄</div>
                    <h3 class="font-bold text-sm md:text-base mb-1">Invoices</h3>
                    <p class="text-xs text-indigo-100 hidden md:block">Generate PDFs</p>
                </a>
            </div>
        </div>

        <!-- System Modules Section -->
        <div class="mb-6 md:mb-10 fade-in">
            <div class="flex items-center gap-3 mb-4 md:mb-6">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-500 p-2 md:p-3 rounded-xl shadow-lg">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800">System Modules</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-5">
                <!-- Books Management -->
                <a href="{{ route('accountant.books') }}" class="bg-white rounded-xl shadow-md hover:shadow-2xl p-4 md:p-6 hover-lift border-l-4 border-blue-500">
                    <div class="flex items-start gap-3 md:gap-4">
                        <div class="text-3xl md:text-4xl">📚</div>
                        <div class="flex-1">
                            <h3 class="text-base md:text-lg font-bold text-blue-600 mb-1">Books Management</h3>
                            <p class="text-xs md:text-sm text-gray-600">Manage account books</p>
                        </div>
                    </div>
                </a>

                <!-- Particulars -->
                <a href="{{ route('accountant.particulars') }}" class="bg-white rounded-xl shadow-md hover:shadow-2xl p-4 md:p-6 hover-lift border-l-4 border-green-500">
                    <div class="flex items-start gap-3 md:gap-4">
                        <div class="text-3xl md:text-4xl">📋</div>
                        <div class="flex-1">
                            <h3 class="text-base md:text-lg font-bold text-green-600 mb-1">Particulars</h3>
                            <p class="text-xs md:text-sm text-gray-600">Manage fee types</p>
                        </div>
                    </div>
                </a>

                <!-- Fee Entry -->
                <a href="{{ route('accountant.fee-entry') }}" class="bg-white rounded-xl shadow-md hover:shadow-2xl p-4 md:p-6 hover-lift border-l-4 border-purple-500">
                    <div class="flex items-start gap-3 md:gap-4">
                        <div class="text-3xl md:text-4xl">💰</div>
                        <div class="flex-1">
                            <h3 class="text-base md:text-lg font-bold text-purple-600 mb-1">Fee Entry</h3>
                            <p class="text-xs md:text-sm text-gray-600">Record transactions</p>
                        </div>
                    </div>
                </a>

                <!-- Ledgers -->
                <a href="{{ route('accountant.ledgers') }}" class="bg-white rounded-xl shadow-md hover:shadow-2xl p-4 md:p-6 hover-lift border-l-4 border-orange-500">
                    <div class="flex items-start gap-3 md:gap-4">
                        <div class="text-3xl md:text-4xl">📊</div>
                        <div class="flex-1">
                            <h3 class="text-base md:text-lg font-bold text-orange-600 mb-1">Ledgers</h3>
                            <p class="text-xs md:text-sm text-gray-600">Student & class reports</p>
                        </div>
                    </div>
                </a>

                <!-- Particular Ledger -->
                <a href="{{ route('accountant.particular-ledger') }}" class="bg-white rounded-xl shadow-md hover:shadow-2xl p-4 md:p-6 hover-lift border-l-4 border-teal-500">
                    <div class="flex items-start gap-3 md:gap-4">
                        <div class="text-3xl md:text-4xl">📋</div>
                        <div class="flex-1">
                            <h3 class="text-base md:text-lg font-bold text-teal-600 mb-1">Particular Ledger</h3>
                            <p class="text-xs md:text-sm text-gray-600">Fee type reports</p>
                        </div>
                    </div>
                </a>

                <!-- Overdue Payments -->
                <a href="{{ route('accountant.overdue') }}" class="bg-white rounded-xl shadow-md hover:shadow-2xl p-4 md:p-6 hover-lift border-l-4 border-red-500">
                    <div class="flex items-start gap-3 md:gap-4">
                        <div class="text-3xl md:text-4xl">💸</div>
                        <div class="flex-1">
                            <h3 class="text-base md:text-lg font-bold text-red-600 mb-1">Overdue Payments</h3>
                            <p class="text-xs md:text-sm text-gray-600">Track overdue fees</p>
                        </div>
                    </div>
                </a>

                <!-- Suspense Accounts -->
                <a href="{{ route('accountant.suspense') }}" class="bg-white rounded-xl shadow-md hover:shadow-2xl p-4 md:p-6 hover-lift border-l-4 border-amber-500">
                    <div class="flex items-start gap-3 md:gap-4">
                        <div class="text-3xl md:text-4xl">⏳</div>
                        <div class="flex-1">
                            <h3 class="text-base md:text-lg font-bold text-amber-600 mb-1">Suspense Accounts</h3>
                            <p class="text-xs md:text-sm text-gray-600">Unidentified transactions</p>
                        </div>
                    </div>
                </a>

                <!-- Payroll -->
                <a href="{{ route('accountant.payroll') }}" class="bg-white rounded-xl shadow-md hover:shadow-2xl p-4 md:p-6 hover-lift border-l-4 border-yellow-500">
                    <div class="flex items-start gap-3 md:gap-4">
                        <div class="text-3xl md:text-4xl">💵</div>
                        <div class="flex-1">
                            <h3 class="text-base md:text-lg font-bold text-yellow-600 mb-1">Payroll</h3>
                            <p class="text-xs md:text-sm text-gray-600">Staff salary management</p>
                        </div>
                    </div>
                </a>

                <!-- Expenses -->
                <a href="{{ route('accountant.expenses') }}" class="bg-white rounded-xl shadow-md hover:shadow-2xl p-4 md:p-6 hover-lift border-l-4 border-rose-500">
                    <div class="flex items-start gap-3 md:gap-4">
                        <div class="text-3xl md:text-4xl">💳</div>
                        <div class="flex-1">
                            <h3 class="text-base md:text-lg font-bold text-rose-600 mb-1">Expenses</h3>
                            <p class="text-xs md:text-sm text-gray-600">Manage school expenses</p>
                        </div>
                    </div>
                </a>

                <!-- Bank API Integration -->
                <a href="{{ route('accountant.bank-api') }}" class="bg-gradient-to-br from-green-500 to-teal-500 rounded-xl shadow-md hover:shadow-2xl p-4 md:p-6 hover-lift border-2 border-green-300">
                    <div class="flex items-start gap-3 md:gap-4">
                        <div class="text-3xl md:text-4xl">🏦</div>
                        <div class="flex-1">
                            <h3 class="text-base md:text-lg font-bold text-white mb-1">Bank API Integration</h3>
                            <p class="text-xs md:text-sm text-green-50">Automated bank payments</p>
                            <div class="mt-2">
                                <span class="px-2 py-1 bg-white bg-opacity-30 rounded text-xs font-bold text-white">NEW!</span>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- SMS & Communication -->
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl shadow-md hover:shadow-2xl p-4 md:p-6 hover-lift border-2 border-indigo-200">
                    <div class="flex items-start gap-3 md:gap-4 mb-3">
                        <div class="text-3xl md:text-4xl">📱</div>
                        <div class="flex-1">
                            <h3 class="text-base md:text-lg font-bold text-indigo-600 mb-1">SMS & Communication</h3>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <a href="{{ route('accountant.sms') }}" class="block bg-white hover:bg-indigo-50 px-3 py-2 rounded-lg border border-indigo-200 text-xs md:text-sm font-semibold text-indigo-700 transition">
                            📤 Send SMS
                        </a>
                        <a href="{{ route('accountant.phone-numbers') }}" class="block bg-white hover:bg-indigo-50 px-3 py-2 rounded-lg border border-indigo-200 text-xs md:text-sm font-semibold text-indigo-700 transition">
                            📞 Phone Numbers
                        </a>
                        <a href="{{ route('accountant.sms-logs') }}" class="block bg-white hover:bg-indigo-50 px-3 py-2 rounded-lg border border-indigo-200 text-xs md:text-sm font-semibold text-indigo-700 transition">
                            📜 SMS Logs
                        </a>
                    </div>
                </div>

                <!-- Student Invoices -->
                <a href="{{ route('accountant.invoices-page') }}" class="bg-white rounded-xl shadow-md hover:shadow-2xl p-4 md:p-6 hover-lift border-l-4 border-purple-500">
                    <div class="flex items-start gap-3 md:gap-4">
                        <div class="text-3xl md:text-4xl">📄</div>
                        <div class="flex-1">
                            <h3 class="text-base md:text-lg font-bold text-purple-600 mb-1">Student Invoices</h3>
                            <p class="text-xs md:text-sm text-gray-600">Generate invoices</p>
                        </div>
                    </div>
                </a>

                <!-- Class Management -->
                <a href="{{ route('accountant.classes') }}" class="bg-white rounded-xl shadow-md hover:shadow-2xl p-4 md:p-6 hover-lift border-l-4 border-indigo-500">
                    <div class="flex items-start gap-3 md:gap-4">
                        <div class="text-3xl md:text-4xl">🎓</div>
                        <div class="flex-1">
                            <h3 class="text-base md:text-lg font-bold text-indigo-600 mb-1">Class Management</h3>
                            <p class="text-xs md:text-sm text-gray-600">Manage school classes</p>
                        </div>
                    </div>
                </a>

                <!-- Student Management -->
                <a href="{{ route('accountant.students') }}" class="bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl shadow-md hover:shadow-2xl p-4 md:p-6 hover-lift border-2 border-blue-300">
                    <div class="flex items-start gap-3 md:gap-4">
                        <div class="text-3xl md:text-4xl">👨‍🎓</div>
                        <div class="flex-1">
                            <h3 class="text-base md:text-lg font-bold text-white mb-1">Student Management</h3>
                            <p class="text-xs md:text-sm text-blue-50">Add & manage students</p>
                            <div class="mt-2">
                                <span class="px-2 py-1 bg-white bg-opacity-30 rounded text-xs font-bold text-white">BULK IMPORT</span>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Student Promotion -->
                <a href="{{ route('students.promotion-page') }}" class="bg-white rounded-xl shadow-md hover:shadow-2xl p-4 md:p-6 hover-lift border-l-4 border-pink-500">
                    <div class="flex items-start gap-3 md:gap-4">
                        <div class="text-3xl md:text-4xl">📚</div>
                        <div class="flex-1">
                            <h3 class="text-base md:text-lg font-bold text-pink-600 mb-1">Student Promotion</h3>
                            <p class="text-xs md:text-sm text-gray-600">Promote students to next class</p>
                        </div>
                    </div>
                </a>

                <!-- School Settings -->
                <a href="{{ route('accountant.settings') }}" class="bg-white rounded-xl shadow-md hover:shadow-2xl p-4 md:p-6 hover-lift border-l-4 border-gray-500">
                    <div class="flex items-start gap-3 md:gap-4">
                        <div class="text-3xl md:text-4xl">⚙️</div>
                        <div class="flex-1">
                            <h3 class="text-base md:text-lg font-bold text-gray-600 mb-1">School Settings</h3>
                            <p class="text-xs md:text-sm text-gray-600">Configure system</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12 py-6">
        <div class="container mx-auto px-6 text-center">
            <p class="text-sm md:text-base">&copy; {{ date('Y') }} Darasa Finance ERP. All rights reserved.</p>
            <p class="text-xs text-gray-400 mt-2">Empowering schools with smart financial management</p>
        </div>
    </footer>

    <script>
        let collectionChart, paymentMethodsChart, particularsChart;
        let currentPeriod = 'today';
        let chartsInitialized = false;
        let allClassStats = [];
        let allParticularStats = [];
        let selectedParticulars = new Set();

        // Initialize on page load with delay to prevent blackout
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize charts first
            initCharts();

            // Initialize Flatpickr for custom date range
            flatpickr("#custom-from-date", {
                dateFormat: "Y-m-d",
                maxDate: "today"
            });

            flatpickr("#custom-to-date", {
                dateFormat: "Y-m-d",
                maxDate: "today"
            });

            // Analytics will be loaded automatically after charts are initialized
        });

        function loadAnalytics(period) {
            currentPeriod = period;
            console.log('Loading analytics for period:', period);

            // Update active button
            document.querySelectorAll('.analytics-btn').forEach(btn => {
                btn.classList.remove('bg-blue-500', 'text-white', 'shadow-lg');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            });
            const activeBtn = document.getElementById('btn-' + period);
            if (activeBtn) {
                activeBtn.classList.remove('bg-gray-200', 'text-gray-700');
                activeBtn.classList.add('bg-blue-500', 'text-white', 'shadow-lg');
            }

            // Show skeleton while loading
            showSkeletonCards();

            // Fetch analytics data
            axios.get('/api/analytics/' + period)
                .then(response => {
                    console.log('Analytics data received for', period, ':', response.data);
                    setTimeout(() => updateAnalyticsUI(response.data), 300);
                })
                .catch(error => {
                    console.error('Error loading analytics for', period, ':', error);
                    setTimeout(() => showSampleData(period), 300);
                });
        }

        function showSkeletonCards() {
            document.getElementById('analytics-cards').innerHTML = `
                <div class="skeleton rounded-xl h-32 md:h-36"></div>
                <div class="skeleton rounded-xl h-32 md:h-36"></div>
                <div class="skeleton rounded-xl h-32 md:h-36"></div>
                <div class="skeleton rounded-xl h-32 md:h-36"></div>
            `;
        }

        function showSampleData(period) {
            const periodLabels = {
                'today': 'today',
                'weekly': 'this week',
                'monthly': 'this month',
                'yearly': 'this year'
            };

            const sampleData = {
                'today': { collected: 1500000, expected: 2000000, overdue: 500000, students: 12, rate: 75 },
                'weekly': { collected: 8500000, expected: 12000000, overdue: 3500000, students: 45, rate: 71 },
                'monthly': { collected: 35000000, expected: 50000000, overdue: 15000000, students: 125, rate: 70 },
                'yearly': { collected: 420000000, expected: 600000000, overdue: 180000000, students: 320, rate: 70 }
            };

            const data = sampleData[period];
            renderAnalyticsCards(data, periodLabels[period]);
        }

        function updateAnalyticsUI(data) {
            // Calculate collection rate
            const totalExpected = data.summary.total_expected || 0;
            const totalCollections = data.summary.total_collections || 0;
            const collectionRate = totalExpected > 0
                ? ((totalCollections / totalExpected) * 100).toFixed(1)
                : 0;

            renderAnalyticsCards({
                collected: totalCollections,
                expected: totalExpected,
                overdue: data.summary.outstanding_balance || 0,
                students: data.summary.active_students || 0,
                rate: collectionRate
            }, currentPeriod);

            // Update class statistics
            if (data.class_stats && data.class_stats.length > 0) {
                allClassStats = data.class_stats;
                displayClassStats(allClassStats);
                populateClassFilter(allClassStats);

                // Calculate totals for summary cards
                const totalStudents = allClassStats.reduce((sum, stat) => sum + stat.total_students, 0);
                const completedStudents = allClassStats.reduce((sum, stat) => sum + stat.completed_students, 0);
                const incompleteStudents = totalStudents - completedStudents;
                const overallRate = totalStudents > 0 ? ((completedStudents / totalStudents) * 100).toFixed(1) : 0;

                document.getElementById('total-students').textContent = totalStudents;
                document.getElementById('completed-students').textContent = completedStudents;
                document.getElementById('incomplete-students').textContent = incompleteStudents;
                document.getElementById('completion-rate').textContent = overallRate + '%';
            } else {
                // No class stats available
                allClassStats = [];
                displayClassStats([]);
                document.getElementById('total-students').textContent = '0';
                document.getElementById('completed-students').textContent = '0';
                document.getElementById('incomplete-students').textContent = '0';
                document.getElementById('completion-rate').textContent = '0%';
            }

            // Update particular statistics
            if (data.particulars_data && data.particulars_data.length > 0) {
                allParticularStats = data.particulars_data;
                populateParticularCheckboxes(allParticularStats);
            } else {
                allParticularStats = [];
                populateParticularCheckboxes([]);
            }

            // Update charts with real data
            if (chartsInitialized) {
                updateChartsData(data);
            }
        }

        function displayClassStats(stats) {
            const tbody = document.getElementById('class-stats-table');

            if (stats.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center">
                            <div class="text-gray-500">
                                <p class="font-semibold mb-2">No class data available</p>
                                <p class="text-sm">Students need to be assigned to classes (Grade 1-6, Form 1-4) to view class-wise statistics.</p>
                                <a href="{{ route('accountant.students') }}" class="inline-block mt-3 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm">
                                    Manage Students
                                </a>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = stats.map(stat => {
                const incompleteStudents = stat.total_students - stat.completed_students;
                return `
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3 font-semibold">${stat.class_name}</td>
                    <td class="px-4 py-3">
                        <span class="font-bold text-green-600">${stat.completed_students}</span> /
                        <span class="text-gray-600">${stat.total_students}</span>
                        <span class="text-xs text-gray-500">(${incompleteStudents} incomplete)</span>
                    </td>
                    <td class="px-4 py-3 font-semibold">TSH ${Math.round(stat.expected_amount).toLocaleString()}</td>
                    <td class="px-4 py-3 font-semibold text-green-600">TSH ${Math.round(stat.collected_amount).toLocaleString()}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: ${stat.collection_rate}%"></div>
                            </div>
                            <span class="font-bold text-sm">${stat.collection_rate}%</span>
                        </div>
                    </td>
                </tr>
            `}).join('');
        }

        function populateClassFilter(stats) {
            const filter = document.getElementById('class-filter');
            const currentValue = filter.value;

            filter.innerHTML = '<option value="">All Classes</option>';
            stats.forEach(stat => {
                filter.innerHTML += `<option value="${stat.class_name}">${stat.class_name}</option>`;
            });

            if (currentValue) {
                filter.value = currentValue;
            }
        }

        function filterClassStats() {
            const selectedClass = document.getElementById('class-filter').value;
            const searchTerm = document.getElementById('student-search').value.toLowerCase().trim();

            let filteredStats = allClassStats;

            // Filter by class if selected
            if (selectedClass) {
                filteredStats = filteredStats.filter(stat => stat.class_name === selectedClass);
            }

            // Apply search filter if there's a search term
            if (searchTerm) {
                // This will filter the stats display, but for student search we'll need more data
                // For now, just show stats that match
                displayClassStats(filteredStats);
            } else {
                displayClassStats(filteredStats);
            }
        }

        let autocompleteTimeout;

        function showAutocomplete() {
            const searchTerm = document.getElementById('student-search').value.trim();
            const dropdown = document.getElementById('autocomplete-dropdown');

            // Clear previous timeout
            clearTimeout(autocompleteTimeout);

            if (!searchTerm || searchTerm.length < 2) {
                dropdown.classList.add('hidden');
                dropdown.innerHTML = '';
                if (!searchTerm) {
                    filterClassStats();
                }
                return;
            }

            // Debounce the API call
            autocompleteTimeout = setTimeout(() => {
                axios.get('/api/students/search?q=' + encodeURIComponent(searchTerm))
                    .then(response => {
                        if (response.data && response.data.length > 0) {
                            displayAutocomplete(response.data);
                        } else {
                            dropdown.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500">No students found</div>';
                            dropdown.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching autocomplete:', error);
                        dropdown.classList.add('hidden');
                    });
            }, 300);
        }

        function displayAutocomplete(students) {
            const dropdown = document.getElementById('autocomplete-dropdown');

            dropdown.innerHTML = students.map(student => `
                <div class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b last:border-b-0" onclick="selectStudent(${student.id}, '${student.name.replace(/'/g, "\\'")}', '${student.student_reg_no}')">
                    <div class="font-semibold text-sm">${student.name}</div>
                    <div class="text-xs text-gray-500">Reg: ${student.student_reg_no} | Class: ${student.class}</div>
                </div>
            `).join('');

            dropdown.classList.remove('hidden');
        }

        function selectStudent(studentId, studentName, studentRegNo) {
            const searchInput = document.getElementById('student-search');
            const dropdown = document.getElementById('autocomplete-dropdown');

            // Set the search input value
            searchInput.value = studentName + ' (' + studentRegNo + ')';

            // Hide dropdown
            dropdown.classList.add('hidden');

            // Fetch and display student's payment details
            searchStudentById(studentId);
        }

        function searchStudentById(studentId) {
            const tbody = document.getElementById('class-stats-table');

            // Show loading state
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                        <p class="font-semibold mb-2">Loading student payment details...</p>
                    </td>
                </tr>
            `;

            // Fetch student payment summary
            axios.get('/api/students/' + studentId + '/payment-summary')
                .then(response => {
                    const student = response.data;

                    tbody.innerHTML = `
                        <tr class="bg-blue-50">
                            <td colspan="5" class="px-6 py-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Student Name</p>
                                        <p class="text-lg font-bold text-gray-800">${student.name}</p>
                                        <p class="text-sm text-gray-600">Reg: ${student.student_reg_no}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Class</p>
                                        <p class="text-lg font-bold text-blue-600">${student.class}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Assignments</p>
                                        <p class="text-lg font-bold">
                                            <span class="text-green-600">${student.completed_assignments}</span>
                                            <span class="text-gray-400">/</span>
                                            <span class="text-gray-600">${student.total_assignments}</span>
                                        </p>
                                        <p class="text-xs text-gray-500">Completed / Total</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Expected Amount</p>
                                        <p class="text-lg font-bold text-gray-700">TSH ${student.total_expected.toLocaleString()}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Collected Amount</p>
                                        <p class="text-lg font-bold text-green-600">TSH ${student.total_collected.toLocaleString()}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Collection Rate</p>
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1 bg-gray-200 rounded-full h-3">
                                                <div class="bg-green-500 h-3 rounded-full transition-all" style="width: ${student.collection_rate}%"></div>
                                            </div>
                                            <span class="text-lg font-bold text-green-600">${student.collection_rate}%</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                })
                .catch(error => {
                    console.error('Error fetching student payment summary:', error);
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-red-500">
                                <p class="font-semibold mb-2">Error loading student details</p>
                                <p class="text-sm">${error.response?.data?.error || error.message}</p>
                            </td>
                        </tr>
                    `;
                });
        }

        // Hide autocomplete when clicking outside
        document.addEventListener('click', function(event) {
            const searchInput = document.getElementById('student-search');
            const dropdown = document.getElementById('autocomplete-dropdown');

            if (searchInput && dropdown && !searchInput.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        function displayStudentSearchResults(students) {
            const tbody = document.getElementById('class-stats-table');

            tbody.innerHTML = students.map(student => `
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-3 font-semibold">${student.name}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs text-gray-500">Reg: ${student.student_reg_no || 'N/A'}</span><br>
                        <span class="text-xs text-gray-500">Class: ${student.class || 'Not Assigned'}</span>
                    </td>
                    <td class="px-4 py-3 font-semibold">TSH ${(student.total_expected || 0).toLocaleString()}</td>
                    <td class="px-4 py-3 font-semibold text-green-600">TSH ${(student.total_collected || 0).toLocaleString()}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: ${student.collection_rate || 0}%"></div>
                            </div>
                            <span class="font-bold text-sm">${student.collection_rate || 0}%</span>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function populateParticularCheckboxes(stats) {
            const container = document.getElementById('particular-checkboxes');
            selectedParticulars.clear();

            if (stats.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-sm">No particulars data available</p>';
                return;
            }

            container.innerHTML = stats.map(stat => {
                selectedParticulars.add(stat.particular_name);
                return `
                    <label class="flex items-center gap-2 bg-gray-100 px-3 py-2 rounded">
                        <input type="checkbox" class="particular-checkbox" value="${stat.particular_name}" onchange="updateParticularsChart()" checked>
                        <span class="text-sm font-semibold">${stat.particular_name}</span>
                    </label>
                `;
            }).join('');

            if (chartsInitialized) {
                updateParticularsChart();
            }
        }

        function toggleAllParticulars() {
            const selectAll = document.getElementById('select-all-particulars').checked;
            const checkboxes = document.querySelectorAll('.particular-checkbox');

            selectedParticulars.clear();
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll;
                if (selectAll) {
                    selectedParticulars.add(checkbox.value);
                }
            });

            updateParticularsChart();
        }

        function updateParticularsChart() {
            const checkboxes = document.querySelectorAll('.particular-checkbox:checked');
            selectedParticulars.clear();
            checkboxes.forEach(checkbox => selectedParticulars.add(checkbox.value));

            // Update select all checkbox state
            const allCheckboxes = document.querySelectorAll('.particular-checkbox');
            const selectAllCheckbox = document.getElementById('select-all-particulars');
            selectAllCheckbox.checked = checkboxes.length === allCheckboxes.length;

            if (particularsChart && allParticularStats.length > 0) {
                const filteredStats = allParticularStats.filter(stat => selectedParticulars.has(stat.particular_name));

                particularsChart.data.labels = filteredStats.map(s => s.particular_name);
                particularsChart.data.datasets[0].data = filteredStats.map(s => s.expected);
                particularsChart.data.datasets[1].data = filteredStats.map(s => s.collected);
                particularsChart.update();
            }
        }

        function renderAnalyticsCards(data, period) {
            const cardsHTML = `
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl shadow-xl p-4 md:p-6 hover-lift fade-in">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <p class="text-blue-100 text-xs md:text-sm font-medium">Total Fee Collected</p>
                            <h3 class="text-2xl md:text-3xl font-bold mt-2">TSH ${Math.round(data.collected).toLocaleString()}</h3>
                            <p class="text-xs md:text-sm mt-2 text-blue-100">${period}</p>
                        </div>
                        <div class="text-3xl md:text-4xl opacity-80">💰</div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl shadow-xl p-4 md:p-6 hover-lift fade-in">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <p class="text-green-100 text-xs md:text-sm font-medium">Expected Fees</p>
                            <h3 class="text-2xl md:text-3xl font-bold mt-2">TSH ${Math.round(data.expected).toLocaleString()}</h3>
                            <p class="text-xs md:text-sm mt-2 text-green-100">${period}</p>
                        </div>
                        <div class="text-3xl md:text-4xl opacity-80">📊</div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-xl shadow-xl p-4 md:p-6 hover-lift fade-in">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <p class="text-red-100 text-xs md:text-sm font-medium">Total Overdue</p>
                            <h3 class="text-2xl md:text-3xl font-bold mt-2">TSH ${Math.round(data.overdue).toLocaleString()}</h3>
                            <p class="text-xs md:text-sm mt-2 text-red-100">${data.students} students</p>
                        </div>
                        <div class="text-3xl md:text-4xl opacity-80">💸</div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl shadow-xl p-4 md:p-6 hover-lift fade-in">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <p class="text-purple-100 text-xs md:text-sm font-medium">Collection Rate</p>
                            <h3 class="text-2xl md:text-3xl font-bold mt-2">${data.rate}%</h3>
                            <p class="text-xs md:text-sm mt-2 text-purple-100">${period}</p>
                        </div>
                        <div class="text-3xl md:text-4xl opacity-80">📈</div>
                    </div>
                </div>
            `;

            document.getElementById('analytics-cards').innerHTML = cardsHTML;
        }

        function initCharts() {
            if (chartsInitialized) return;

            // Dynamically load Chart.js
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.onload = function() {
                createCharts();
                chartsInitialized = true;
                // Load analytics after charts are ready
                loadAnalytics('today');
            };
            document.head.appendChild(script);
        }

        function createCharts() {
            // Collection Trend Chart
            const ctxCollection = document.getElementById('collectionChart').getContext('2d');
            collectionChart = new Chart(ctxCollection, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Collected',
                        data: [1200000, 1500000, 1100000, 1800000, 2000000, 900000, 1500000],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: false,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgb(59, 130, 246)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'TSH ' + (value / 1000000).toFixed(1) + 'M';
                                }
                            }
                        }
                    }
                }
            });

            // Books Distribution Chart
            const ctxPayment = document.getElementById('paymentMethodsChart').getContext('2d');
            paymentMethodsChart = new Chart(ctxPayment, {
                type: 'doughnut',
                data: {
                    labels: ['Loading...'],
                    datasets: [{
                        data: [1],
                        backgroundColor: [
                            'rgb(59, 130, 246)',
                            'rgb(16, 185, 129)',
                            'rgb(249, 115, 22)',
                            'rgb(168, 85, 247)',
                            'rgb(236, 72, 153)',
                            'rgb(251, 191, 36)',
                            'rgb(139, 92, 246)',
                            'rgb(14, 165, 233)'
                        ],
                        borderWidth: 3,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += 'TSH ' + context.parsed.toLocaleString();
                                    return label;
                                }
                            }
                        }
                    }
                }
            });

            // Particulars Bar Chart (Expected vs Collected by Particular)
            const ctxParticulars = document.getElementById('particularsChart').getContext('2d');
            particularsChart = new Chart(ctxParticulars, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Expected Amount',
                            data: [],
                            backgroundColor: 'rgba(147, 51, 234, 0.7)',
                            borderColor: 'rgb(147, 51, 234)',
                            borderWidth: 2
                        },
                        {
                            label: 'Collected Amount',
                            data: [],
                            backgroundColor: 'rgba(34, 197, 94, 0.7)',
                            borderColor: 'rgb(34, 197, 94)',
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += 'TSH ' + context.parsed.y.toLocaleString();
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1000000) {
                                        return 'TSH ' + (value / 1000000).toFixed(1) + 'M';
                                    } else if (value >= 1000) {
                                        return 'TSH ' + (value / 1000).toFixed(0) + 'K';
                                    }
                                    return 'TSH ' + value;
                                }
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });
        }

        function updateChartsData(data) {
            console.log('Updating charts with data:', data);

            // Update collection trend chart - destroy and recreate for reliable redraw
            if (collectionChart && data.collection_trend) {
                const labels = data.collection_trend.map(d => d.label);
                const amounts = data.collection_trend.map(d => parseFloat(d.amount) || 0);
                const maxAmount = Math.max(...amounts, 100); // Minimum 100 for scale

                console.log('Line graph update:', { points: labels.length, max: maxAmount, amounts: amounts });

                // Destroy and recreate for clean redraw
                const canvas = document.getElementById('collectionChart');
                collectionChart.destroy();

                collectionChart = new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Collections',
                            data: amounts,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.15)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3,
                            pointRadius: 5,
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointHoverRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 600 },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(0,0,0,0.8)',
                                padding: 12,
                                titleFont: { size: 14 },
                                bodyFont: { size: 13 },
                                callbacks: {
                                    label: ctx => 'TSH ' + ctx.parsed.y.toLocaleString()
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                min: 0,
                                suggestedMax: maxAmount * 1.2,
                                ticks: {
                                    callback: v => v >= 1000000 ? 'TSH ' + (v/1000000).toFixed(1) + 'M' : (v >= 1000 ? 'TSH ' + (v/1000).toFixed(0) + 'K' : 'TSH ' + v)
                                },
                                grid: { color: 'rgba(0,0,0,0.05)' }
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });
                console.log('Line chart recreated successfully');
            }

            // Update books distribution chart and legend
            if (paymentMethodsChart && data.books_distribution) {
                const colors = [
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(249, 115, 22)',
                    'rgb(168, 85, 247)',
                    'rgb(236, 72, 153)',
                    'rgb(251, 191, 36)',
                    'rgb(139, 92, 246)',
                    'rgb(14, 165, 233)'
                ];

                if (data.books_distribution && data.books_distribution.length > 0) {
                    console.log('Updating pie chart with', data.books_distribution.length, 'books');
                    // Update chart data
                    paymentMethodsChart.data.labels = data.books_distribution.map(d => d.name);
                    paymentMethodsChart.data.datasets[0].data = data.books_distribution.map(d => d.amount);
                    paymentMethodsChart.data.datasets[0].backgroundColor = data.books_distribution.map((d, i) => colors[i % colors.length]);

                    // Calculate total for percentage
                    const total = data.books_distribution.reduce((sum, book) => sum + parseFloat(book.amount), 0);
                    console.log('Total for pie chart:', total);

                    // Update legend with percentages
                    const legendHTML = data.books_distribution.map((book, index) => {
                        const color = colors[index % colors.length];
                        const percentage = total > 0 ? ((parseFloat(book.amount) / total) * 100).toFixed(1) : 0;
                        return `
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full" style="background-color: ${color}"></div>
                                <span class="text-gray-700 font-medium">${book.name}: ${percentage}%</span>
                            </div>
                        `;
                    }).join('');
                    document.getElementById('books-legend').innerHTML = legendHTML;
                } else {
                    // Show message when no book data is available
                    paymentMethodsChart.data.labels = ['No book transactions yet'];
                    paymentMethodsChart.data.datasets[0].data = [1];
                    paymentMethodsChart.data.datasets[0].backgroundColor = ['rgb(209, 213, 219)'];
                    document.getElementById('books-legend').innerHTML = '<p class="text-gray-500 text-sm col-span-2">No book data available</p>';
                }
                paymentMethodsChart.update();
            }

            // Update particulars chart
            if (particularsChart && data.particulars_data) {
                if (data.particulars_data.length > 0) {
                    const filteredStats = data.particulars_data.filter(stat =>
                        selectedParticulars.size === 0 || selectedParticulars.has(stat.particular_name)
                    );
                    particularsChart.data.labels = filteredStats.map(s => s.particular_name);
                    particularsChart.data.datasets[0].data = filteredStats.map(s => s.expected);
                    particularsChart.data.datasets[1].data = filteredStats.map(s => s.collected);
                } else {
                    particularsChart.data.labels = [];
                    particularsChart.data.datasets[0].data = [];
                    particularsChart.data.datasets[1].data = [];
                }
                particularsChart.update();
            }
        }

        // Sidebar functions
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        function toggleCategory(category) {
            const submenu = document.getElementById(category + '-submenu');
            const arrow = document.getElementById(category + '-arrow');

            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                arrow.classList.add('rotate-180');
            } else {
                submenu.classList.add('hidden');
                arrow.classList.remove('rotate-180');
            }
        }

        // Custom date picker functions
        function showCustomDatePicker() {
            document.getElementById('custom-date-picker').classList.remove('hidden');

            // Update button states
            document.querySelectorAll('.analytics-btn').forEach(btn => {
                btn.classList.remove('bg-blue-500', 'text-white', 'shadow-lg');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            });
            const customBtn = document.getElementById('btn-custom');
            customBtn.classList.remove('bg-gray-200', 'text-gray-700');
            customBtn.classList.add('bg-blue-500', 'text-white', 'shadow-lg');
        }

        function hideCustomDatePicker() {
            document.getElementById('custom-date-picker').classList.add('hidden');
            document.getElementById('custom-from-date').value = '';
            document.getElementById('custom-to-date').value = '';

            // Reset to today view
            loadAnalytics('today');
        }

        function applyCustomDateRange() {
            const fromDate = document.getElementById('custom-from-date').value;
            const toDate = document.getElementById('custom-to-date').value;

            if (!fromDate || !toDate) {
                alert('Please select both start and end dates');
                return;
            }

            if (new Date(fromDate) > new Date(toDate)) {
                alert('Start date must be before end date');
                return;
            }

            // Show skeleton while loading
            showSkeletonCards();

            // Fetch analytics data with custom date range
            axios.get('/api/analytics/custom', {
                params: {
                    from_date: fromDate,
                    to_date: toDate
                }
            })
            .then(response => {
                setTimeout(() => updateAnalyticsUI(response.data), 300);
            })
            .catch(error => {
                console.error('Error loading custom analytics:', error);
                alert('Error loading analytics for custom date range. The API endpoint may not be implemented yet.');
                hideCustomDatePicker();
            });
        }
    </script>
</body>
</html>

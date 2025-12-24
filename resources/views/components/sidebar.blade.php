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
                <a href="{{ route('accountant.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('accountant.dashboard') ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg' : 'bg-slate-700 hover:bg-slate-600 text-gray-300 hover:text-white' }} transition-all transform hover:scale-105">
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
                    <svg class="w-4 h-4 transform transition-transform {{ request()->routeIs('accountant.fee-entry', 'accountant.ledgers', 'accountant.particular-ledger', 'accountant.overdue', 'accountant.suspense') ? 'rotate-180' : '' }}" id="finance-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="finance-submenu" class="ml-6 mt-1 space-y-1 {{ request()->routeIs('accountant.fee-entry', 'accountant.ledgers', 'accountant.particular-ledger', 'accountant.overdue', 'accountant.suspense') ? '' : 'hidden' }}">
                    <a href="{{ route('accountant.fee-entry') }}" class="block px-4 py-2 text-sm rounded transition {{ request()->routeIs('accountant.fee-entry') ? 'bg-blue-500 text-white font-semibold' : 'text-gray-300 hover:text-blue-400 hover:bg-slate-700' }}">Fee Entry</a>
                    <a href="{{ route('accountant.ledgers') }}" class="block px-4 py-2 text-sm rounded transition {{ request()->routeIs('accountant.ledgers') ? 'bg-blue-500 text-white font-semibold' : 'text-gray-300 hover:text-blue-400 hover:bg-slate-700' }}">Ledgers</a>
                    <a href="{{ route('accountant.particular-ledger') }}" class="block px-4 py-2 text-sm rounded transition {{ request()->routeIs('accountant.particular-ledger') ? 'bg-blue-500 text-white font-semibold' : 'text-gray-300 hover:text-blue-400 hover:bg-slate-700' }}">Particular Ledger</a>
                    <a href="{{ route('accountant.overdue') }}" class="block px-4 py-2 text-sm rounded transition {{ request()->routeIs('accountant.overdue') ? 'bg-blue-500 text-white font-semibold' : 'text-gray-300 hover:text-blue-400 hover:bg-slate-700' }}">Overdue Payments</a>
                    <a href="{{ route('accountant.suspense') }}" class="block px-4 py-2 text-sm rounded transition {{ request()->routeIs('accountant.suspense') ? 'bg-blue-500 text-white font-semibold' : 'text-gray-300 hover:text-blue-400 hover:bg-slate-700' }}">Suspense Accounts</a>
                </div>
            </div>

            <!-- Books & Accounting -->
            <div class="sidebar-category">
                <button onclick="toggleCategory('books')" class="w-full flex items-center justify-between px-4 py-3 rounded-lg bg-slate-700 hover:bg-slate-600 text-white transition">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">📚</span>
                        <span class="font-semibold">Books & Accounting</span>
                    </div>
                    <svg class="w-4 h-4 transform transition-transform {{ request()->routeIs('accountant.books', 'accountant.particulars') ? 'rotate-180' : '' }}" id="books-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="books-submenu" class="ml-6 mt-1 space-y-1 {{ request()->routeIs('accountant.books', 'accountant.particulars') ? '' : 'hidden' }}">
                    <a href="{{ route('accountant.books') }}" class="block px-4 py-2 text-sm rounded transition {{ request()->routeIs('accountant.books') ? 'bg-blue-500 text-white font-semibold' : 'text-gray-300 hover:text-blue-400 hover:bg-slate-700' }}">Books Management</a>
                    <a href="{{ route('accountant.particulars') }}" class="block px-4 py-2 text-sm rounded transition {{ request()->routeIs('accountant.particulars') ? 'bg-blue-500 text-white font-semibold' : 'text-gray-300 hover:text-blue-400 hover:bg-slate-700' }}">Particulars</a>
                </div>
            </div>

            <!-- Expenses & Payroll -->
            <div class="sidebar-category">
                <button onclick="toggleCategory('expenses')" class="w-full flex items-center justify-between px-4 py-3 rounded-lg bg-slate-700 hover:bg-slate-600 text-white transition">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">💳</span>
                        <span class="font-semibold">Expenses & Payroll</span>
                    </div>
                    <svg class="w-4 h-4 transform transition-transform {{ request()->routeIs('accountant.payroll', 'accountant.expenses') ? 'rotate-180' : '' }}" id="expenses-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="expenses-submenu" class="ml-6 mt-1 space-y-1 {{ request()->routeIs('accountant.payroll', 'accountant.expenses') ? '' : 'hidden' }}">
                    <a href="{{ route('accountant.payroll') }}" class="block px-4 py-2 text-sm rounded transition {{ request()->routeIs('accountant.payroll') ? 'bg-blue-500 text-white font-semibold' : 'text-gray-300 hover:text-blue-400 hover:bg-slate-700' }}">Payroll</a>
                    <a href="{{ route('accountant.expenses') }}" class="block px-4 py-2 text-sm rounded transition {{ request()->routeIs('accountant.expenses') ? 'bg-blue-500 text-white font-semibold' : 'text-gray-300 hover:text-blue-400 hover:bg-slate-700' }}">Expenses</a>
                </div>
            </div>

            <!-- Student Management -->
            <div class="sidebar-category">
                <button onclick="toggleCategory('students')" class="w-full flex items-center justify-between px-4 py-3 rounded-lg bg-slate-700 hover:bg-slate-600 text-white transition">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">👨‍🎓</span>
                        <span class="font-semibold">Students</span>
                    </div>
                    <svg class="w-4 h-4 transform transition-transform {{ request()->routeIs('accountant.students', 'accountant.classes', 'students.promotion-page', 'accountant.invoices-page') ? 'rotate-180' : '' }}" id="students-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="students-submenu" class="ml-6 mt-1 space-y-1 {{ request()->routeIs('accountant.students', 'accountant.classes', 'students.promotion-page', 'accountant.invoices-page') ? '' : 'hidden' }}">
                    <a href="{{ route('accountant.students') }}" class="block px-4 py-2 text-sm rounded transition {{ request()->routeIs('accountant.students') ? 'bg-blue-500 text-white font-semibold' : 'text-gray-300 hover:text-blue-400 hover:bg-slate-700' }}">Student Management</a>
                    <a href="{{ route('accountant.classes') }}" class="block px-4 py-2 text-sm rounded transition {{ request()->routeIs('accountant.classes') ? 'bg-blue-500 text-white font-semibold' : 'text-gray-300 hover:text-blue-400 hover:bg-slate-700' }}">Class Management</a>
                    <a href="{{ route('students.promotion-page') }}" class="block px-4 py-2 text-sm rounded transition {{ request()->routeIs('students.promotion-page') ? 'bg-blue-500 text-white font-semibold' : 'text-gray-300 hover:text-blue-400 hover:bg-slate-700' }}">Student Promotion</a>
                    <a href="{{ route('accountant.invoices-page') }}" class="block px-4 py-2 text-sm rounded transition {{ request()->routeIs('accountant.invoices-page') ? 'bg-blue-500 text-white font-semibold' : 'text-gray-300 hover:text-blue-400 hover:bg-slate-700' }}">Student Invoices</a>
                </div>
            </div>

            <!-- Communication -->
            <div class="sidebar-category">
                <button onclick="toggleCategory('communication')" class="w-full flex items-center justify-between px-4 py-3 rounded-lg bg-slate-700 hover:bg-slate-600 text-white transition">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">📱</span>
                        <span class="font-semibold">Communication</span>
                    </div>
                    <svg class="w-4 h-4 transform transition-transform {{ request()->routeIs('accountant.sms', 'accountant.phone-numbers', 'accountant.sms-logs') ? 'rotate-180' : '' }}" id="communication-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="communication-submenu" class="ml-6 mt-1 space-y-1 {{ request()->routeIs('accountant.sms', 'accountant.phone-numbers', 'accountant.sms-logs') ? '' : 'hidden' }}">
                    <a href="{{ route('accountant.sms') }}" class="block px-4 py-2 text-sm rounded transition {{ request()->routeIs('accountant.sms') ? 'bg-blue-500 text-white font-semibold' : 'text-gray-300 hover:text-blue-400 hover:bg-slate-700' }}">Send SMS</a>
                    <a href="{{ route('accountant.phone-numbers') }}" class="block px-4 py-2 text-sm rounded transition {{ request()->routeIs('accountant.phone-numbers') ? 'bg-blue-500 text-white font-semibold' : 'text-gray-300 hover:text-blue-400 hover:bg-slate-700' }}">Phone Numbers</a>
                    <a href="{{ route('accountant.sms-logs') }}" class="block px-4 py-2 text-sm rounded transition {{ request()->routeIs('accountant.sms-logs') ? 'bg-blue-500 text-white font-semibold' : 'text-gray-300 hover:text-blue-400 hover:bg-slate-700' }}">SMS Logs</a>
                </div>
            </div>

            <!-- Integrations -->
            <div class="sidebar-item mt-2">
                <a href="{{ route('accountant.bank-api') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('accountant.bank-api') ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg' : 'bg-gradient-to-r from-green-600 to-emerald-600 text-white hover:from-green-500 hover:to-emerald-500 shadow-md' }}">
                    <span class="text-xl">🏦</span>
                    <span class="font-semibold">Bank Integration</span>
                </a>
            </div>

            <!-- Settings -->
            <div class="sidebar-item">
                <a href="{{ route('accountant.settings') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('accountant.settings') ? 'bg-blue-500 text-white font-semibold shadow-lg' : 'bg-slate-700 hover:bg-slate-600 text-gray-300 hover:text-white' }}">
                    <span class="text-xl">⚙️</span>
                    <span class="font-semibold">Settings</span>
                </a>
            </div>
        </nav>
    </div>
</div>

<!-- Sidebar Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40" onclick="toggleSidebar()"></div>

<!-- Sidebar JavaScript -->
<script>
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
</script>

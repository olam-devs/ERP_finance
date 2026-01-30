<!DOCTYPE html>
<html lang="{{ Session::get('parent_language', 'en') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Parent Portal - Darasa Finance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-gradient { background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%); }
        .glass-effect { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-gray-50 h-screen flex overflow-hidden">

    <!-- Mobile Header -->
    <div class="md:hidden fixed w-full z-50 top-0 left-0 bg-white shadow-md p-4 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded bg-indigo-600 flex items-center justify-center text-white font-bold">D</div>
            <span class="font-bold text-gray-800" data-translate="portal-title">Darasa Parent</span>
        </div>
        <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="text-gray-600 focus:outline-none">
            <i class="fas fa-bars text-xl"></i>
        </button>
    </div>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu" class="hidden fixed inset-0 z-40 bg-gray-800 bg-opacity-75 md:hidden" onclick="this.classList.add('hidden')">
        <div class="absolute right-0 top-0 h-full w-64 bg-white shadow-xl p-6 transform transition-transform" onclick="event.stopPropagation()">
             <div class="flex flex-col gap-6 mt-12">
                <a href="{{ route('parent.dashboard') }}" class="text-gray-700 font-medium hover:text-indigo-600 flex items-center gap-3">
                    <i class="fas fa-home w-6"></i> <span data-translate="menu-dashboard">Dashboard</span>
                </a>
                <a href="{{ route('parent.fees') }}" class="text-gray-700 font-medium hover:text-indigo-600 flex items-center gap-3">
                    <i class="fas fa-file-invoice-dollar w-6"></i> <span data-translate="menu-fees">Fees & Statements</span>
                </a>
                <a href="{{ route('parent.invoices') }}" class="text-gray-700 font-medium hover:text-indigo-600 flex items-center gap-3">
                    <i class="fas fa-file-invoice w-6"></i> <span data-translate="menu-invoices">Invoices</span>
                </a>
                <a href="{{ route('parent.messages') }}" class="text-gray-700 font-medium hover:text-indigo-600 flex items-center gap-3">
                    <i class="fas fa-envelope w-6"></i> <span data-translate="menu-messages">Messages</span>
                </a>
                <a href="{{ route('parent.notifications') }}" class="text-gray-700 font-medium hover:text-indigo-600 flex items-center gap-3">
                    <i class="fas fa-bell w-6"></i> <span data-translate="menu-notifications">Notifications</span>
                </a>
                <hr>
                <form action="{{ route('parent.logout') }}" method="POST">
                    @csrf
                    <button class="text-red-600 font-medium hover:text-red-700 flex items-center gap-3 w-full">
                        <i class="fas fa-sign-out-alt w-6"></i> <span data-translate="menu-logout">Logout</span>
                    </button>
                </form>
             </div>
        </div>
    </div>

    <!-- Sidebar (Desktop) -->
    <aside class="hidden md:flex flex-col w-64 sidebar-gradient text-white shadow-xl z-10 transition-all duration-300">
        <div class="p-6 flex items-center gap-3 border-b border-white/10">
            <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center backdrop-blur-sm">
                <i class="fas fa-graduation-cap text-xl"></i>
            </div>
            <div>
                <h1 class="font-bold text-lg tracking-wide">Darasa</h1>
                <p class="text-xs text-indigo-200 uppercase tracking-widest" data-translate="portal-subtitle">Parent Portal</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
            <a href="{{ route('parent.dashboard') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('parent.dashboard') ? 'bg-white/20 text-white shadow-lg' : 'text-indigo-100 hover:bg-white/10 hover:text-white' }}">
                <i class="fas fa-home w-6 text-center group-hover:scale-110 transition-transform"></i>
                <span class="font-medium" data-translate="menu-dashboard">Dashboard</span>
            </a>

            <a href="{{ route('parent.fees') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('parent.fees') ? 'bg-white/20 text-white shadow-lg' : 'text-indigo-100 hover:bg-white/10 hover:text-white' }}">
                <i class="fas fa-file-invoice-dollar w-6 text-center group-hover:scale-110 transition-transform"></i>
                <span class="font-medium" data-translate="menu-fees">Fee Statements</span>
            </a>

            <a href="{{ route('parent.invoices') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('parent.invoices') ? 'bg-white/20 text-white shadow-lg' : 'text-indigo-100 hover:bg-white/10 hover:text-white' }}">
                <i class="fas fa-file-invoice w-6 text-center group-hover:scale-110 transition-transform"></i>
                <span class="font-medium" data-translate="menu-invoices">Invoices</span>
            </a>

            <a href="{{ route('parent.messages') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('parent.messages') ? 'bg-white/20 text-white shadow-lg' : 'text-indigo-100 hover:bg-white/10 hover:text-white' }}">
                <i class="fas fa-envelope w-6 text-center group-hover:scale-110 transition-transform"></i>
                <span class="font-medium" data-translate="menu-messages">Messages</span>
            </a>

            <a href="{{ route('parent.notifications') }}" class="group flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('parent.notifications') ? 'bg-white/20 text-white shadow-lg' : 'text-indigo-100 hover:bg-white/10 hover:text-white' }}">
                <i class="fas fa-bell w-6 text-center group-hover:scale-110 transition-transform"></i>
                <span class="font-medium" data-translate="menu-notifications">Notifications</span>
            </a>
        </nav>

        <div class="p-4 border-t border-white/10">
            <!-- Language Switcher -->
            <div class="mb-4 flex gap-2">
                <button onclick="switchLanguage('en')" id="lang-btn-en" class="flex-1 py-2 px-3 rounded-lg text-xs font-semibold transition {{ Session::get('parent_language', 'en') == 'en' ? 'bg-white/20 text-white' : 'bg-white/5 text-indigo-200 hover:bg-white/10' }}">
                    EN
                </button>
                <button onclick="switchLanguage('sw')" id="lang-btn-sw" class="flex-1 py-2 px-3 rounded-lg text-xs font-semibold transition {{ Session::get('parent_language', 'en') == 'sw' ? 'bg-white/20 text-white' : 'bg-white/5 text-indigo-200 hover:bg-white/10' }}">
                    SW
                </button>
            </div>

            <div class="bg-white/10 rounded-xl p-4 mb-4 backdrop-blur-sm">
                <p class="text-xs text-indigo-200 mb-1" data-translate="logged-in-as">Logged in as parent of</p>
                <p class="font-bold truncate">{{ Session::get('parent_student_name') }}</p>
            </div>
            
            <form action="{{ route('parent.logout') }}" method="POST">
                @csrf
                <button class="w-full flex items-center justify-center gap-2 bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg transition shadow-md">
                    <i class="fas fa-sign-out-alt"></i> <span data-translate="menu-logout">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-gray-50 md:rounded-l-3xl shadow-2xl relative z-0">
        <!-- Top Bar (Desktop) -->
        <header class="hidden md:flex justify-between items-center p-8 pb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    @yield('title', 'Overview')
                </h2>
                <p class="text-gray-500 text-sm" data-translate="welcome-back">Welcome back to your portal</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800">{{ now()->format('l, F j, Y') }}</p>
                    <p class="text-xs text-gray-500" data-translate="today">Today</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                    <i class="far fa-calendar-alt"></i>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="flex-1 overflow-y-auto px-4 md:px-8 py-6 md:pb-8 pt-20 md:pt-4">
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm" role="alert">
                    <p class="font-bold">Error</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif
            
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm" role="alert">
                    <p class="font-bold">Success</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
        // Translations
        const translations = {
            en: {
                'portal-title': 'Darasa Parent',
                'portal-subtitle': 'Parent Portal',
                'menu-dashboard': 'Dashboard',
                'menu-fees': 'Fee Statements',
                'menu-invoices': 'Invoices',
                'menu-messages': 'Messages',
                'menu-notifications': 'Notifications',
                'menu-logout': 'Logout',
                'logged-in-as': 'Logged in as parent of',
                'welcome-back': 'Welcome back to your portal',
                'today': 'Today'
            },
            sw: {
                'portal-title': 'Darasa Wazazi',
                'portal-subtitle': 'Mlango wa Wazazi',
                'menu-dashboard': 'Dashibodi',
                'menu-fees': 'Taarifa za Ada',
                'menu-invoices': 'Ankara',
                'menu-messages': 'Ujumbe',
                'menu-notifications': 'Arifa',
                'menu-logout': 'Toka',
                'logged-in-as': 'Umeingia kama mzazi wa',
                'welcome-back': 'Karibu tena kwenye mlango wako',
                'today': 'Leo'
            }
        };

        let currentLang = '{{ Session::get("parent_language", "en") }}';

        function switchLanguage(lang) {
            currentLang = lang;
            
            // Update button states
            document.getElementById('lang-btn-en').className = lang === 'en' 
                ? 'flex-1 py-2 px-3 rounded-lg text-xs font-semibold transition bg-white/20 text-white'
                : 'flex-1 py-2 px-3 rounded-lg text-xs font-semibold transition bg-white/5 text-indigo-200 hover:bg-white/10';
            
            document.getElementById('lang-btn-sw').className = lang === 'sw'
                ? 'flex-1 py-2 px-3 rounded-lg text-xs font-semibold transition bg-white/20 text-white'
                : 'flex-1 py-2 px-3 rounded-lg text-xs font-semibold transition bg-white/5 text-indigo-200 hover:bg-white/10';
            
            // Update all translatable elements
            const t = translations[lang];
            document.querySelectorAll('[data-translate]').forEach(el => {
                const key = el.getAttribute('data-translate');
                if (t[key]) {
                    el.textContent = t[key];
                }
            });
            
            // Save to session via AJAX
            axios.post('{{ route("parent.change-language") }}', {
                language: lang
            }, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(() => {
                // Optionally reload page to update server-side translations
                // location.reload();
            });
        }

        // Initialize translations on page load
        document.addEventListener('DOMContentLoaded', function() {
            const t = translations[currentLang];
            document.querySelectorAll('[data-translate]').forEach(el => {
                const key = el.getAttribute('data-translate');
                if (t[key]) {
                    el.textContent = t[key];
                }
            });
        });
    </script>

</body>
</html>

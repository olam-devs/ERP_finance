<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Portal Login - {{ $school->school_name ?? 'School' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .language-btn {
            transition: all 0.3s ease;
        }
        
        .language-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: scale(1.05);
        }
        
        .input-field {
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <!-- Floating Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-10 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-float"></div>
        <div class="absolute top-40 right-10 w-72 h-72 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-float" style="animation-delay: 2s;"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-float" style="animation-delay: 4s;"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <!-- Language Switcher -->
        <div class="flex justify-center gap-3 mb-6">
            <button onclick="switchLanguage('en')" id="lang-en" class="language-btn active px-6 py-2 rounded-full font-semibold shadow-lg">
                <i class="fas fa-globe mr-2"></i>English
            </button>
            <button onclick="switchLanguage('sw')" id="lang-sw" class="language-btn px-6 py-2 rounded-full font-semibold bg-white/30 text-white shadow-lg">
                <i class="fas fa-globe mr-2"></i>Kiswahili
            </button>
        </div>

        <!-- Login Card -->
        <div class="glass-effect rounded-3xl shadow-2xl p-8 md:p-10">
            <!-- Logo/Icon -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full gradient-bg shadow-lg mb-4 animate-float">
                    <i class="fas fa-user-graduate text-white text-3xl"></i>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2" id="title-text">Parent Portal</h1>
                <p class="text-gray-600 text-sm md:text-base" id="subtitle-text">Access your child's academic and financial records</p>
            </div>

            <!-- Error Message -->
            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg animate-pulse">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <p class="text-red-700 font-medium text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('parent.login') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="language" id="selected-language" value="en">

                <!-- Registration Number Field -->
                <div>
                    <label for="student_reg_no" class="block text-sm font-semibold text-gray-700 mb-2" id="label-regno">
                        Student Registration Number
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-id-card text-gray-400 text-lg"></i>
                        </div>
                        <input 
                            id="student_reg_no" 
                            name="student_reg_no" 
                            type="text" 
                            required 
                            value="{{ old('student_reg_no') }}"
                            class="input-field w-full pl-12 pr-4 py-4 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-purple-500 text-gray-800 font-medium text-lg"
                            placeholder="e.g. STD/2024/001"
                            id="input-regno"
                        >
                    </div>
                    <p class="mt-2 text-xs text-gray-500" id="help-text">
                        <i class="fas fa-info-circle mr-1"></i>Enter the student's registration number
                    </p>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full gradient-bg text-white font-bold py-4 px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 text-lg"
                    id="submit-btn"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    <span id="btn-text">Sign In to Portal</span>
                </button>
            </form>

            <!-- Help Section -->
            <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                <p class="text-sm text-gray-600 mb-3" id="help-title">
                    <i class="fas fa-question-circle mr-1"></i>
                    <span id="help-question">Need help?</span>
                </p>
                <p class="text-xs text-gray-500" id="contact-text">
                    Contact school administration
                </p>
                <a href="tel:{{ $school->phone ?? '' }}" class="inline-block mt-2 text-purple-600 font-bold hover:text-purple-800 transition">
                    <i class="fas fa-phone mr-1"></i>{{ $school->phone ?? 'N/A' }}
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-white text-sm">
            <p class="opacity-90" id="footer-text">
                <i class="fas fa-shield-alt mr-1"></i>
                Secure parent portal
            </p>
            <p class="opacity-75 text-xs mt-1" id="powered-by">
                Powered by Olam Technologies
            </p>
        </div>
    </div>

    <script>
        const translations = {
            en: {
                title: 'Parent Portal',
                subtitle: 'Access your child\'s academic and financial records',
                labelRegno: 'Student Registration Number',
                helpText: 'Enter the student\'s registration number',
                btnText: 'Enter Student Registration Number to Proceed',
                helpQuestion: 'Need help?',
                contactText: 'Contact school administration',
                footerText: 'Secure parent portal',
                poweredBy: 'Powered by Olam Technologies'
            },
            sw: {
                title: 'Mlango wa Wazazi',
                subtitle: 'Pata taarifa za elimu na fedha za mwanao',
                labelRegno: 'Nambari ya Usajili wa Mwanafunzi',
                helpText: 'Weka nambari ya usajili wa mwanafunzi',
                btnText: 'Weka Nambari ya Usajili wa Mwanafunzi Kuendelea',
                helpQuestion: 'Unahitaji msaada?',
                contactText: 'Wasiliana na utawala wa shule',
                footerText: 'Mlango salama wa wazazi',
                poweredBy: 'Inatumika na Olam Technologies'
            }
        };

        let currentLang = 'en';

        function switchLanguage(lang) {
            currentLang = lang;
            document.getElementById('selected-language').value = lang;
            
            // Update button states
            document.getElementById('lang-en').classList.toggle('active', lang === 'en');
            document.getElementById('lang-sw').classList.toggle('active', lang === 'sw');
            document.getElementById('lang-en').classList.toggle('bg-white/30', lang !== 'en');
            document.getElementById('lang-sw').classList.toggle('bg-white/30', lang !== 'sw');
            document.getElementById('lang-en').classList.toggle('text-white', lang !== 'en');
            document.getElementById('lang-sw').classList.toggle('text-white', lang !== 'sw');
            
            // Update text content
            const t = translations[lang];
            document.getElementById('title-text').textContent = t.title;
            document.getElementById('subtitle-text').textContent = t.subtitle;
            document.getElementById('label-regno').textContent = t.labelRegno;
            document.getElementById('help-text').innerHTML = '<i class="fas fa-info-circle mr-1"></i>' + t.helpText;
            document.getElementById('btn-text').textContent = t.btnText;
            document.getElementById('help-question').textContent = t.helpQuestion;
            document.getElementById('contact-text').textContent = t.contactText;
            document.getElementById('footer-text').textContent = t.footerText;
            document.getElementById('powered-by').textContent = t.poweredBy;
            
            // Update placeholder
            if (lang === 'sw') {
                document.getElementById('input-regno').placeholder = 'mfano: STD/2024/001';
            } else {
                document.getElementById('input-regno').placeholder = 'e.g. STD/2024/001';
            }
        }

        // Load saved language preference
        const savedLang = localStorage.getItem('parent_language') || 'en';
        if (savedLang === 'sw') {
            switchLanguage('sw');
        }

        // Save language preference on form submit
        document.querySelector('form').addEventListener('submit', function() {
            localStorage.setItem('parent_language', currentLang);
        });
    </script>
</body>
</html>

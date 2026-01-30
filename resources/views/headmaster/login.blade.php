<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Headmaster Login - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Headmaster Portal</h1>
            <p class="text-gray-600 mt-2">School Management Dashboard</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('headmaster.login.post') }}">
            @csrf

            <div class="mb-6">
                <label for="registration_number" class="block text-gray-700 font-medium mb-2">
                    Registration Number
                </label>
                <input type="text" name="registration_number" id="registration_number" 
                    value="{{ old('registration_number') }}" required autofocus
                    placeholder="Enter your registration number"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('registration_number') border-red-500 @enderror">
                @error('registration_number')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-2">Contact your school accountant if you don't have access.</p>
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition-colors duration-200 shadow-lg">
                Login to Dashboard
            </button>
        </form>

        <div class="mt-6 text-center space-y-2">
            <a href="{{ route('parent.login') }}" class="block text-indigo-600 hover:text-indigo-800">
                Parent Portal →
            </a>
            <a href="{{ route('login') }}" class="block text-gray-600 hover:text-gray-800">
                Accountant Login →
            </a>
        </div>
    </div>
</body>
</html>

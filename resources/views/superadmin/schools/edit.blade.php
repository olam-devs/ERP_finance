<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit {{ $school->name }} - Super Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center gap-4">
                   <a href="{{ route('superadmin.dashboard') }}" class="text-2xl font-bold text-indigo-600">Darasa Finance</a>
                    <span class="text-gray-400">|</span>
                    <span class="text-gray-700">Edit {{ $school->name }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('superadmin.dashboard') }}" class="text-gray-600 hover:text-gray-800">Dashboard</a>
                    <a href="{{ route('superadmin.schools.index') }}" class="text-gray-600 hover:text-gray-800">Schools</a>
                    <a href="{{ route('superadmin.schools.show', $school) }}" class="text-gray-600 hover:text-gray-800">Back to Details</a>
                    <a href="{{ route('superadmin.profile') }}" class="text-gray-600 hover:text-indigo-600">{{ auth('superadmin')->user()->name }}</a>
                    <form method="POST" action="{{ route('superadmin.logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit School</h1>

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('superadmin.schools.update', $school) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- School Information -->
                <div class="border-b pb-6">
                    <h2 class="text-xl font-semibold mb-4">School Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">School Name *</label>
                            <input type="text" name="name" value="{{ old('name', $school->name) }}" required
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Slug (Read Only)</label>
                            <input type="text" value="{{ $school->slug }}" disabled
                                class="w-full px-4 py-2 border rounded-lg bg-gray-100">
                            <p class="text-sm text-gray-500 mt-1">Slug cannot be changed after creation</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="border-b pb-6">
                    <h2 class="text-xl font-semibold mb-4">Contact Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Contact Email *</label>
                            <input type="email" name="contact_email" value="{{ old('contact_email', $school->contact_email) }}" required
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Contact Phone</label>
                            <input type="text" name="contact_phone" value="{{ old('contact_phone', $school->contact_phone) }}"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-gray-700 font-medium mb-2">Address</label>
                        <textarea name="address" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('address', $school->address) }}</textarea>
                    </div>

                    <div class="mt-4">
                        <label class="block text-gray-700 font-medium mb-2">Custom Domain (optional)</label>
                        <input type="text" name="domain" value="{{ old('domain', $school->domain) }}"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Subscription Settings -->
                <div class="border-b pb-6">
                    <h2 class="text-xl font-semibold mb-4">Subscription Settings</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Subscription Status *</label>
                            <select name="subscription_status" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="active" {{ old('subscription_status', $school->subscription_status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="trial" {{ old('subscription_status', $school->subscription_status) == 'trial' ? 'selected' : '' }}>Trial</option>
                                <option value="suspended" {{ old('subscription_status', $school->subscription_status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="cancelled" {{ old('subscription_status', $school->subscription_status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Subscription Expires At</label>
                            <input type="date" name="subscription_expires_at" value="{{ old('subscription_expires_at', $school->subscription_expires_at?->format('Y-m-d')) }}"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Max Students</label>
                            <input type="number" name="max_students" value="{{ old('max_students', $school->max_students) }}" min="1"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end gap-4">
                    <a href="{{ route('superadmin.schools.show', $school) }}" class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400">
                        Cancel
                    </a>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700">
                        Update School
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

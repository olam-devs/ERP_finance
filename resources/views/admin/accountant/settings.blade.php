<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>School Settings - Darasa Finance</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Top Header -->
        <nav class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-3 shadow-lg">
            <div class="flex justify-between items-center px-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('accountant.dashboard') }}" class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-xl font-bold">School Settings</h1>
                </div>
                <div class="flex gap-3 items-center">
                    <span class="bg-white bg-opacity-20 px-3 py-1 rounded text-sm">👤 Accountant</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 px-3 py-1.5 rounded transition text-sm">Logout</button>
                    </form>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto p-6">
            @if(session('success'))
            <div class="bg-green-100 border-2 border-green-500 text-green-800 px-4 py-3 rounded mb-4">
                ✅ {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="bg-red-100 border-2 border-red-500 text-red-800 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">⚙️ School Settings</h2>

                <form action="{{ route('accountant.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-bold mb-2">School Name *</label>
                            <input type="text" name="school_name" value="{{ old('school_name', $settings->school_name) }}" required
                                class="w-full border-2 border-gray-300 rounded px-4 py-2 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2">P.O. Box</label>
                            <input type="text" name="po_box" value="{{ old('po_box', $settings->po_box) }}"
                                class="w-full border-2 border-gray-300 rounded px-4 py-2 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2">Region</label>
                            <input type="text" name="region" value="{{ old('region', $settings->region) }}"
                                class="w-full border-2 border-gray-300 rounded px-4 py-2 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $settings->phone) }}"
                                class="w-full border-2 border-gray-300 rounded px-4 py-2 focus:border-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2">Email</label>
                            <input type="email" name="email" value="{{ old('email', $settings->email) }}"
                                class="w-full border-2 border-gray-300 rounded px-4 py-2 focus:border-blue-500 focus:outline-none">
                        </div>

                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold mb-2">School Logo</label>
                        <p class="text-xs text-gray-600 mb-3">Upload a logo (JPEG, PNG, GIF - Max 2MB)</p>

                        @if($settings->logo_path)
                        <div class="mb-4 p-4 bg-gray-50 border-2 border-gray-300 rounded">
                            <p class="text-sm font-semibold mb-2">Current Logo:</p>
                            <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="School Logo" class="max-w-xs max-h-40 border-2 border-gray-300 rounded">
                        </div>
                        @endif

                        <input type="file" name="logo" accept="image/*"
                            class="w-full border-2 border-gray-300 rounded px-4 py-2 focus:border-blue-500 focus:outline-none">
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded font-bold transition">
                            💾 Save Settings
                        </button>
                        <a href="{{ route('accountant.dashboard') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded font-bold transition">
                            ← Back to Dashboard
                        </a>
                    </div>
                </form>

                <!-- Bank Accounts Section -->
                <div class="mt-8 bg-gray-50 rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-800">🏦 Bank Accounts</h3>
                        <button onclick="showAddBankModal()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded transition">
                            + Add Bank Account
                        </button>
                    </div>

                    <div id="bankAccountsTable"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Bank Account Modal -->
    <div id="bankModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 id="bankModalTitle" class="text-xl font-bold mb-4">Add Bank Account</h3>
            <form id="bankForm" onsubmit="saveBankAccount(event)">
                <input type="hidden" id="bank_id">
                <div class="mb-4">
                    <label class="block font-bold mb-2">Bank Name <span class="text-red-500">*</span></label>
                    <input type="text" id="bank_name" required
                           placeholder="e.g., CRDB Bank, NMB Bank"
                           class="w-full border-2 border-gray-300 rounded px-4 py-2 focus:border-blue-500 focus:outline-none">
                </div>
                <div class="mb-4">
                    <label class="block font-bold mb-2">Account Number <span class="text-red-500">*</span></label>
                    <input type="text" id="account_number" required
                           placeholder="e.g., 0150123456789"
                           class="w-full border-2 border-gray-300 rounded px-4 py-2 focus:border-blue-500 focus:outline-none">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded font-bold transition">
                        💾 Save
                    </button>
                    <button type="button" onclick="closeBankModal()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded font-bold transition">
                        ✖ Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;

        document.addEventListener('DOMContentLoaded', function() {
            loadBankAccounts();
        });

        async function loadBankAccounts() {
            try {
                const response = await axios.get('/api/bank-accounts');
                const accounts = response.data.bank_accounts;

                let html = '';
                if (accounts.length === 0) {
                    html = '<p class="text-gray-500 text-center py-4">No bank accounts added yet. Click "Add Bank Account" to add one.</p>';
                } else {
                    html = `
                        <table class="w-full border-2 border-gray-300 rounded-lg">
                            <thead class="bg-blue-100">
                                <tr>
                                    <th class="p-3 text-left">#</th>
                                    <th class="p-3 text-left">Bank Name</th>
                                    <th class="p-3 text-left">Account Number</th>
                                    <th class="p-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                    accounts.forEach((account, index) => {
                        html += `
                            <tr class="border-t hover:bg-gray-50">
                                <td class="p-3 font-bold">${index + 1}</td>
                                <td class="p-3">${account.bank_name}</td>
                                <td class="p-3 font-mono">${account.account_number}</td>
                                <td class="p-3 text-center">
                                    <button onclick='editBankAccount(${JSON.stringify(account)})'
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm mr-2 transition">
                                        Edit
                                    </button>
                                    <button onclick="deleteBankAccount(${account.id})"
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    html += `
                            </tbody>
                        </table>
                    `;
                }

                document.getElementById('bankAccountsTable').innerHTML = html;
            } catch (error) {
                console.error('Error loading bank accounts:', error);
            }
        }

        function showAddBankModal() {
            document.getElementById('bankModalTitle').textContent = 'Add Bank Account';
            document.getElementById('bankForm').reset();
            document.getElementById('bank_id').value = '';
            document.getElementById('bankModal').classList.remove('hidden');
        }

        function editBankAccount(account) {
            document.getElementById('bankModalTitle').textContent = 'Edit Bank Account';
            document.getElementById('bank_id').value = account.id;
            document.getElementById('bank_name').value = account.bank_name;
            document.getElementById('account_number').value = account.account_number;
            document.getElementById('bankModal').classList.remove('hidden');
        }

        function closeBankModal() {
            document.getElementById('bankModal').classList.add('hidden');
            document.getElementById('bankForm').reset();
        }

        async function saveBankAccount(event) {
            event.preventDefault();

            const bankId = document.getElementById('bank_id').value;
            const data = {
                bank_name: document.getElementById('bank_name').value,
                account_number: document.getElementById('account_number').value
            };

            try {
                if (bankId) {
                    // Update existing
                    await axios.put(`/api/bank-accounts/${bankId}`, data);
                } else {
                    // Create new
                    await axios.post('/api/bank-accounts', data);
                }

                closeBankModal();
                loadBankAccounts();
                alert('✅ Bank account saved successfully!');
            } catch (error) {
                alert('❌ Error saving bank account: ' + (error.response?.data?.message || error.message));
            }
        }

        async function deleteBankAccount(id) {
            if (!confirm('Are you sure you want to delete this bank account?')) {
                return;
            }

            try {
                await axios.delete(`/api/bank-accounts/${id}`);
                loadBankAccounts();
                alert('✅ Bank account deleted successfully!');
            } catch (error) {
                alert('❌ Error deleting bank account: ' + (error.response?.data?.message || error.message));
            }
        }
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Books Management - Darasa Finance</title>
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
                    <h1 class="text-2xl font-bold">📚 Books Management</h1>
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
                <h2 class="text-3xl font-bold text-blue-600">📚 Books Management</h2>
                <button onclick="showCreateBookForm()" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg shadow transition">
                    ➕ Create New Book
                </button>
            </div>
            <div id="booksList" class="mt-4"></div>
            <div id="bookFormContainer"></div>
        </div>
    </div>

    <!-- Module Scripts -->
    <script>
        const API_BASE = '/api';
        let allBooks = [];

        // Configure axios
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        axios.defaults.headers.common['Accept'] = 'application/json';
        axios.defaults.withCredentials = true;

        // Load books on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadBooks();
        });

        async function loadBooks() {
            try {
                const response = await axios.get(`${API_BASE}/books`);
                allBooks = response.data;

                let html = '<div class="grid grid-cols-1 md:grid-cols-3 gap-4">';
                allBooks.forEach(book => {
                    html += `
                        <div class="border-2 rounded-lg p-4 ${book.is_cash_book ? 'border-green-500 bg-green-50' : 'border-blue-300 bg-blue-50'}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-lg">${book.name}</h3>
                                    <p class="text-sm text-gray-600">${book.bank_account_number || 'No Account Number'}</p>
                                    <p class="text-xs font-semibold mt-2 ${book.is_cash_book ? 'text-green-600' : 'text-blue-600'}">
                                        ${book.is_cash_book ? '💵 Cash Book' : '🏦 Bank Account'}
                                    </p>
                                </div>
                                ${!book.is_cash_book ? `
                                    <div class="flex gap-2">
                                        <button onclick='showEditBookForm(${JSON.stringify(book)})' class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition">
                                            Edit
                                        </button>
                                        <button onclick="deleteBook(${book.id})" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition">
                                            Delete
                                        </button>
                                    </div>
                                ` : '<span class="text-xs text-green-600 font-bold">Protected</span>'}
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                document.getElementById('booksList').innerHTML = html;
            } catch (error) {
                alert('Error loading books: ' + error.message);
            }
        }

        function showCreateBookForm() {
            const formHtml = `
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-8 max-w-md w-full shadow-2xl">
                        <h3 class="text-2xl font-bold mb-6 text-blue-600">Create New Book</h3>
                        <form onsubmit="createBook(event)" class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold mb-2">Book Name *</label>
                                <input type="text" id="bookName" required
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:outline-none"
                                    placeholder="e.g., NMB 002, CRDB 800">
                                <p class="text-xs text-gray-500 mt-1">Include last 3 digits of account number</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2">Bank Account Number *</label>
                                <input type="text" id="bookAccount" required
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:outline-none"
                                    placeholder="e.g., 1234567002">
                            </div>
                            <div class="flex gap-3 pt-4">
                                <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-bold transition">
                                    💾 Save Book
                                </button>
                                <button type="button" onclick="closeBookForm()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-lg font-bold transition">
                                    ✖️ Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            document.getElementById('bookFormContainer').innerHTML = formHtml;
        }

        function closeBookForm() {
            document.getElementById('bookFormContainer').innerHTML = '';
        }

        async function createBook(event) {
            event.preventDefault();
            const name = document.getElementById('bookName').value;
            const accountNumber = document.getElementById('bookAccount').value;

            try {
                await axios.post(`${API_BASE}/books`, {
                    name: name,
                    bank_account_number: accountNumber
                });
                alert('✅ Book created successfully!');
                closeBookForm();
                loadBooks();
            } catch (error) {
                alert('❌ Error: ' + (error.response?.data?.message || error.message));
            }
        }

        function showEditBookForm(book) {
            const formHtml = `
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-8 max-w-md w-full shadow-2xl">
                        <h3 class="text-2xl font-bold mb-6 text-blue-600">Edit Book</h3>
                        <form onsubmit="updateBook(event, ${book.id})" class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold mb-2">Book Name *</label>
                                <input type="text" id="editBookName" required
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:outline-none"
                                    placeholder="e.g., NMB 002, CRDB 800"
                                    value="${book.name}">
                                <p class="text-xs text-gray-500 mt-1">Include last 3 digits of account number</p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2">Bank Account Number *</label>
                                <input type="text" id="editBookAccount" required
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-blue-500 focus:outline-none"
                                    placeholder="e.g., 1234567002"
                                    value="${book.bank_account_number || ''}">
                            </div>
                            <div class="flex gap-3 pt-4">
                                <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-bold transition">
                                    💾 Update Book
                                </button>
                                <button type="button" onclick="closeBookForm()" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-lg font-bold transition">
                                    ✖️ Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            document.getElementById('bookFormContainer').innerHTML = formHtml;
        }

        async function updateBook(event, id) {
            event.preventDefault();
            const name = document.getElementById('editBookName').value;
            const accountNumber = document.getElementById('editBookAccount').value;

            try {
                await axios.put(`${API_BASE}/books/${id}`, {
                    name: name,
                    bank_account_number: accountNumber
                });
                alert('✅ Book updated successfully!');
                closeBookForm();
                loadBooks();
            } catch (error) {
                alert('❌ Error: ' + (error.response?.data?.message || error.message));
            }
        }

        async function deleteBook(id) {
            if (confirm('⚠️ Are you sure you want to delete this book?')) {
                try {
                    await axios.delete(`${API_BASE}/books/${id}`);
                    alert('✅ Book deleted successfully!');
                    loadBooks();
                } catch (error) {
                    alert('❌ Error: ' + (error.response?.data?.message || error.message));
                }
            }
        }
    </script>
</body>
</html>

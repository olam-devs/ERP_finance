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
                            <div class="flex justify-between items-start mb-3">
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

                            <!-- Deposit/Withdrawal Buttons -->
                            <div class="border-t pt-3 mt-3 flex gap-2 flex-wrap">
                                <button onclick="showDepositForm(${book.id}, '${book.name}')" class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded text-sm font-bold transition flex-1">
                                    ➕ Deposit
                                </button>
                                <button onclick="showWithdrawForm(${book.id}, '${book.name}')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm font-bold transition flex-1">
                                    ➖ Withdraw
                                </button>
                                <button onclick="showTransactionHistory(${book.id}, '${book.name}')" class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-2 rounded text-sm font-bold transition flex-1">
                                    📜 History
                                </button>
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

        // Format amount in Tanzania Shillings
        function formatTSh(amount) {
            return 'TSh ' + parseFloat(amount || 0).toLocaleString('en-TZ', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function showDepositForm(bookId, bookName) {
            const formHtml = `
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-8 max-w-lg w-full shadow-2xl">
                        <h3 class="text-2xl font-bold mb-2 text-green-600">➕ Bank Deposit</h3>
                        <p class="text-gray-600 mb-4">Book: <strong>${bookName}</strong></p>
                        <form onsubmit="submitDeposit(event, ${bookId})" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold mb-2">Amount (TSh) *</label>
                                    <input type="number" id="depositAmount" required step="0.01" min="0.01"
                                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-green-500 focus:outline-none"
                                        placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold mb-2">Date *</label>
                                    <input type="date" id="depositDate" required
                                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-green-500 focus:outline-none"
                                        value="${new Date().toISOString().split('T')[0]}">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2">Reference Number</label>
                                <input type="text" id="depositRef"
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-green-500 focus:outline-none"
                                    placeholder="e.g., Receipt #, Check #">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2">Short Notes (shown in ledger)</label>
                                <input type="text" id="depositShortNotes"
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-green-500 focus:outline-none"
                                    placeholder="e.g., School fundraiser deposit" maxlength="255">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2">Full Details (optional)</label>
                                <textarea id="depositFullDetails" rows="3"
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-green-500 focus:outline-none"
                                    placeholder="Detailed description of the deposit source..."></textarea>
                            </div>
                            <div class="flex gap-3 pt-4">
                                <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-bold transition">
                                    💾 Record Deposit
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

        async function submitDeposit(event, bookId) {
            event.preventDefault();
            try {
                await axios.post(`${API_BASE}/book-transactions/deposit`, {
                    book_id: bookId,
                    amount: parseFloat(document.getElementById('depositAmount').value),
                    transaction_date: document.getElementById('depositDate').value,
                    reference_number: document.getElementById('depositRef').value || null,
                    short_notes: document.getElementById('depositShortNotes').value || null,
                    full_details: document.getElementById('depositFullDetails').value || null
                });
                alert('✅ Deposit recorded successfully!');
                closeBookForm();
            } catch (error) {
                alert('❌ Error: ' + (error.response?.data?.error || error.message));
            }
        }

        function showWithdrawForm(bookId, bookName) {
            const formHtml = `
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-8 max-w-lg w-full shadow-2xl">
                        <h3 class="text-2xl font-bold mb-2 text-red-600">➖ Bank Withdrawal</h3>
                        <p class="text-gray-600 mb-4">Book: <strong>${bookName}</strong></p>
                        <form onsubmit="submitWithdrawal(event, ${bookId})" class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold mb-2">Amount (TSh) *</label>
                                    <input type="number" id="withdrawAmount" required step="0.01" min="0.01"
                                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-red-500 focus:outline-none"
                                        placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold mb-2">Date *</label>
                                    <input type="date" id="withdrawDate" required
                                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-red-500 focus:outline-none"
                                        value="${new Date().toISOString().split('T')[0]}">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2">Reference Number</label>
                                <input type="text" id="withdrawRef"
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-red-500 focus:outline-none"
                                    placeholder="e.g., Cheque #, Transfer ID">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2">Short Notes (shown in ledger)</label>
                                <input type="text" id="withdrawShortNotes"
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-red-500 focus:outline-none"
                                    placeholder="e.g., Cash withdrawal for expenses" maxlength="255">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2">Full Details (optional)</label>
                                <textarea id="withdrawFullDetails" rows="3"
                                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 focus:border-red-500 focus:outline-none"
                                    placeholder="Detailed description of the withdrawal purpose..."></textarea>
                            </div>
                            <div class="flex gap-3 pt-4">
                                <button type="submit" class="flex-1 bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg font-bold transition">
                                    💾 Record Withdrawal
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

        async function submitWithdrawal(event, bookId) {
            event.preventDefault();
            try {
                await axios.post(`${API_BASE}/book-transactions/withdrawal`, {
                    book_id: bookId,
                    amount: parseFloat(document.getElementById('withdrawAmount').value),
                    transaction_date: document.getElementById('withdrawDate').value,
                    reference_number: document.getElementById('withdrawRef').value || null,
                    short_notes: document.getElementById('withdrawShortNotes').value || null,
                    full_details: document.getElementById('withdrawFullDetails').value || null
                });
                alert('✅ Withdrawal recorded successfully!');
                closeBookForm();
            } catch (error) {
                alert('❌ Error: ' + (error.response?.data?.error || error.message));
            }
        }

        async function showTransactionHistory(bookId, bookName) {
            try {
                const response = await axios.get(`${API_BASE}/book-transactions/${bookId}`);
                const data = response.data;

                let html = `
                    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-auto">
                        <div class="bg-white rounded-lg p-8 max-w-4xl w-full shadow-2xl my-8 max-h-[90vh] overflow-y-auto">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h3 class="text-2xl font-bold text-purple-600">📜 Transaction History</h3>
                                    <p class="text-gray-600">Book: <strong>${bookName}</strong></p>
                                </div>
                                <button onclick="closeBookForm()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                            </div>

                            <!-- Summary Cards -->
                            <div class="grid grid-cols-3 gap-4 mb-6">
                                <div class="bg-green-50 border-2 border-green-300 rounded-lg p-4 text-center">
                                    <p class="text-sm text-gray-600">Total Deposits</p>
                                    <p class="text-xl font-bold text-green-600">${formatTSh(data.summary.total_deposits)}</p>
                                </div>
                                <div class="bg-red-50 border-2 border-red-300 rounded-lg p-4 text-center">
                                    <p class="text-sm text-gray-600">Total Withdrawals</p>
                                    <p class="text-xl font-bold text-red-600">${formatTSh(data.summary.total_withdrawals)}</p>
                                </div>
                                <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-4 text-center">
                                    <p class="text-sm text-gray-600">Net Amount</p>
                                    <p class="text-xl font-bold ${data.summary.net_amount >= 0 ? 'text-blue-600' : 'text-red-600'}">${formatTSh(data.summary.net_amount)}</p>
                                </div>
                            </div>

                            <!-- Transaction Table -->
                            <div class="overflow-x-auto">
                                <table class="w-full border-2 border-gray-300 bg-white">
                                    <thead class="bg-purple-100">
                                        <tr>
                                            <th class="p-3 text-left">Date</th>
                                            <th class="p-3 text-left">Type</th>
                                            <th class="p-3 text-right">Amount</th>
                                            <th class="p-3 text-left">Reference</th>
                                            <th class="p-3 text-left">Notes</th>
                                            <th class="p-3 text-left">Details</th>
                                            <th class="p-3 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                `;

                if (data.transactions.data.length === 0) {
                    html += `<tr><td colspan="7" class="p-8 text-center text-gray-500">No transactions found</td></tr>`;
                } else {
                    data.transactions.data.forEach(txn => {
                        const isDeposit = txn.transaction_type === 'deposit';
                        html += `
                            <tr class="border-t hover:bg-purple-50">
                                <td class="p-3">${txn.transaction_date}</td>
                                <td class="p-3">
                                    <span class="px-2 py-1 rounded text-xs font-bold ${isDeposit ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'}">
                                        ${isDeposit ? '➕ Deposit' : '➖ Withdrawal'}
                                    </span>
                                </td>
                                <td class="p-3 text-right font-bold ${isDeposit ? 'text-green-600' : 'text-red-600'}">
                                    ${formatTSh(txn.amount)}
                                </td>
                                <td class="p-3 font-mono text-sm">${txn.reference_number || '-'}</td>
                                <td class="p-3 text-sm">${txn.short_notes || '-'}</td>
                                <td class="p-3 text-xs text-gray-600 max-w-xs truncate">${txn.full_details || '-'}</td>
                                <td class="p-3 text-center">
                                    <button onclick="deleteTransaction(${txn.id}, ${bookId}, '${bookName}')" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }

                html += `
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 text-center">
                                <button onclick="closeBookForm()" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg font-bold transition">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('bookFormContainer').innerHTML = html;
            } catch (error) {
                alert('❌ Error loading history: ' + (error.response?.data?.error || error.message));
            }
        }

        async function deleteTransaction(txnId, bookId, bookName) {
            if (confirm('⚠️ Are you sure you want to delete this transaction? This will also remove the associated ledger entry.')) {
                try {
                    await axios.delete(`${API_BASE}/book-transactions/${txnId}`);
                    alert('✅ Transaction deleted successfully!');
                    showTransactionHistory(bookId, bookName); // Refresh history
                } catch (error) {
                    alert('❌ Error: ' + (error.response?.data?.error || error.message));
                }
            }
        }
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Expense Management - Darasa Finance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">Expense Management</h1>
                    <p class="text-sm text-blue-100">Create and process expense transactions</p>
                </div>
                <a href="{{ route('accountant.dashboard') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition font-semibold">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-yellow-500 text-white rounded-lg shadow-lg p-6">
                <h3 class="text-sm font-semibold opacity-90">Pending Expenses</h3>
                <p class="text-3xl font-bold mt-2" id="pending-total">TSH 0</p>
            </div>
            <div class="bg-green-500 text-white rounded-lg shadow-lg p-6">
                <h3 class="text-sm font-semibold opacity-90">Processed Expenses</h3>
                <p class="text-3xl font-bold mt-2" id="processed-total">TSH 0</p>
            </div>
            <div class="bg-blue-500 text-white rounded-lg shadow-lg p-6">
                <h3 class="text-sm font-semibold opacity-90">Total Expenses</h3>
                <p class="text-3xl font-bold mt-2" id="total-count">0</p>
            </div>
        </div>

        <!-- Create Expense Form -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Create New Expense</h2>
            <form id="expense-form" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Expense Name *</label>
                    <input type="text" id="expense_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Transaction Date *</label>
                    <input type="date" id="transaction_date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Select Book *</label>
                    <select id="book_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Select Book --</option>
                    </select>
                    <p class="text-sm text-gray-600 mt-1">Available Balance: <span id="book-balance" class="font-bold text-green-600">TSH 0</span></p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Amount *</label>
                    <input type="number" id="amount" step="0.01" min="0.01" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea id="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-bold">
                        Create Expense
                    </button>
                </div>
            </form>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Filter Expenses</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <select id="filter-status" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="processed">Processed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select id="filter-book" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Books</option>
                </select>
                <input type="date" id="filter-from" class="px-4 py-2 border border-gray-300 rounded-lg">
                <input type="date" id="filter-to" class="px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <button onclick="loadExpenses()" class="mt-4 bg-gray-700 text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition">
                Apply Filters
            </button>
        </div>

        <!-- Expenses List -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Expense List</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">ID</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Expense Name</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Book</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Amount</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="expenses-table">
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">Loading expenses...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="pagination" class="mt-6"></div>
        </div>
    </div>

    <script>
        let books = [];
        let currentPage = 1;

        document.addEventListener('DOMContentLoaded', function() {
            loadBooks();
            loadExpenses();

            document.getElementById('transaction_date').valueAsDate = new Date();

            document.getElementById('book_id').addEventListener('change', function() {
                const selectedBook = books.find(b => b.id == this.value);
                if (selectedBook) {
                    const balance = parseFloat(selectedBook.opening_balance || 0) +
                                  parseFloat(selectedBook.total_debits || 0) -
                                  parseFloat(selectedBook.total_credits || 0);
                    document.getElementById('book-balance').textContent = 'TSH ' + balance.toLocaleString();
                } else {
                    document.getElementById('book-balance').textContent = 'TSH 0';
                }
            });

            document.getElementById('expense-form').addEventListener('submit', function(e) {
                e.preventDefault();
                createExpense();
            });
        });

        function loadBooks() {
            axios.get('/api/books')
                .then(response => {
                    books = response.data.books.data || response.data.books;
                    const bookSelect = document.getElementById('book_id');
                    const filterBook = document.getElementById('filter-book');

                    bookSelect.innerHTML = '<option value="">-- Select Book --</option>';
                    filterBook.innerHTML = '<option value="">All Books</option>';

                    books.forEach(book => {
                        const option = `<option value="${book.id}">${book.name}</option>`;
                        bookSelect.innerHTML += option;
                        filterBook.innerHTML += option;
                    });
                })
                .catch(error => {
                    console.error('Error loading books:', error);
                });
        }

        function loadExpenses(page = 1) {
            currentPage = page;
            let url = '/api/expenses?page=' + page;

            const status = document.getElementById('filter-status').value;
            const bookId = document.getElementById('filter-book').value;
            const fromDate = document.getElementById('filter-from').value;
            const toDate = document.getElementById('filter-to').value;

            if (status) url += '&status=' + status;
            if (bookId) url += '&book_id=' + bookId;
            if (fromDate) url += '&from_date=' + fromDate;
            if (toDate) url += '&to_date=' + toDate;

            axios.get(url)
                .then(response => {
                    const data = response.data;

                    document.getElementById('pending-total').textContent = 'TSH ' + (data.summary.total_pending || 0).toLocaleString();
                    document.getElementById('processed-total').textContent = 'TSH ' + (data.summary.total_processed || 0).toLocaleString();
                    document.getElementById('total-count').textContent = data.summary.total_count || 0;

                    displayExpenses(data.expenses.data);
                    displayPagination(data.expenses);
                })
                .catch(error => {
                    console.error('Error loading expenses:', error);
                    alert('Error loading expenses');
                });
        }

        function displayExpenses(expenses) {
            const tbody = document.getElementById('expenses-table');

            if (expenses.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No expenses found</td></tr>';
                return;
            }

            tbody.innerHTML = expenses.map(expense => {
                const statusColors = {
                    pending: 'bg-yellow-100 text-yellow-800',
                    processed: 'bg-green-100 text-green-800',
                    cancelled: 'bg-red-100 text-red-800'
                };

                let actions = '';
                if (expense.status === 'pending') {
                    actions = `
                        <button onclick="processExpense(${expense.id})" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 mr-2">
                            Process
                        </button>
                        <button onclick="editExpense(${expense.id})" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 mr-2">
                            Edit
                        </button>
                        <button onclick="cancelExpense(${expense.id})" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                            Cancel
                        </button>
                    `;
                } else {
                    actions = `<span class="text-gray-400 text-sm">No actions</span>`;
                }

                return `
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3">#${expense.id}</td>
                        <td class="px-4 py-3 font-semibold">${expense.expense_name}</td>
                        <td class="px-4 py-3">${new Date(expense.transaction_date).toLocaleDateString()}</td>
                        <td class="px-4 py-3">${expense.book?.name || 'N/A'}</td>
                        <td class="px-4 py-3 font-bold">TSH ${parseFloat(expense.amount).toLocaleString()}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold ${statusColors[expense.status]}">${expense.status.toUpperCase()}</span>
                        </td>
                        <td class="px-4 py-3">${actions}</td>
                    </tr>
                `;
            }).join('');
        }

        function displayPagination(paginationData) {
            const paginationDiv = document.getElementById('pagination');
            if (paginationData.last_page <= 1) {
                paginationDiv.innerHTML = '';
                return;
            }

            let html = '<div class="flex justify-center gap-2">';
            for (let i = 1; i <= paginationData.last_page; i++) {
                const active = i === paginationData.current_page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700';
                html += `<button onclick="loadExpenses(${i})" class="${active} px-4 py-2 rounded hover:bg-blue-500 hover:text-white transition">${i}</button>`;
            }
            html += '</div>';
            paginationDiv.innerHTML = html;
        }

        function createExpense() {
            const data = {
                expense_name: document.getElementById('expense_name').value,
                transaction_date: document.getElementById('transaction_date').value,
                book_id: document.getElementById('book_id').value,
                amount: document.getElementById('amount').value,
                description: document.getElementById('description').value,
            };

            axios.post('/api/expenses', data)
                .then(response => {
                    alert('Expense created successfully!');
                    document.getElementById('expense-form').reset();
                    document.getElementById('transaction_date').valueAsDate = new Date();
                    document.getElementById('book-balance').textContent = 'TSH 0';
                    loadExpenses();
                })
                .catch(error => {
                    console.error('Error creating expense:', error);
                    if (error.response && error.response.data.message) {
                        alert(error.response.data.message);
                    } else {
                        alert('Error creating expense');
                    }
                });
        }

        function processExpense(id) {
            if (!confirm('Are you sure you want to process this expense? This will deduct money from the book.')) {
                return;
            }

            axios.post(`/api/expenses/${id}/process`)
                .then(response => {
                    alert(response.data.message + '\nNew Book Balance: TSH ' + response.data.new_book_balance.toLocaleString());
                    loadExpenses(currentPage);
                })
                .catch(error => {
                    console.error('Error processing expense:', error);
                    if (error.response && error.response.data.message) {
                        alert(error.response.data.message);
                    } else {
                        alert('Error processing expense');
                    }
                });
        }

        function cancelExpense(id) {
            if (!confirm('Are you sure you want to cancel this expense?')) {
                return;
            }

            axios.post(`/api/expenses/${id}/cancel`)
                .then(response => {
                    alert('Expense cancelled successfully!');
                    loadExpenses(currentPage);
                })
                .catch(error => {
                    console.error('Error cancelling expense:', error);
                    alert('Error cancelling expense');
                });
        }

        function editExpense(id) {
            alert('Edit functionality coming soon!');
        }
    </script>
</body>
</html>

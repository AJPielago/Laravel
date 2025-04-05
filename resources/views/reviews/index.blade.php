<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - Lara Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-900">
    <header class="bg-white shadow-md py-4">
        <div class="container mx-auto flex justify-between items-center px-6">
            <h1 class="text-2xl font-bold text-indigo-600">Lara Shop</h1>
            <nav class="space-x-4">
                <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-indigo-600">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-indigo-600 hover:text-indigo-800 font-semibold">Logout</button>
                </form>
            </nav>
        </div>
    </header>

    <main class="min-h-[calc(100vh-8rem)] bg-gradient-to-r from-indigo-500 to-purple-500 py-12">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg p-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-indigo-600">Product Reviews</h1>
                        <p class="mt-2 text-gray-600">Manage all product reviews</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table id="reviewsTable" class="w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Product</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white text-gray-700 text-center py-6 border-t border-gray-200">
        <p>&copy; 2025 Lara Shop. All rights reserved.</p>
    </footer>

    <script>
        $(document).ready(function() {
            $('#reviewsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("reviews.data") }}',
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'user_name', name: 'user_name'},
                    {data: 'product_name', name: 'product_name'},
                    {data: 'rating', name: 'rating'},
                    {data: 'comment', name: 'comment'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false}
                ]
            });
        });

        function deleteReview(id) {
            if (confirm('Are you sure you want to delete this review?')) {
                $.ajax({
                    url: '/reviews/' + id,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function() {
                        $('#reviewsTable').DataTable().ajax.reload();
                    }
                });
            }
        }
    </script>
</body>
</html>

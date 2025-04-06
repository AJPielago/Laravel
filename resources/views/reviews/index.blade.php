@extends('layouts.app')

@section('title', 'Reviews')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                @if(auth()->user()->isAdmin())
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">Reviews Management</h1>
                            <p class="mt-2 text-gray-600 dark:text-gray-300">Manage and review customer feedback</p>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table id="reviews-table" class="w-full stripe hover">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700 border-b">
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">ID</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Product</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">User</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Rating</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Comment</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Date</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @else
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">My Reviews</h1>
                            <p class="mt-2 text-gray-600 dark:text-gray-300">Your product review history</p>
                        </div>
                    </div>

                    @if($reviews->isEmpty())
                        <div class="text-center py-8">
                            <div class="text-gray-500 dark:text-gray-400 text-lg mb-4">You haven't written any reviews yet.</div>
                            <a href="{{ route('shop.index') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 transition duration-300">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Browse Products
                            </a>
                        </div>
                    @else
                        <div class="grid gap-6">
                            @foreach($reviews as $review)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 shadow-md hover:shadow-lg transition duration-300">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h3 class="font-semibold text-xl text-gray-800 dark:text-gray-200">{{ $review->product->name }}</h3>
                                            <div class="flex text-yellow-400 my-2">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <span class="text-2xl">{{ $i <= $review->rating ? '★' : '☆' }}</span>
                                                @endfor
                                            </div>
                                        </div>
                                        <span class="text-gray-500 dark:text-gray-400 text-sm">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-gray-600 dark:text-gray-300">{{ $review->comment }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(auth()->user()->isAdmin())
<script>
    $(document).ready(function() {
        $('#reviews-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('reviews.data') }}",
                type: "GET"
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'product_name', name: 'product.name' },
                { data: 'user_name', name: 'user.name' },
                { data: 'rating', name: 'rating' },
                { data: 'comment', name: 'comment' },
                { data: 'created_at', name: 'created_at' },
                { 
                    data: 'actions', 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false 
                }
            ],
            responsive: true,
            language: {
                processing: '<div class="flex justify-center items-center"><div class="spinner-border text-indigo-600" role="status"><span class="sr-only">Loading...</span></div></div>'
            }
        });
    });

    function deleteReview(reviewId) {
        Swal.fire({
            title: 'Delete Review',
            text: 'Are you sure you want to delete this review?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/reviews/${reviewId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Deleted!', 
                            text: 'Review has been deleted.', 
                            icon: 'success',
                            timer: 3000,
                            timerProgressBar: true
                        });
                        $('#reviews-table').DataTable().ajax.reload();
                    } else {
                        Swal.fire({
                            title: 'Error', 
                            text: data.message, 
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error', 
                        text: 'Something went wrong', 
                        icon: 'error'
                    });
                });
            }
        });
    }
</script>
@endif
@endpush

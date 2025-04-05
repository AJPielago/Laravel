@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-6">Reviews Management</h1>
        
        <table id="reviews-table" class="w-full stripe hover">
            <thead>
                <tr>
                    <th id="col-id">ID</th>
                    <th id="col-product">Product</th>
                    <th id="col-user">User</th>
                    <th id="col-rating">Rating</th>
                    <th id="col-comment">Comment</th>
                    <th id="col-date">Date</th>
                    <th id="col-actions">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@push('scripts')
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
                {data: 'id', name: 'id'},
                {data: 'product_name', name: 'product.name'},
                {data: 'user_name', name: 'user.name'},
                {data: 'rating', name: 'rating'},
                {data: 'comment', name: 'comment'},
                {data: 'created_at', name: 'created_at'},
                {data: 'actions', name: 'actions', orderable: false, searchable: false}
            ]
        });
    });

    function deleteReview(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/reviews/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', 'Review has been deleted.', 'success');
                        $('#reviews-table').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'Failed to delete review.', 'error');
                    }
                });
            }
        });
    }
</script>
@endpush
@endsection

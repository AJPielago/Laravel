<div id="reviewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg w-full max-w-md p-6">
            <h2 id="reviewModalTitle" class="text-2xl font-bold mb-4">Write a Review</h2>
            <form id="reviewForm" method="POST">
                @csrf
                <input type="hidden" name="review_id" id="review_id">
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="order_id" id="order_id">
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Rating</label>
                    <div class="flex space-x-2" id="ratingStars">
                        @for($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer">
                                <input type="radio" name="rating" value="{{ $i }}" class="hidden" onchange="updateStars({{ $i }})">
                                <span class="text-2xl text-gray-300 hover:text-yellow-400 transition-colors" onclick="setRating({{ $i }})">★</span>
                            </label>
                        @endfor
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Review</label>
                    <textarea id="comment" 
                              name="comment" 
                              rows="4" 
                              class="w-full border rounded p-2 focus:ring-2 focus:ring-indigo-200" 
                              placeholder="Share your experience..."></textarea>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" 
                            onclick="closeReviewModal()" 
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setRating(rating) {
    const stars = document.querySelectorAll('#ratingStars span');
    const input = document.querySelector(`input[name="rating"][value="${rating}"]`);
    
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('text-yellow-400');
            star.classList.remove('text-gray-300');
        } else {
            star.classList.add('text-gray-300');
            star.classList.remove('text-yellow-400');
        }
    });
    
    if (input) {
        input.checked = true;
    }
}

function updateStars(rating) {
    setRating(rating);
}

function openReviewModal(orderId, rating = 0, comment = '', reviewId = null) {
    const form = document.getElementById('reviewForm');
    const modalTitle = document.getElementById('reviewModalTitle');
    const submitBtn = form.querySelector('button[type="submit"]');
    const commentTextarea = form.querySelector('#comment');
    
    // Set form values
    form.querySelector('#order_id').value = orderId;
    form.querySelector('#review_id').value = reviewId;
    commentTextarea.value = comment;
    
    // Set rating if editing
    if (rating > 0) {
        setRating(rating);
        const ratingInput = form.querySelector(`input[name="rating"][value="${rating}"]`);
        if (ratingInput) ratingInput.checked = true;
    }
    
    // Set form mode
    const isEdit = reviewId !== null;
    form.setAttribute('data-mode', isEdit ? 'edit' : 'create');
    
    modalTitle.textContent = isEdit ? 'Edit Your Review' : 'Write a Review';
    submitBtn.textContent = isEdit ? 'Update Review' : 'Submit Review';
    
    document.getElementById('reviewModal').classList.remove('hidden');
}

document.getElementById('reviewForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const isEdit = this.getAttribute('data-mode') === 'edit';
    const productId = formData.get('product_id');
    
    submitButton.disabled = true;
    
    try {
        const url = `/product/${productId}/reviews`;
        const response = await fetch(url, {
            method: isEdit ? 'PUT' : 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: isEdit ? 'Review updated successfully!' : 'Review submitted successfully!',
                showConfirmButton: false,
                timer: 2000
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: result.message || 'Failed to process review',
                confirmButtonText: 'OK'
            });
        }
    } catch (error) {
        console.error('Review submission error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An unexpected error occurred. Please try again.',
        });
    } finally {
        submitButton.disabled = false;
    }
});
</script>

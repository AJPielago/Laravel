<div id="reviewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg w-full max-w-md p-6">
            <h2 class="text-2xl font-bold mb-4">Write a Review</h2>
            <form id="reviewForm" action="{{ route('reviews.store', ['product' => $product]) }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="order_id" id="order_id">
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Rating</label>
                    <div class="flex space-x-2">
                        @for ($i = 1; $i <= 5; $i++)
                            <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" class="hidden peer" required>
                            <label for="star{{ $i }}" class="cursor-pointer text-2xl text-gray-300 peer-checked:text-yellow-400">★</label>
                        @endfor
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Comment</label>
                    <textarea name="comment" rows="4" class="w-full border rounded p-2" required></textarea>
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeReviewModal()" class="px-4 py-2 bg-gray-200 rounded">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

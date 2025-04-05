<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use Searchable, HasFactory;

    protected $fillable = [
        'name', 
        'price', 
        'stock', 
        'photos',
        'description',
        'is_deleted',
        'category',
        'searchable_text'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_deleted' => 'boolean'
    ];

    /**
     * Get photos array
     */
    public function getPhotosAttribute($value)
    {
        if (empty($value)) {
            return [];
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Mutator to ensure photos are always stored as a JSON string
     * 
     * @param mixed $value
     * @return void
     */
    public function setPhotosAttribute($value)
    {
        // Log the input value
        Log::info('Setting Photos Attribute', [
            'input_value' => $value,
            'input_type' => gettype($value)
        ]);

        // If it's already a JSON string, use it directly
        if (is_string($value)) {
            $this->attributes['photos'] = $value;
            return;
        }
        
        // If it's an array, convert to JSON
        if (is_array($value)) {
            $this->attributes['photos'] = json_encode($value);
            return;
        }
        
        // Default to an empty array
        $this->attributes['photos'] = '[]';

        // Log the final stored value
        Log::info('Photos Attribute Set', [
            'stored_value' => $this->attributes['photos']
        ]);
    }

    /**
     * Get the first photo URL for the product
     * 
     * @return string|null
     */
    public function getFirstPhotoAttribute()
    {
        // Check if photos attribute exists
        if (!isset($this->attributes['photos'])) {
            Log::warning('Photos attribute not set', [
                'product_id' => $this->id,
                'product_attributes' => $this->attributes
            ]);
            return null;
        }

        // Get the raw photos value
        $rawPhotos = $this->attributes['photos'];

        // Log the raw photos value
        Log::info('Raw Photos Debug', [
            'product_id' => $this->id,
            'raw_photos' => $rawPhotos,
            'raw_photos_type' => gettype($rawPhotos)
        ]);

        // Try to decode if it's a string
        $photos = is_string($rawPhotos) 
            ? json_decode($rawPhotos, true) 
            : $rawPhotos;

        // Log the decoded photos
        Log::info('Decoded Photos Debug', [
            'product_id' => $this->id,
            'decoded_photos' => $photos,
            'decoded_photos_type' => gettype($photos)
        ]);

        // Return the first photo or null
        $firstPhoto = is_array($photos) && !empty($photos) ? $photos[0] : null;

        Log::info('First Photo Result', [
            'product_id' => $this->id,
            'first_photo' => $firstPhoto
        ]);

        return $firstPhoto;
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'price' => $this->price,
            'searchable_text' => $this->generateSearchableText()
        ];
    }

    /**
     * Generate a combined searchable text field
     * 
     * @return string
     */
    public function generateSearchableText()
    {
        return implode(' ', [
            $this->name,
            $this->description,
            $this->category,
            $this->price
        ]);
    }

    // Model Search Method (10 pts)
    public static function search($searchTerm)
    {
        return self::where(function($query) use ($searchTerm) {
            $query->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('category', 'LIKE', "%{$searchTerm}%");
        })->paginate(12);
    }
}

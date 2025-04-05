<?php
namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    private $rowCount = 0;

    public function model(array $row)
    {
        $this->rowCount++;
        
        return new Product([
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'price' => $this->parsePrice($row['price']),
            'stock' => (int)($row['stock'] ?? 0),
            'category_id' => $row['category_id'] ?? null,
            'photos' => json_encode($this->parsePhotos($row['photos'] ?? '')),
            'is_deleted' => false,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
        ];
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    protected function parsePrice($value): float
    {
        return is_numeric($value) ? (float)$value : 0;
    }

    protected function parsePhotos($value): array
    {
        if (empty($value)) return [];
        return array_map('trim', explode(',', $value));
    }
}
<?php

namespace App\Http\Controllers;

use App\Imports\ProductsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class ProductImportController extends Controller
{
    public function showImportForm()
    {
        // Debug output - should show "admin"
        Log::info('Current user role', ['role' => auth()->user()->role]);
        
        // Log the access
        Log::channel('imports')->info('Import form accessed', [
            'user' => auth()->user()->only(['id', 'email', 'role'])
        ]);
        
        // Return the view with any necessary data
        return view('products.import');
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ], [
            'excel_file.required' => 'Please select a file to import.',
            'excel_file.mimes' => 'The file must be an Excel (.xlsx, .xls) or CSV file.',
            'excel_file.max' => 'The file may not be greater than 2MB.'
        ]);

        try {
            $import = new ProductsImport();
            Excel::import($import, $request->file('excel_file'));

            $importedCount = $import->getRowCount();

            Log::channel('imports')->info('Products imported successfully', [
                'count' => $importedCount,
                'user_id' => auth()->id()
            ]);

            return redirect()
                ->route('products.index')
                ->with('success', "Successfully imported {$importedCount} products!");

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $errors = collect($e->failures())->map(function($failure) {
                return "Row {$failure->row()}: {$failure->attribute()} - " . implode(', ', $failure->errors());
            });

            Log::channel('imports')->error('Import validation failed', [
                'errors' => $errors,
                'user_id' => auth()->id()
            ]);

            return back()
                ->withErrors($errors)
                ->with('error', 'Some rows failed validation. Please check the errors below.');

        } catch (\Exception $e) {
            Log::channel('imports')->error('Import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
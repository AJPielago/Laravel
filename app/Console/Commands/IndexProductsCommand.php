<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class IndexProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index all products for Scout search';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Indexing all products...');

        // Import all non-deleted products
        Product::where('is_deleted', false)->searchable();

        $this->info('All products have been indexed successfully!');
    }
}

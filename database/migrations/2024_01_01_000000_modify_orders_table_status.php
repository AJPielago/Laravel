<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First check if orders table exists
        if (!Schema::hasTable('orders')) {
            return;
        }
        
        Schema::table('orders', function (Blueprint $table) {
            // Check if status column exists before modifying
            if (Schema::hasColumn('orders', 'status')) {
                $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending')->change();
            }
        });
    }

    public function down()
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'status')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('status')->change();
            });
        }
    }
};

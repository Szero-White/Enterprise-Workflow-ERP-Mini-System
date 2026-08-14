<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('item_categories')->nullOnDelete();
            $table->string('sku', 80)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit', 30)->default('cái');
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('reorder_level', 15, 3)->default(0);
            $table->boolean('is_asset_trackable')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['category_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};

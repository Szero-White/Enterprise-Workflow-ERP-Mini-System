<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_request_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->string('item_sku', 80);
            $table->string('item_name');
            $table->string('unit', 30);
            $table->decimal('requested_quantity', 15, 3);
            $table->decimal('estimated_unit_cost', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['purchase_request_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
    }
};

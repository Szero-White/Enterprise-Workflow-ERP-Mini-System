<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_request_item_id')
                ->constrained('purchase_request_items')
                ->restrictOnDelete();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->string('item_sku', 80);
            $table->string('item_name');
            $table->string('unit', 30);
            $table->decimal('ordered_quantity', 15, 3);
            $table->decimal('received_quantity', 15, 3)->default(0);
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('line_total', 15, 2);
            $table->timestamps();

            $table->unique(['purchase_order_id', 'purchase_request_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};

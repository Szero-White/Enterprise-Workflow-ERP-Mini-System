<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('type', 40)->index();
            $table->decimal('quantity', 15, 3);
            $table->decimal('balance_after', 15, 3);
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->nullableMorphs('reference');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['warehouse_id', 'product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};

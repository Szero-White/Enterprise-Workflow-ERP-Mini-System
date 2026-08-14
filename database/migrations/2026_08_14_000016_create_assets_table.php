<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table): void {
            $table->id();
            $table->string('asset_code', 40)->unique();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->foreignId('goods_receipt_item_id')->nullable()->constrained('goods_receipt_items')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('serial_number', 120)->nullable()->unique();
            $table->date('acquired_at');
            $table->decimal('acquisition_cost', 15, 2)->default(0);
            $table->string('status', 30)->index();
            $table->string('condition', 30)->index();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['item_id', 'warehouse_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table): void {
            $table->id();
            $table->string('po_number', 40)->unique();
            $table->foreignId('purchase_request_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('status', 40)->default('draft')->index();
            $table->timestamp('ordered_at')->nullable();
            $table->date('expected_date')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['purchase_request_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};

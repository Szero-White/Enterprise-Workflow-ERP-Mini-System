<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workflow_request_id')
                ->unique()
                ->constrained('requests')
                ->cascadeOnDelete();
            $table->text('purpose');
            $table->date('required_date')->nullable();
            $table->decimal('estimated_total', 15, 2)->default(0);
            $table->char('currency', 3)->default('VND');
            $table->string('status', 40)->default('pending_approval')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};

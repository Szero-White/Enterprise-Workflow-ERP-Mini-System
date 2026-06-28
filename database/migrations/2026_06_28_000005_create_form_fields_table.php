<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_template_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('field_key');
            $table->enum('field_type', ['text', 'textarea', 'number', 'date', 'select', 'file']);
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['form_template_id', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};

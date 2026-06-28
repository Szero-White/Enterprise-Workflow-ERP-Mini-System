<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignId('form_field_id')->constrained('form_fields')->restrictOnDelete();
            $table->string('field_key');
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->unique(['request_id', 'form_field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_values');
    }
};

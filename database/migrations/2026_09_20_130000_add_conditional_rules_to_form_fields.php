<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->string('condition_field_key')->nullable()->after('is_required');
            $table->string('condition_operator', 32)->nullable()->after('condition_field_key');
            $table->string('condition_value')->nullable()->after('condition_operator');
        });
    }

    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropColumn([
                'condition_field_key',
                'condition_operator',
                'condition_value',
            ]);
        });
    }
};

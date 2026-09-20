<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LEGACY_TYPES = ['text', 'textarea', 'number', 'date', 'select', 'file'];

    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildSqliteTable(useLegacyEnum: false);

            return;
        }

        Schema::table('form_fields', function (Blueprint $table): void {
            $table->string('field_type', 32)->change();
        });
    }

    public function down(): void
    {
        $unsupportedTypeExists = DB::table('form_fields')
            ->whereNotIn('field_type', self::LEGACY_TYPES)
            ->exists();

        if ($unsupportedTypeExists) {
            throw new RuntimeException('Cannot roll back form field types while newer field types are still in use.');
        }

        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildSqliteTable(useLegacyEnum: true);

            return;
        }

        Schema::table('form_fields', function (Blueprint $table): void {
            $table->enum('field_type', self::LEGACY_TYPES)->change();
        });
    }

    private function rebuildSqliteTable(bool $useLegacyEnum): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        try {
            Schema::dropIfExists('form_fields_field_type_upgrade');

            Schema::create('form_fields_field_type_upgrade', function (Blueprint $table) use ($useLegacyEnum): void {
                $table->id();
                $table->foreignId('form_template_id')->constrained()->cascadeOnDelete();
                $table->string('label');
                $table->string('field_key');

                if ($useLegacyEnum) {
                    $table->enum('field_type', self::LEGACY_TYPES);
                } else {
                    $table->string('field_type', 32);
                }

                $table->boolean('is_required')->default(false);
                $table->string('condition_field_key')->nullable();
                $table->string('condition_operator', 32)->nullable();
                $table->string('condition_value')->nullable();
                $table->json('options')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['form_template_id', 'field_key']);
            });

            DB::table('form_fields_field_type_upgrade')->insertUsing(
                [
                    'id',
                    'form_template_id',
                    'label',
                    'field_key',
                    'field_type',
                    'is_required',
                    'condition_field_key',
                    'condition_operator',
                    'condition_value',
                    'options',
                    'sort_order',
                    'created_at',
                    'updated_at',
                ],
                DB::table('form_fields')->select([
                    'id',
                    'form_template_id',
                    'label',
                    'field_key',
                    'field_type',
                    'is_required',
                    'condition_field_key',
                    'condition_operator',
                    'condition_value',
                    'options',
                    'sort_order',
                    'created_at',
                    'updated_at',
                ])
            );

            Schema::drop('form_fields');
            Schema::rename('form_fields_field_type_upgrade', 'form_fields');
        } finally {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table): void {
            $table->string('fulfillment_route', 30)->default('pending')->index()->after('status');
        });

        Schema::table('assets', function (Blueprint $table): void {
            $table->foreignId('reserved_for_purchase_request_item_id')
                ->nullable()
                ->after('goods_receipt_item_id')
                ->constrained('purchase_request_items')
                ->nullOnDelete();
        });

        Schema::table('asset_assignments', function (Blueprint $table): void {
            $table->foreignId('purchase_request_item_id')
                ->nullable()
                ->after('asset_id')
                ->constrained('purchase_request_items')
                ->nullOnDelete();
        });

        DB::table('purchase_requests')
            ->whereIn('status', ['approved', 'ordered', 'closed'])
            ->update(['fulfillment_route' => 'procurement']);
    }

    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('purchase_request_item_id');
        });

        Schema::table('assets', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('reserved_for_purchase_request_item_id');
        });

        Schema::table('purchase_requests', function (Blueprint $table): void {
            $table->dropColumn('fulfillment_route');
        });
    }
};

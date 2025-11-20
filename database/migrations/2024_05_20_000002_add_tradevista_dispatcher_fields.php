<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_boys', function (Blueprint $table) {
            $table->string('kyc_document_type')->nullable();
            $table->string('kyc_document_number')->nullable();
            $table->unsignedBigInteger('kyc_document_upload_id')->nullable();
            $table->string('license_number')->nullable();
            $table->json('service_area_cities')->nullable();
            $table->decimal('default_rate', 10, 2)->nullable();
            $table->unsignedInteger('default_eta_hours')->nullable();
            $table->timestamp('kyc_submitted_at')->nullable();
            $table->timestamp('kyc_reviewed_at')->nullable();
            $table->timestamp('tradevista_approved_at')->nullable();
            $table->string('kyc_status')->default('draft');
            $table->text('kyc_rejection_reason')->nullable();
        });

        Schema::table('carts', function (Blueprint $table) {
            if (!Schema::hasColumn('carts', 'dispatcher_id')) {
                $table->unsignedBigInteger('dispatcher_id')->nullable()->after('carrier_id');
            }
        });

        Schema::table('order_details', function (Blueprint $table) {
            if (!Schema::hasColumn('order_details', 'dispatcher_id')) {
                $table->unsignedBigInteger('dispatcher_id')->nullable()->after('carrier_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('delivery_boys', function (Blueprint $table) {
            $table->dropColumn([
                'kyc_document_type',
                'kyc_document_number',
                'kyc_document_upload_id',
                'license_number',
                'service_area_cities',
                'default_rate',
                'default_eta_hours',
                'kyc_submitted_at',
                'kyc_reviewed_at',
                'tradevista_approved_at',
                'kyc_status',
                'kyc_rejection_reason',
            ]);
        });

        Schema::table('carts', function (Blueprint $table) {
            if (Schema::hasColumn('carts', 'dispatcher_id')) {
                $table->dropColumn('dispatcher_id');
            }
        });

        Schema::table('order_details', function (Blueprint $table) {
            if (Schema::hasColumn('order_details', 'dispatcher_id')) {
                $table->dropColumn('dispatcher_id');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            if (!Schema::hasColumn('disputes', 'submitted_by')) {
                $table->unsignedBigInteger('submitted_by')->nullable()->after('order_id');
            }
            if (!Schema::hasColumn('disputes', 'reason')) {
                $table->string('reason')->nullable()->after('submitted_by');
            }
            if (!Schema::hasColumn('disputes', 'description')) {
                $table->text('description')->nullable()->after('reason');
            }
            if (!Schema::hasColumn('disputes', 'decision_notes')) {
                $table->text('decision_notes')->nullable()->after('resolution');
            }
            if (!Schema::hasColumn('disputes', 'decision_amount')) {
                $table->decimal('decision_amount', 20, 2)->nullable()->after('decision_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            foreach (['decision_amount', 'decision_notes', 'description', 'reason', 'submitted_by'] as $column) {
                if (Schema::hasColumn('disputes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

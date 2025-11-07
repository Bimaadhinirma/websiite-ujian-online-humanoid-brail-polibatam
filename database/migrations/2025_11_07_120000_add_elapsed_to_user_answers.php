<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_answers', function (Blueprint $table) {
            if (!Schema::hasColumn('user_answers', 'elapsed_seconds')) {
                $table->integer('elapsed_seconds')->nullable()->after('status')->comment('Total elapsed seconds spent by participant');
            }
            if (!Schema::hasColumn('user_answers', 'ended_at')) {
                $table->timestamp('ended_at')->nullable()->after('elapsed_seconds');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_answers', function (Blueprint $table) {
            if (Schema::hasColumn('user_answers', 'ended_at')) {
                $table->dropColumn('ended_at');
            }
            if (Schema::hasColumn('user_answers', 'elapsed_seconds')) {
                $table->dropColumn('elapsed_seconds');
            }
        });
    }
};

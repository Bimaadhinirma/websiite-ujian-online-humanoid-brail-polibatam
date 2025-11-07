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
            if (!Schema::hasColumn('user_answers', 'category_order')) {
                $table->json('category_order')->nullable()->after('status');
            }
            if (!Schema::hasColumn('user_answers', 'question_order')) {
                $table->json('question_order')->nullable()->after('category_order');
            }
            if (!Schema::hasColumn('user_answers', 'options_order')) {
                $table->json('options_order')->nullable()->after('question_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_answers', function (Blueprint $table) {
            if (Schema::hasColumn('user_answers', 'options_order')) {
                $table->dropColumn('options_order');
            }
            if (Schema::hasColumn('user_answers', 'question_order')) {
                $table->dropColumn('question_order');
            }
            if (Schema::hasColumn('user_answers', 'category_order')) {
                $table->dropColumn('category_order');
            }
        });
    }
};

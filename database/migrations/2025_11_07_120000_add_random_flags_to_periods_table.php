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
        Schema::table('periods', function (Blueprint $table) {
            $table->boolean('is_random_questions')->default(false)->after('exam_password');
            $table->boolean('is_random_options')->default(false)->after('is_random_questions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periods', function (Blueprint $table) {
            $table->dropColumn(['is_random_questions', 'is_random_options']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStartIpAndHeartbeatToUserAnswers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_answers', function (Blueprint $table) {
            if (!Schema::hasColumn('user_answers', 'start_ip')) {
                $table->string('start_ip')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('user_answers', 'last_heartbeat_at')) {
                $table->timestamp('last_heartbeat_at')->nullable()->after('start_ip');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_answers', function (Blueprint $table) {
            if (Schema::hasColumn('user_answers', 'last_heartbeat_at')) {
                $table->dropColumn('last_heartbeat_at');
            }
            if (Schema::hasColumn('user_answers', 'start_ip')) {
                $table->dropColumn('start_ip');
            }
        });
    }
}

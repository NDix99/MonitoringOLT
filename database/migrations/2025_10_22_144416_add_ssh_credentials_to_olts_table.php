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
        Schema::table('olts', function (Blueprint $table) {
            $table->string('ssh_username')->nullable()->after('community_string');
            $table->string('ssh_password')->nullable()->after('ssh_username');
            $table->integer('ssh_port')->default(22)->after('ssh_password');
            $table->boolean('ssh_enabled')->default(false)->after('ssh_port');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('olts', function (Blueprint $table) {
            $table->dropColumn(['ssh_username', 'ssh_password', 'ssh_port', 'ssh_enabled']);
        });
    }
};

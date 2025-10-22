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
            $table->string('version')->nullable()->after('ssh_enabled');
            $table->decimal('temperature', 5, 2)->nullable()->after('version');
            $table->integer('fan_speed')->nullable()->after('temperature');
            $table->bigInteger('uptime_seconds')->nullable()->after('fan_speed');
            $table->timestamp('last_system_check')->nullable()->after('uptime_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('olts', function (Blueprint $table) {
            $table->dropColumn(['version', 'temperature', 'fan_speed', 'uptime_seconds', 'last_system_check']);
        });
    }
};

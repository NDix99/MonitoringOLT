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
        Schema::create('onus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained()->onDelete('cascade');
            $table->string('onu_index');
            $table->string('serial_number');
            $table->integer('status_code')->default(0); // 3=Working, 6=Offline, etc.
            $table->string('status_text')->default('Unknown');
            $table->decimal('rx_power', 8, 2)->nullable(); // dBm
            $table->decimal('tx_power', 8, 2)->nullable(); // dBm
            $table->string('model')->nullable();
            $table->string('vendor')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->timestamps();
            
            $table->unique(['olt_id', 'onu_index']);
            $table->index(['olt_id', 'status_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onus');
    }
};

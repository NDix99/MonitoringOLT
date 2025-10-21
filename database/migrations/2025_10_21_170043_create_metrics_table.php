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
        Schema::create('metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onu_id')->constrained()->onDelete('cascade');
            $table->string('metric_type'); // rx_power, tx_power, status, etc.
            $table->decimal('value', 10, 4)->nullable();
            $table->string('unit')->nullable(); // dBm, etc.
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            $table->index(['onu_id', 'metric_type', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metrics');
    }
};

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
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->float('cpu_usage');
            $table->float('memory_usage');
            $table->unsignedBigInteger('memory_used');
            $table->unsignedBigInteger('memory_total');
            $table->float('disk_usage');
            $table->unsignedBigInteger('disk_used');
            $table->unsignedBigInteger('disk_total');
            $table->float('network_in');
            $table->float('network_out');
            $table->float('request_rate');
            $table->float('response_time');
            $table->timestamps();
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

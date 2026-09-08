<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->string('previous_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->text('reason')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['unit_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_status_histories');
    }
};

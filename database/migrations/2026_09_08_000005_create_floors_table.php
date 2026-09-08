<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wing_id')->constrained('wings')->onDelete('cascade');
            $table->integer('floor_number')->default(1);
            $table->string('label', 100)->nullable(); // e.g. "1st Floor", "Penthouse Level"
            $table->string('status', 50)->default('Active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['wing_id', 'floor_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floors');
    }
};

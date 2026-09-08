<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('buildings')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->integer('number_of_floors')->default(1);
            $table->string('status', 50)->default('Active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['building_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wings');
    }
};

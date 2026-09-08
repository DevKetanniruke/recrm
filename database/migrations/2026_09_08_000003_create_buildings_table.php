<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->integer('number_of_floors')->default(1);
            $table->string('status', 50)->default('Under Construction'); // Under Construction, Completed, Planning, On Hold
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};

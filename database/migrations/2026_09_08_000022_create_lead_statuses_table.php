<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->string('color_code', 10)->default('#6c757d');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_won')->default(false);
            $table->boolean('is_lost')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_statuses');
    }
};

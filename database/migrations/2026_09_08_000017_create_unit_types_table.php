<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->string('name'); // e.g. "2 BHK Premium", "Executive Penthouse"
            $table->string('code', 50)->nullable();
            $table->string('category', 50)->default('2 BHK'); // 1 BHK, 2 BHK, 3 BHK, Studio, Shop, Office, Plot, Villa, Custom
            $table->decimal('default_carpet_area', 10, 2)->default(0);
            $table->json('attributes')->nullable(); // flexible extensible attributes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_types');
    }
};

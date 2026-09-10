<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_partner_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('channel_partner_id')->constrained('channel_partners')->onDelete('cascade');
            $table->string('name', 100);
            $table->string('mobile', 20);
            $table->string('email', 100)->nullable();
            $table->string('designation', 100)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Future partner portal user linkage
            $table->timestamps();

            $table->index(['company_id', 'channel_partner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_partner_contacts');
    }
};

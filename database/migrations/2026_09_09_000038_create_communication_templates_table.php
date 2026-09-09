<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name', 150);
            $table->string('channel', 30); // email, sms, whatsapp, in_app
            $table->string('subject')->nullable();
            $table->text('body');
            $table->json('variables_json')->nullable(); // placeholder tags
            $table->boolean('is_transactional')->default(false); // true = bypass opt-out
            $table->string('status', 30)->default('Active'); // Active, Draft, Archived
            $table->integer('version')->default(1);
            $table->timestamps();

            $table->index(['company_id', 'channel', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_templates');
    }
};

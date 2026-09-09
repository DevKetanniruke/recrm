<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('automation_rule_id')->constrained('automation_rules')->onDelete('cascade');
            $table->string('entity_type', 100);
            $table->unsignedBigInteger('entity_id');
            $table->string('status', 30)->default('Success'); // Success, Failed, Skipped
            $table->text('error_message')->nullable();
            $table->dateTime('executed_at');
            $table->timestamps();

            $table->index(['company_id', 'automation_rule_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_executions');
    }
};

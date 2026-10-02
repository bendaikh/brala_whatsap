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
        Schema::create('workspace_service_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('delivery'); // delivery, payment, logistics, other
            $table->string('api_url')->nullable();
            $table->text('api_key_encrypted')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'is_enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_service_integrations');
    }
};

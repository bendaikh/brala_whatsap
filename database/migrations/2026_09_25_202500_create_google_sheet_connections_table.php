<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_sheet_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('spreadsheet_id')->nullable();
            $table->string('spreadsheet_url')->nullable();
            $table->string('sheet_tab')->default('Sheet1');
            $table->text('webhook_url');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->index(['workspace_id', 'is_enabled']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('google_sheet_connection_id')
                ->nullable()
                ->after('store_id')
                ->constrained('google_sheet_connections')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('google_sheet_connection_id');
        });

        Schema::dropIfExists('google_sheet_connections');
    }
};

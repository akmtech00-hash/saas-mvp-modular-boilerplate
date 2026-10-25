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
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->uuid('workspace_id')->nullable()->index(); // Null = global flag, UUID = tenant override
            $table->string('feature_key')->index();
            $table->boolean('is_enabled')->default(true);
            $table->json('rules')->nullable();
            $table->timestamps();

            $table->unique(['workspace_id', 'feature_key']);
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
    }
};

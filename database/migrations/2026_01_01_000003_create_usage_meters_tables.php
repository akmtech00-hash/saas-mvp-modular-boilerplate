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
        Schema::create('usage_meters', function (Blueprint $table) {
            $table->id();
            $table->uuid('workspace_id')->index();
            $table->string('metric_name'); // e.g. ai_credits, api_calls, storage_mb
            $table->unsignedBigInteger('allocated_limit')->default(100);
            $table->unsignedBigInteger('current_usage')->default(0);
            $table->timestamp('cycle_resets_at')->nullable();
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->unique(['workspace_id', 'metric_name']);
        });

        Schema::create('usage_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('workspace_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('metric_name');
            $table->integer('amount');
            $table->string('operation'); // e.g. "gemini_agent_call", "rag_vector_search"
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_logs');
        Schema::dropIfExists('usage_meters');
    }
};

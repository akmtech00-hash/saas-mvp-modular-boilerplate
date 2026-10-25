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
        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->uuid('workspace_id')->index();
            $table->string('url');
            $table->string('secret');
            $table->json('events'); // e.g. ["member.invited", "subscription.renewed", "credit.exhausted"]
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('webhook_endpoint_id')->index();
            $table->string('event_type')->index();
            $table->json('payload');
            $table->integer('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->float('duration_ms')->nullable();
            $table->integer('attempt')->default(1);
            $table->string('status')->default('pending'); // pending, success, failed
            $table->timestamps();

            $table->foreign('webhook_endpoint_id')->references('id')->on('webhook_endpoints')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhook_endpoints');
    }
};

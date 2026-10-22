<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Durable reservations: no application delete/update operations exist for these rows.
        Schema::create('document_numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->timestamp('allocated_at');
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_number_id')->unique()->constrained('document_numbers')->restrictOnDelete();
            $table->string('tracking_number', 40)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('current_route_id')->nullable()->unique();
            $table->timestamps();
            $table->index(['created_by', 'created_at']);
        });

        Schema::create('document_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->restrictOnDelete();
            $table->foreignId('from_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('from_department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('to_department_id')->constrained('departments')->restrictOnDelete();
            $table->string('status', 20)->index();
            $table->timestamp('sent_at');
            $table->timestamp('seen_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index(['to_user_id', 'status']);
            $table->index(['document_id', 'id']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreign('current_route_id')->references('id')->on('document_routes')->restrictOnDelete();
        });

        Schema::create('document_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->restrictOnDelete();
            $table->foreignId('document_route_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->string('event_type', 30);
            $table->json('previous_value')->nullable();
            $table->json('new_value')->nullable();
            $table->json('context');
            $table->text('remarks')->nullable();
            $table->timestamp('created_at');
            $table->index(['document_id', 'created_at', 'id'], 'document_events_timeline_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_events');
        Schema::table('documents', fn (Blueprint $table) => $table->dropForeign(['current_route_id']));
        Schema::dropIfExists('document_routes');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_numbers');
    }
};

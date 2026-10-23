<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique();
            $table->string('designation')->nullable();
            $table->string('contact_number', 40)->nullable();
            $table->timestamp('profile_completed_at')->nullable();
        });
        Schema::create('profile_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('action');
            $table->json('changes');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_audits');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['google_id', 'designation', 'contact_number', 'profile_completed_at']));
    }
};

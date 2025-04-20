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
        Schema::create('alumni', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('graduation_date');
            $table->string('degree');
            $table->string('faculty');
            $table->string('major')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('current_job')->nullable();
            $table->string('company')->nullable();
            $table->jsonb('social_links')->nullable();
            $table->text('biography')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes(); // For soft deletion
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni');
    }
};

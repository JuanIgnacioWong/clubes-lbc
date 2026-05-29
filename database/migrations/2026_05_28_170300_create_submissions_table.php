<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('division_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('club_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('responsible_name');
            $table->string('phone', 40);
            $table->string('email');
            $table->string('payment_status', 20)->default('pending');
            $table->string('submission_status', 20)->default('received');
            $table->unsignedBigInteger('active_version')->nullable();
            $table->unsignedTinyInteger('max_allowed_submissions')->default(2);
            $table->timestamps();

            $table->unique(['season_id', 'division_id', 'club_id']);
            $table->index(['payment_status', 'submission_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};

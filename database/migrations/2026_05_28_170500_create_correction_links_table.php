<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correction_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('division_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('club_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('token', 120)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['season_id', 'division_id', 'club_id', 'is_active'], 'correction_links_filter_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correction_links');
    }
};

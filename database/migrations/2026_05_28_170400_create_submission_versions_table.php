<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedSmallInteger('version_number');
            $table->string('club_logo_path')->nullable();
            $table->string('payment_receipt_path')->nullable();
            $table->string('players_roster_path')->nullable();
            $table->text('observations')->nullable();
            $table->string('status', 20)->default('received');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['submission_id', 'version_number']);
            $table->index(['submission_id', 'status']);
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->foreign('active_version')
                ->references('id')
                ->on('submission_versions')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['active_version']);
        });

        Schema::dropIfExists('submission_versions');
    }
};

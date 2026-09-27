<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_award_scores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('special_award_id')
                ->constrained('special_awards')
                ->cascadeOnDelete();

            $table->foreignId('special_award_criterion_id')
                ->constrained('special_award_criteria')
                ->cascadeOnDelete();

            $table->foreignId('contestant_id')
                ->constrained('contestants')
                ->cascadeOnDelete();

            // Same judge_id structure as your existing Score model
            $table->foreignId('judge_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->decimal('score', 8, 2);

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique([
                'special_award_id',
                'special_award_criterion_id',
                'contestant_id',
                'judge_id',
            ], 'special_award_scores_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_award_scores');
    }
};
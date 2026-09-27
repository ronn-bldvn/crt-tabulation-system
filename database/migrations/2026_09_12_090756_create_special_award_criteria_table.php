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
        Schema::create('special_award_criteria', function (Blueprint $table) {
            $table->id();

            $table->foreignId('special_award_id')
                ->constrained('special_awards')
                ->cascadeOnDelete();

            $table->string('name');

            $table->decimal('weight', 5, 2);

            $table->decimal('max_score', 8, 2)
                ->default(100);

            $table->unsignedInteger('order')
                ->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): voi
    {
        Schema::dropIfExists('special_award_criteria');
    }
};

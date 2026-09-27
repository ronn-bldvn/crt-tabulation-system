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
        Schema::create('special_award_photos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('special_award_id')
                ->constrained('special_awards')
                ->cascadeOnDelete();

            $table->foreignId('contestant_id')
                ->constrained('contestants')
                ->cascadeOnDelete();

            $table->string('photo_path');

            $table->unsignedInteger('order')
                ->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('special_award_photos');
    }
};

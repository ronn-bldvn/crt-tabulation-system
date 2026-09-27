<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('special_award_scores', 'special_award_criterion_id')) {
            if (! Schema::hasColumn('special_award_scores', 'criterion_id')) {
                Schema::table('special_award_scores', function (Blueprint $table) {
                    $table->renameColumn('special_award_criterion_id', 'criterion_id');
                });
            }
        }

        $hasFk = collect(Schema::getForeignKeys('special_award_scores'))
            ->contains(fn ($foreignKey) => $foreignKey['columns'] === ['criterion_id']
                && $foreignKey['foreign_table'] === 'special_award_criteria');

        if (! $hasFk) {
            Schema::table('special_award_scores', function (Blueprint $table) {
                $table->foreign('criterion_id')
                    ->references('id')
                    ->on('special_award_criteria')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('special_award_scores', 'criterion_id')
            && ! Schema::hasColumn('special_award_scores', 'special_award_criterion_id')) {
            Schema::table('special_award_scores', function (Blueprint $table) {
                $table->dropForeign(['criterion_id']);
                $table->renameColumn('criterion_id', 'special_award_criterion_id');
            });
        }
    }
};
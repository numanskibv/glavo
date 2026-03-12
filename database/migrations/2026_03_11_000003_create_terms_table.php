<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('word_cluster_id')->nullable();
            $table->foreign('word_cluster_id')->references('id')->on('word_clusters')->nullOnDelete();
            $table->unsignedBigInteger('language_id');
            $table->foreign('language_id')->references('id')->on('languages')->cascadeOnDelete();

            // Store original word and explanation. Apply MySQL-specific charset/collation only when using MySQL.
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->string('word')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
                $table->text('definition')->nullable()->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            } else {
                // SQLite and others - default column definitions (SQLite doesn't support MySQL collations)
                $table->string('word');
                $table->text('definition')->nullable();
            }

            $table->string('transliteration')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};

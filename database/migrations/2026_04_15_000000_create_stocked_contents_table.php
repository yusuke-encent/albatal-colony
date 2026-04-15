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
        Schema::create('stocked_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('genre_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('price')->nullable();
            $table->string('currency', 3)->default('JPY');
            $table->string('cover_path')->nullable();
            $table->json('preview_paths')->nullable();
            $table->string('download_path');
            $table->string('download_name');
            $table->string('download_mime_type')->nullable();
            $table->unsignedBigInteger('download_size')->nullable();
            $table->timestamps();
        });

        Schema::create('stocked_content_tag', function (Blueprint $table) {
            $table->foreignId('stocked_content_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['stocked_content_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocked_content_tag');
        Schema::dropIfExists('stocked_contents');
    }
};

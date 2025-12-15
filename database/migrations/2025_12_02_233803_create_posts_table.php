<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ===========================================
        //   TABLE: tags
        // ===========================================
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 150)->unique();
            $table->timestamps();
        });

        // ===========================================
        //   TABLE: posts
        // ===========================================
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            $table->string('title', 255);
            $table->text('content');

            $table->enum('status', ['draft','pending','published','rejected'])
                  ->default('pending');

            $table->string('slug', 255)->unique()->nullable();
            $table->unsignedInteger('views')->default(0);

            $table->dateTime('published_at')->nullable();

            $table->enum('visibility', ['public','private','registered'])
                  ->default('public');

            $table->boolean('allow_comments')->default(true);

            // Review info
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });

        // ===========================================
        //   TABLE: post_images
        // ===========================================
        Schema::create('post_images', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('post_id');
            $table->string('path', 255);
            $table->string('alt', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->boolean('is_main')->default(false);
            $table->integer('order')->default(0);

            $table->timestamps();

            $table->foreign('post_id')
                  ->references('id')->on('posts')
                  ->onDelete('cascade');
        });

        // ===========================================
        //   PIVOT TABLE: post_tag
        // ===========================================
        Schema::create('post_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('tag_id');

            $table->primary(['post_id', 'tag_id']);

            $table->foreign('post_id')
                  ->references('id')->on('posts')
                  ->onDelete('cascade');

            $table->foreign('tag_id')
                  ->references('id')->on('tags')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('post_images');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('tags');
    }
};

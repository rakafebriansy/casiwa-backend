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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->string('title',60)->nullable(false);
            $table->text('description')->nullable(false);
            $table->string('file_name',255)->nullable(false);
            $table->string('thumbnail_name',255)->nullable(false);
            $table->integer('price')->nullable(false);
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->softDeletes();
            
            $table->foreign('user_id')->on('users')->references('id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};

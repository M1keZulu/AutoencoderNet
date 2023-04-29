<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('embeddings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('image_id');
            $table->text('embedding');
            $table->integer('cluster')->nullable();
            $table->timestamps();

            $table->foreign('image_id')->references('id')->on('images')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('embeddings');
    }
};

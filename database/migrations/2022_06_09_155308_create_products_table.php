<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('classification_id');
            $table->foreign('classification_id')->references('id')->on('classifications')->onDelete('cascade');
            $table->unsignedBigInteger('tax_id');
            $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('cascade');
            $table->string('code');
            $table->string('name');
            $table->string('image')->nullable();
            $table->float('price');
            $table->float('stock');
            $table->float('sales');
            $table->float('receipts');
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartItemsTable extends Migration
{
    public function up()
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->bigIncrements('cart_itemID');
            $table->unsignedBigInteger('customerID')->nullable(); // link to customers.customerID
            $table->unsignedBigInteger('productID');
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->string('size')->nullable();
            $table->string('colorway')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('sub_total', 12, 2);
            $table->timestamps();

            // If you want DB-level foreign keys (optional, adapt to your schema)
            // $table->foreign('customerID')->references('customerID')->on('customers')->onDelete('cascade');
            // $table->foreign('productID')->references('productID')->on('products')->onDelete('cascade');
            // $table->foreign('product_variant_id')->references('id')->on('product_variants')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cart_items');
    }
}

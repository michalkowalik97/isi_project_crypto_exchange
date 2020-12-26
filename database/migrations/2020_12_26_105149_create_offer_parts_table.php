<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfferPartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_parts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('offer_id');
            $table->decimal('amount',20,10);
            $table->decimal('rate');
            $table->bigInteger('matched_offer_id')->nullable();
            $table->string('matched_offer_hash',300)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offer_parts');
    }
}

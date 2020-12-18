<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_jobs', function (Blueprint $table) {
            $table->id();
            $table->decimal('max_value');
            $table->decimal('min_profit');
            $table->bigInteger('market_id');
            $table->bigInteger('user_id');
            $table->boolean('active')->default(true);
            $table->bigInteger('offer_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bot_jobs');
    }
}

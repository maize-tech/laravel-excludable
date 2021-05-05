<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExclusionsTable extends Migration
{
    public function up()
    {
        Schema::create('exclusions', function (Blueprint $table) {
            $table->id();
            $table->morphs('excludable');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('exclusions');
    }
}

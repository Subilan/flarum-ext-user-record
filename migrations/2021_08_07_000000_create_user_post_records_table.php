<?php

use Flarum\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

return Migration::createTable("user_post_records", function (Blueprint $table) {
    $table->increments('id');
    $table->timestamps(6);
    $table->string('username', 100);
    $table->integer('enwords', false, true);
    $table->integer('chwords', false, true);
    $table->integer('likes', false, true);
    $table->integer('post_id', false, true);
    $table->string('type', 50);
    $table->boolean('best_answer');
});

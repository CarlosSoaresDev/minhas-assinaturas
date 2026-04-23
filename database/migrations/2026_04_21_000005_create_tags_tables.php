<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->uuid('privacy_token');
            $table->string('name');
            $table->string('color')->nullable();
            $table->timestamps();

            $table->foreign('privacy_token')->references('token')->on('privacy_tokens')->onDelete('cascade');
        });

        Schema::create('subscription_tags', function (Blueprint $table) {
            $table->uuid('subscription_id');
            $table->unsignedBigInteger('tag_id');

            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            $table->primary(['subscription_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_tags');
        Schema::dropIfExists('tags');
    }
};

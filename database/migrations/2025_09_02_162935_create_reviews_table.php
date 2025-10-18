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
        Schema::create('property_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id');
            $table->unsignedBigInteger('reviewer_id');
            $table->unsignedBigInteger('booking_id');
            $table->tinyInteger('rating')->unsigned();
            $table->text('review_text')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique('booking_id', 'unique_booking_review');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_reviews');
    }
};

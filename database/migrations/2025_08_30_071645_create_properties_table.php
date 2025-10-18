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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id');
            $table->string('title', 100);
            $table->text('description');
            $table->string('property_type', 20);
            $table->string('listing_type',20);
            $table->string('property_label', 50);
            $table->integer('size');
            $table->integer('land_area');
            $table->string('property_id', 20)->nullable();
            $table->integer('rooms')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('garages')->nullable();
            $table->integer('garage_size')->nullable();
            $table->integer('year_built')->nullable();
            $table->text('address');
            $table->string('zip_code', 20);
            $table->string('city', 50);
            $table->string('state', 50);
            $table->string('location', 50);
            $table->decimal('price', 12, 2);
            $table->text('terms');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};

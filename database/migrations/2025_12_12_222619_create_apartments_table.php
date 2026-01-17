<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('province');//المحافظة
            $table->string('city');
            $table->text('address');
            $table->text('description');
            $table->decimal('price',8,2);
            $table->integer('count_room');
            $table->integer('count_personal')->nullable();
            $table->integer('area');
            $table->integer('floor');
            $table->boolean('balcony')->default(false); //بلكونة
            $table->boolean('furnished')->default(false); //فرش
            $table->boolean('parking')->default(false); //موقف سيارة
            $table->json('photos');
            $table->boolean('elevator')->default(false);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};

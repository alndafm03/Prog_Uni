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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('apartment_id')->constrained('apartments')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('count_personal');
            $table->decimal('total_price');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'completed'])->default('pending'); //انتظار او تم الموافقة او تم الرفض او تم الالغاء او انتهى
            $table->timestamp('approved_at')->nullable(); //تاريخ الموافقة
            $table->timestamp('cancelled_at')->nullable(); //تاريخ الالغاء

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

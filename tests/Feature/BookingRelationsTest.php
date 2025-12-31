<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Apartment;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingRelationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function renter_can_see_his_bookings()
    {
        $renter = User::factory()->create(['role' => 'renter']);
        $apartment = Apartment::factory()->create();
        $booking = Booking::factory()->create([
            'renter_id' => $renter->id,
            'apartment_id' => $apartment->id,
        ]);

        $this->assertTrue($renter->bookings->contains($booking));
    }

    /** @test */
    public function owner_can_see_his_apartments()
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $apartment = Apartment::factory()->create(['owner_id' => $owner->id]);

        $this->assertTrue($owner->ownedApartments->contains($apartment));
    }
}

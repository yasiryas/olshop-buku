<?php

namespace Tests\Feature;

use App\Models\ProductTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowRouteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_show_for_show_per_role(): void
    {
        $owner = User::where('email', 'owner@mail.com')->firstOrFail();
        $buyer = User::where('email', 'buyer@mail.com')->firstOrFail();
        $tr = ProductTransaction::max('id');

        // Owner
        $rOwner = $this->actingAs($owner)->get('/product_transactions/'.$tr);
        $ownerStatus = $rOwner->status();
        $ownerRedirect = $rOwner->headers->get('Location');

        // Buyer
        $rBuyer = $this->actingAs($buyer)->get('/product_transactions/'.$tr);
        $buyerStatus = $rBuyer->status();
        $buyerRedirect = $rBuyer->headers->get('Location');

        dump(compact('ownerStatus', 'ownerRedirect', 'buyerStatus', 'buyerRedirect'));
        $this->assertTrue(true);
    }
}

<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PromoCodeDeactivationTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * Test successful promo code creation
     *
     * @return void
     */
    public function testDeactivationSuccess()
    {
        $promoCode = factory(App\PromoCode::class)->create();

        $this->get(route('promo-code-deactivate', ['id' => $promoCode->id]))->seeJson([
            'deactivated' => true,
        ])->assertResponseStatus(200);
    }

    public function testPromoCodeNotFound()
    {
        $this->get(route('promo-code-deactivate', ['id' => 200]))->seeJson([
            'deactivated' => false,
        ])->assertResponseStatus(404);
    }
}

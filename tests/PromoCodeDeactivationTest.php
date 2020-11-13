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

        $response = $this->get(route('promo-code-deactivate', ['id' => $promoCode->id]));
        $response->seeJsonStructure([
            'message',
        ]);
        $response->assertResponseStatus(200);
    }

    public function testPromoCodeNotFound()
    {
        $response = $this->get(route('promo-code-deactivate', ['id' => 200]));
        $response->seeJsonStructure([
            'error',
        ]);
        $response->assertResponseStatus(404);
    }
}

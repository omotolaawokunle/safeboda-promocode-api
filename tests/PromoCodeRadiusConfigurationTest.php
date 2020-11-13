<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PromoCodeRadiusConfigurationTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * Test successful promo code creation
     *
     * @return void
     */
    public function testRadiusConfigSuccess()
    {
        $promoCode = factory(App\PromoCode::class)->create();
        $response = $this->put(
            route('promo-code-radius-config', ['id' => $promoCode->id]),
            ['radius' => 500]
        );

        $response->seeJsonStructure([
            'promoCode' => ['id', 'code', 'radius', 'ride_worth', 'created_at', 'expires_at', 'updated_at'],
        ]);
        $response->assertResponseStatus(200);
    }

    public function testRadiusConfigValidationError()
    {
        $promoCode = factory(App\PromoCode::class)->create();
        $response = $this->put(route('promo-code-radius-config', ['id' => $promoCode->id]), []);
        $response->seeJsonStructure([
            'errors' => [],
        ]);
        $response->assertResponseStatus(422);
    }

    public function testPromoCodeNotFound()
    {
        $response = $this->put(
            route('promo-code-radius-config', ['id' => 200]),
            ['radius' => 500]
        );
        $response->seeJsonStructure([
            'error',
        ]);
        $response->assertResponseStatus(404);
    }
}

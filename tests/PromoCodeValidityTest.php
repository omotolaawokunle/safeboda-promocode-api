<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class PromoCodeValidityTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * Test promo code validity endpoint
     *
     * @return void
     */
    public function testPromoCodeIsValid()
    {
        $promoCode = factory(App\PromoCode::class)->create();
        $response = $this->post(
            route('promo-code-validity', ['id' => $promoCode->id]),
            [
                'origin' => ['lat' => 7.7634697, 'lon' => 4.5341617],
                'destination' => ['lat' => 7.760891, 'lon' => 4.5329985],
            ]
        );

        $response->seeJsonStructure([
            'promoCode' => ['id', 'code', 'radius', 'ride_worth', 'created_at', 'expires_at'],
            'polyline'
        ]);
        $response->assertResponseStatus(200);
    }

    /**
     * Test promo code validity endpoint for an invalid promo code
     *
     * @return void
     */
    public function testPromoCodeIsInvalid()
    {
        $promoCode = factory(App\PromoCode::class)->create();
        $response = $this->post(
            route('promo-code-validity', ['id' => $promoCode->id]),
            [
                'origin' => ['lat' => 4.7634697, 'lon' => 2.5341617],
                'destination' => ['lat' => 3.7650869, 'lon' => 8.5375614],
            ]
        );
        $response->seeJsonStructure([
            'error'
        ]);
        $response->assertResponseStatus(400);
    }

    /**
     * Test promo code validity endpoint for validation errors
     *
     * @return void
     */
    public function testPromoCodeValidityValidationError()
    {
        $promoCode = factory(App\PromoCode::class)->create();
        $response = $this->post(route('promo-code-validity', ['id' => $promoCode->id]), []);
        $response->seeJsonStructure(['errors' => [],]);
        $response->assertResponseStatus(422);
    }

    /**
     * Test promo code validity endpoint for non existent promo code
     *
     * @return void
     */
    public function testPromoCodeNotFound()
    {
        $response = $this->post(
            route('promo-code-validity', ['id' => 200]),
            [
                'origin' => ['lat' => 7.7634697, 'lon' => 4.5341617],
                'destination' => ['lat' => 7.7650869, 'lon' => 4.5375614],
                'event' => ['lat' => 7.760891, 'lon' => 4.5329985]
            ]
        );

        $response->seeJsonStructure([
            'error'
        ]);
        $response->assertResponseStatus(404);
    }
}

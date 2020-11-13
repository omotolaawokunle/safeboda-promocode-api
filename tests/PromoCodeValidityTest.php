<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class PromoCodeValidityTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * Test successful promo code creation
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
                'destination' => ['lat' => 7.7650869, 'lon' => 4.5375614],
                'event' => ['lat' => 7.760891, 'lon' => 4.5329985]
            ]
        );

        $response->seeJsonStructure([
            'promoCode' => ['id', 'code', 'radius', 'ride_worth', 'created_at', 'expires_at', 'updated_at'],
            'polyline'
        ]);
        $response->assertResponseStatus(200);
    }

    public function testPromoCodeIsInvalid()
    {
        $promoCode = factory(App\PromoCode::class)->create();
        $response = $this->post(
            route('promo-code-validity', ['id' => $promoCode->id]),
            [
                'origin' => ['lat' => 4.7634697, 'lon' => 2.5341617],
                'destination' => ['lat' => 3.7650869, 'lon' => 8.5375614],
                'event' => ['lat' => 7.760891, 'lon' => 4.5329985]
            ]
        );

        $response->seeJsonStructure([
            'error'
        ]);
        $response->assertResponseStatus(200);
    }

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

//7.760891 4.5329985
//7.7634697 4.5341617
//7.7650869 4.5375614

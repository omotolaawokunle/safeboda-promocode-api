<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PromoCodeCreationTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * Test successful promo code creation
     *
     * @return void
     */
    public function testCreationSuccess()
    {
        $response = $this->post(route('promo-code-create'), ['radius' => 50, 'ride_worth' => 500.50, 'expires_at' => '2020/11/13']);

        $response->seeJsonStructure([
            'promoCode' => ['id', 'code', 'radius', 'ride_worth', 'created_at', 'expires_at', 'updated_at'],
        ]);
        $response->assertResponseStatus(200);
    }

    public function testCreationValidationError()
    {
        $response = $this->post(route('promo-code-create'), []);
        $response->seeJsonStructure([
            'errors' => [],
        ]);
        $response->assertResponseStatus(422);
    }

    public function testRadiusIsRequired()
    {
        $response = $this->post(route('promo-code-create'), ['ride_worth' => 500.50, 'expires_at' => '2020/11/13']);
        $response->seeJson([
            'radius' => ["The radius field is required."],
        ]);
        $response->assertResponseStatus(422);
    }

    public function testRadiusIsNumerical()
    {
        $response = $this->post(route('promo-code-create'), ['radius' => "50m", 'ride_worth' => 500.50, 'expires_at' => '2020/11/13']);
        $response->seeJson([
            'radius' => ["The radius must be a number."],
        ]);
        $response->assertResponseStatus(422);
    }

    public function testRideWorthIsRequired()
    {
        $response = $this->post(route('promo-code-create'), ['radius' => 50, 'expires_at' => '2020/11/13']);
        $response->seeJson([
            'ride_worth' => ["The ride worth field is required."],
        ]);
        $response->assertResponseStatus(422);
    }

    public function testRideWorthIsNumerical()
    {
        $response = $this->post(route('promo-code-create'), ['radius' => 50, 'ride_worth' => "#500.50", 'expires_at' => '2020/11/13']);
        $response->seeJson([
            'ride_worth' => ["The ride worth must be a number."],
        ]);
        $response->assertResponseStatus(422);
    }

    public function testExpiresAtIsRequired()
    {
        $response = $this->post(route('promo-code-create'), ['radius' => 50, 'ride_worth' => 500.50]);
        $response->seeJson([
            'expires_at' => ["The expiry date field is required."],
        ]);
        $response->assertResponseStatus(422);
    }

    public function testExpiresAtIsDate()
    {
        $response = $this->post(route('promo-code-create'), ['radius' => 50, 'ride_worth' => 500.50, 'expires_at' => '12ab/2ab']);
        $response->seeJson([
            'expires_at' => ["The expiry date is not a valid date."],
        ]);
        $response->assertResponseStatus(422);
    }
}

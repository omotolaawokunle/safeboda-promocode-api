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
        $this->json("POST", route('promo-code-create'), ['radius' => 50, 'ride_worth' => 500.50, 'expires_at' => '2020/11/13'])->seeJson([
            'created' => true,
        ])->assertResponseStatus(200);
    }

    public function testCreationValidationError()
    {
        $this->json("POST", route('promo-code-create'), [])->seeJson([
            'created' => false,
        ])->assertResponseStatus(422);
    }

    public function testRadiusIsRequired()
    {
        $this->json("POST", route('promo-code-create'), ['ride_worth' => 500.50, 'expires_at' => '2020/11/13'])->seeJson([
            'created' => false,
        ])->seeJson([
            'radius' => ["The radius field is required."],
        ])->assertResponseStatus(422);
    }

    public function testRadiusIsNumerical()
    {
        $this->json("POST", route('promo-code-create'), ['radius' => "50m", 'ride_worth' => 500.50, 'expires_at' => '2020/11/13'])->seeJson([
            'created' => false,
        ])->seeJson([
            'radius' => ["The radius must be a number."],
        ])->assertResponseStatus(422);
    }

    public function testRideWorthIsRequired()
    {
        $this->json("POST", route('promo-code-create'), ['radius' => 50, 'expires_at' => '2020/11/13'])->seeJson([
            'created' => false,
        ])->seeJson([
            'ride_worth' => ["The ride worth field is required."],
        ])->assertResponseStatus(422);
    }

    public function testRideWorthIsNumerical()
    {
        $this->json("POST", route('promo-code-create'), ['radius' => 50, 'ride_worth' => "#500.50", 'expires_at' => '2020/11/13'])->seeJson([
            'created' => false,
        ])->seeJson([
            'ride_worth' => ["The ride worth must be a number."],
        ])->assertResponseStatus(422);
    }

    public function testExpiresAtIsRequired()
    {
        $this->json("POST", route('promo-code-create'), ['radius' => 50, 'ride_worth' => 500.50])->seeJson([
            'created' => false,
        ])->seeJson([
            'expires_at' => ["The expiry date field is required."],
        ])->assertResponseStatus(422);
    }

    public function testExpiresAtIsDate()
    {
        $this->json("POST", route('promo-code-create'), ['radius' => 50, 'ride_worth' => 500.50, 'expires_at' => '12ab/2ab'])->seeJson([
            'created' => false,
        ])->seeJson([
            'expires_at' => ["The expiry date is not a valid date."],
        ])->assertResponseStatus(422);
    }
}

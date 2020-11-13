<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PromoCodeCreationTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * Test promo code creation endpoint
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

    /**
     * Test promo code creation endpoint for validation errors
     *
     * @return void
     */
    public function testCreationValidationError()
    {
        $response = $this->post(route('promo-code-create'), []);
        $response->seeJsonStructure([
            'errors' => [],
        ]);
        $response->assertResponseStatus(422);
    }

}

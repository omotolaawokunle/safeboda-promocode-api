<?php

use App\PromoCode;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PromoCodeRetrievalTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * Test successful promo code creation
     *
     * @return void
     */
    public function testReturnAllPromoCodes()
    {
        $promoCodes = factory(App\PromoCode::class, 10)->create();
        $this->call("GET", route('promo-code-deactivate', ['id' => $promoCodes[rand(0, 9)]->id]));
        $this->call("GET", route('promo-code-deactivate', ['id' => $promoCodes[rand(0, 9)]->id]));
        $this->get(route('promo-codes', ['type' => 'all']))->seeJsonStructure([
            'promoCodes' => ['*' => [
                'id', 'code', 'radius', 'ride_worth', 'created_at', 'updated_at', 'deleted_at'
            ]]
        ])->assertResponseStatus(200);
    }

    public function testReturnActivePromoCodes()
    {
        $promoCodes = factory(App\PromoCode::class, 10)->create();
        $this->call("GET", route('promo-code-deactivate', ['id' => $promoCodes[rand(0, 9)]->id]));
        $this->call("GET", route('promo-code-deactivate', ['id' => $promoCodes[rand(0, 9)]->id]));
        $this->get(route('promo-codes'))->seeJsonStructure([
            'promoCodes' => ['*' => [
                'id', 'code', 'radius', 'ride_worth', 'created_at', 'updated_at',
            ]]
        ])->assertResponseStatus(200);
    }
}

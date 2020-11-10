<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoCode extends Model
{
    use SoftDeletes;


    protected $fillable = [
        'code', 'radius', 'ride_worth', 'expires_at'
    ];

    /**
     * Generate Promo Code
     *
     * @param integer $length Length of the promo code to be returned
     * @param boolean $alphabetsOnly Whether generated Promo code should contain alphanumeric characters or just alphabets
     * @return string
     */
    public function generateCode(int $length = 6, $alphabetsOnly = true)
    {
        $chars = $alphabetsOnly ? 'abcdefghijklmnopqrstuvwxyz' : '0123456789abcdefghijklmnopqrstuvwxyz';
        $code = substr(str_shuffle($chars), 0, $length);
        if ($this->codeExists($code)) {
            $code = $this->generateCode($length, $alphabetsOnly);
        }
        return $code;
    }

    /**
     * Check if code exists in database
     *
     * @param string $code
     * @return boolean
     */
    private function codeExists(string $code)
    {
        return Promocode::withTrashed()->where('code', $code)->first();
    }


}

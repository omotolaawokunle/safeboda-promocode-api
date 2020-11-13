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


    /**
     * Calculates the great-circle distance between two points, with
     * the Vincenty formula.
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [meters]
     * @return float Distance between points in [meters] (same as earthRadius)
     */
    public static function vincentyGreatCircleDistance(
        $latitudeFrom,
        $longitudeFrom,
        $latitudeTo,
        $longitudeTo,
        $earthRadius = 6371000
    ) {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
            pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);
        return $angle * $earthRadius;
    }

    /**
     * Check if promo code is valid
     *
     * @param array $origin
     * @param array $venue
     * @return boolean
     */
    public function isValid($origin, $venue)
    {
        $distance = self::vincentyGreatCircleDistance($origin['lat'], $origin['lon'], $venue['lat'], $venue['lon']);
        if ($distance > $this->radius) return false;
        return true;
    }
}

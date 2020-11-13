<?php

namespace App\Http\Controllers;

use Polyline;
use App\PromoCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Resources\PromoCodeResource;
use Illuminate\Support\Facades\Validator;

class PromoCodeController extends Controller
{

    /**
     * Retrieve promo codes
     *
     * @return Illuminate\Http\Response
     */
    public function index()
    {
        $type = request()->input('type', 'active'); //Check if url has query type
        if ($type === 'all') {
            $promoCodes = PromoCodeResource::collection(PromoCode::withTrashed()->get());
        } else {
            $promoCodes = PromoCode::where('expires_at', '>=', Carbon::now()->toDateString())->get();
            $promoCodes = PromoCodeResource::collection($promoCodes)->hide(['deleted_at']);
        }
        return response()->json(['promoCodes' => ($promoCodes)], 200);
    }

    /**
     * Create new promo code
     *
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validateCreation($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $promoCodeModel = new PromoCode;
        $promoCode = $promoCodeModel::create([
            'code' => $promoCodeModel->generateCode(),
            'expires_at' => $request->input('expires_at'),
            'radius' => $request->input('radius'),
            'ride_worth' => $request->ride_worth,
        ]);

        return response()->json(['promoCode' => (new PromoCodeResource($promoCode))->hide(['deleted_at'])], 200);
    }

    /**
     * Validate promo code creation
     *
     * @param Request $request
     * @return Illuminate\Support\Facades\Validator
     */
    private function validateCreation(Request $request)
    {
        $customAttribute = ['expires_at' => 'expiry date'];
        $validator = Validator::make($request->all(), [
            'radius' => 'required|numeric',
            'ride_worth' => 'required|numeric',
            'expires_at' => 'required|date'
        ], [], $customAttribute);

        return $validator;
    }

    /**
     * Check promo code validity
     *
     * @param Request $request
     * @param int $id
     * @return Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        /** @var \App\PromoCode $promoCode */
        $promoCode = PromoCode::find($id);

        //Return 404 if promo code is non-existent or not active
        if (!$promoCode || !$this->isActive($promoCode)) return response()->json(['error' => 'Promo code not found'], 404);

        $validator = $this->validateShowPromoCode($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        $data = $request->only('origin', 'destination', 'event');
        if (
            $promoCode->isValid($data["origin"], $data["event"]) ||
            $promoCode->isValid($data["destination"], $data["event"])
        ) {
            $promoCode = (new PromoCodeResource($promoCode))->hide(['deleted_at']);
            $polyline = Polyline::encode([
                [$data['origin']['lat'], $data['origin']['lon']],
                [$data['destination']['lat'], $data['destination']['lon']],
            ]);
            return response()->json(['promoCode' => $promoCode, 'polyline' => $polyline], 200);
        }

        return response()->json(['error' => "Promo code is not valid"], 200);
    }

    /**
     * Validate promo code validity check
     *
     * @param Request $request
     * @return Illuminate\Support\Facades\Validator
     */
    private function validateShowPromoCode(Request $request)
    {
        $customAttributes = ['origin.lon' => 'Origin Longitude', 'origin.lat' => 'Origin Latitude', 'destination.lon' => 'Destination Longitude', 'destination.lat' => 'Destination Latitude', 'event.lon' => 'Event Venue Longitude', 'event.lat' => 'Event Venue Latitude'];

        $validator = Validator::make($request->all(), [
            'origin.lon' => 'required|numeric',
            'origin.lat' => 'required|numeric',
            'destination.lon' => 'required|numeric',
            'destination.lat' => 'required|numeric',
            'event.lon' => 'required|numeric',
            'event.lat' => 'required|numeric',
        ], $customAttributes);
        return $validator;
    }

    /**
     * Configure promo code radius
     *
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        $promoCode = PromoCode::find($id);
        if ($promoCode) {
            $validator = Validator::make($request->all(), [
                'radius' => 'required|numeric',
            ]);
            if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

            $promoCode->radius = $request->input('radius');
            $promoCode->save();
            return response()->json(['promoCode' => $promoCode], 200);
        }
        return response()->json(['error' => "Promo code not found"], 404);
    }

    /**
     * Deactivate Promo Code
     *
     * @param int $id
     * @return Illuminate\Http\Response
     */
    public function deactivate($id)
    {
        $promoCode = PromoCode::find($id);
        if ($promoCode) {
            $promoCode->delete(); //Soft delete promo code
            return response()->json(['message' => "Promo code deactivated"]);
        }
        return response()->json(['error' => "Promo code not found"], 404);
    }

    /**
     * Check if promo code is active and has not passed expiry date
     *
     * @param \App\PromoCode $promoCode
     * @return boolean
     */
    private function isActive($promoCode)
    {
        return ($promoCode->expires_at >= Carbon::now()->toDateString() && is_null($promoCode->deleted_at));
    }
}

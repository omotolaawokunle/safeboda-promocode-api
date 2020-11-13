<?php

namespace App\Http\Controllers;

use App\PromoCode;
use Illuminate\Http\Request;
use App\Http\Resources\PromoCodeResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class PromoCodeController extends Controller
{

    public function index()
    {
        $type = request()->input('type', 'active');
        if ($type === 'all') {
            $promoCodes = PromoCodeResource::collection(PromoCode::withTrashed()->get());
        } else {
            $promoCodes = PromoCode::where('expires_at', '>=', Carbon::now()->toDateString())->get();
            $promoCodes = PromoCodeResource::collection($promoCodes)->hide(['deleted_at']);
        }
        return response()->json(['promoCodes' => ($promoCodes)], 200);
    }

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

    public function show(Request $request, $id)
    {
        $promoCode = PromoCode::find($id);
        if (!$promoCode || !$this->isActive($promoCode)) return response()->json(['error' => 'Promo code not found'], 404);

        $validator = $this->validateShowPromoCode($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 422);

        if (
            $promoCode->isValid($request->input("origin"), $request->input("event")) ||
            $promoCode->isValid($request->input("destination"), $request->input("event"))
        ) {
            $promoCode = (new PromoCodeResource($promoCode))->hide(['deleted_at']);
            return response()->json(['promoCode' => $promoCode, 'polyline' => ''], 200);
        }

        return response()->json(['error' => "Promo code is not valid"], 200);
    }

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

    public function deactivate($id)
    {
        $promoCode = PromoCode::find($id);
        if ($promoCode) {
            $promoCode->delete();
            return response()->json(['message' => "Promo code deactivated"]);
        }
        return response()->json(['error' => "Promo code not found"], 404);
    }

    private function isActive($promoCode)
    {
        return ($promoCode->expires_at >= Carbon::now()->toDateString() && is_null($promoCode->deleted_at));
    }
}

<?php

namespace App\Http\Controllers;

use App\PromoCode;
use Illuminate\Http\Request;
use App\Http\Resources\PromoCodeResource;
use Illuminate\Support\Facades\Validator;

class PromoCodeController extends Controller
{

    public function index()
    {
        $type = request()->input('type', 'active');
        if ($type === 'all') {
            $promoCodes = PromoCodeResource::collection(PromoCode::withTrashed()->get());
        } else {
            $promoCodes = PromoCodeResource::collection(PromoCode::all())->hide(['deleted_at']);
        }
        return response()->json(['promoCodes' => ($promoCodes)], 200);
    }

    public function store(Request $request)
    {
        $validator = $this->validateCreation($request);
        if ($validator->fails()) return response()->json(['errors' => $validator->errors(), 'created' => false], 422);

        $promoCodeModel = new PromoCode;
        $promoCode = $promoCodeModel::create([
            'code' => $promoCodeModel->generateCode(),
            'expires_at' => $request->input('expires_at'),
            'radius' => $request->input('radius'),
            'ride_worth' => $request->ride_worth,
        ]);

        return response()->json(['promo_code' => $promoCode, 'created' => true], 200);
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

    public function deactivate($id)
    {
        $promoCode = PromoCode::find($id);
        if ($promoCode) {
            $promoCode->delete();
            return response()->json(['deactivated' => true]);
        }
        return response()->json(['deactivated' => false], 404);
    }
}

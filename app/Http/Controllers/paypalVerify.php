<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class paypalVerify extends Controller
{
    public function paypalVerify(Request $request)
{
    $orderId = $request->orderID;
    $amount = $request->amount;


    return response()->json([
        "message" => "Paiement PayPal validé",
        "status" => "success"
    ]);
}

}

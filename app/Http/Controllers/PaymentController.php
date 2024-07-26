<?php

namespace App\Http\Controllers;

use App\Http\Resources\GeneralRescource;
use App\Http\Utilities\CustomResponse;
use App\Models\Order;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function getPaymentToken(Request $request): JsonResponse
    {
        $user = Auth::user();

        $order = new Order();
        $order->user_id = $user->id;
        $order->note_id = $request->id;
        $order->status = 'unpaid';
        $order->save();

        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
        \Midtrans\Config::$isSanitized = env('MIDTRANS_IS_SANITIZED');
        \Midtrans\Config::$is3ds = env('MIDTRANS_IS_3DS');

        $params = array(
            'transaction_details' => array(
                'order_id' => $order->id,
                'gross_amount' => $request->price ?? 2500,
            ),
            'customer_details' => array(
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email
            ),
            'callbacks' => [
                'finish' => 'test/' . $request->id,
                'error' => 'www.instagram.com',
                'pending' => 'www.instagram.com',
            ]
        );
        
        $snap_token = \Midtrans\Snap::getSnapToken($params);

        if(isset($snap_token)) {
            $response = new CustomResponse();
            $response->success = true;
            $response->message = 'Token Has Been Created';
            $response->data = [
                'snap_token' => $snap_token
            ];

            return (new GeneralRescource($response))->response()->setStatusCode(200);
        }

        throw new HttpResponseException(response([
            'errors' => [
                'error' => [
                    'Token Can\'t Be Created'
                ]
            ]
        ],500)); 
    }
    public function doPayment(Request $request)
    {
        $server_key = env('MIDTRANS_SERVER_KEY');
        $hashed = hash('sha512',$request->order_id . $request->status_code . $request->gross_amount . $server_key);
        if($hashed == $request->signature_key) {
            if($request->transaction_status == 'caputre' || $request->transaction_status == 'settlement') {
                $order = Order::find($request->order_id);
                $order->update(['status' => 'paid','payment_type' => $request->payment_type]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'OK'
                ])->setStatusCode(200);
            }
        }
        throw new HttpResponseException(response([
            'errors' => [
                'error' => [
                    'Payment Failed'
                ]
            ]
        ],500)); 
    }
}


// $user_id = Auth::user()->id;
// $order = new DownloadDetail();
// $order->user_id = $user_id;
// $order->note_id = $request->id;
// $order->transaction_id = $request->transaction_id;
// $order->order_id = $request->order_id;
// $result = $order->save();
// Log::warning('result' . $result);
// if($result) {
//     $response = new CustomResponse();
//     $response->success = true;
//     $response->message = 'Pembayaran Berhasil';
//     return (new GeneralRescource($response))->response()->setStatusCode(200);
// }
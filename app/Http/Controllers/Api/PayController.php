<?php

namespace App\Http\Controllers\Api;


use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Stripe\Webhook;
use Stripe\Customer;
use Stripe\Price;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Exception\SignatureVerificationException;
use App\Models\Course;
use App\Models\Order;

class PayController extends Controller
{
    //
    public function checkout(Request $request) {

        try {
            $user = $request -> user();
            $token = $user -> token;
            $courseId = $request -> id;


            Stripe::setApiKey('sk_test_51OJxShLgRmwMqsoUM0zuxU1TpjThyb2dq7VZrx89Twb79wGcMhnjDYqgNsNxLtSDcTeP5l5ofCmE3E2O6p75A8zt00aJ3mJNYd');


            $courseResult = Course::where('id', '=', $courseId) -> first();

            //invalid request
            if(empty($courseResult)) {
                return response() -> json([
                    'code' => 400,
                    'msg' => 'Course does not exist',
                    'data' => '',
                ], 400);
            }

            $orderMap = [];

            $orderMap['course_id'] = $courseId;
            $orderMap['user_token'] = $token;
            $orderMap['status'] = 1;

            /*
                if the order has been placed before or not
                So we need order model/table
            */

            $orderRes = Order::where($orderMap) -> first();
            if(!empty($orderRes)) {
                return response() -> json([
                    'code' => 400,
                    'msg' => 'You already bought this course',
                    'data' => '',
                ]);
            }

            //new order for the user
            $YOUR_DOMAIN = env('APP_URL');
            $map = [];
            $map['user_token'] = $token;
            $map['course_id'] = $courseId;
            $map['total_amount'] = $courseResult -> price;
            $map['status'] = 0;
            $map['created_at'] = Carbon::now();
            $orderNum = Order::insertGetId($map);

            $checkoutSession = Session::create(
                [
                    'line_items'=> [[
                        'price_data' => [
                            'currency' => 'USD',
                            'product_data' => [
                                'name' => $courseResult -> name,
                                'description'=> $courseResult -> description,
                            ],
                            'unit_amount' => intval($courseResult -> price * 100),
                        ],
                        'quantity' => 1,
                    ]],
                    'payment_intent_data' => [
                        'metadata' => [
                            'order_num' => $orderNum,
                            'user_token' => $token,
                        ],
                    ],
                    'metadata' => [
                        'order_num' => $orderNum,
                        'user_token' => $token,
                    ],
                    'mode' => 'payment',
                    'success_url' => $YOUR_DOMAIN . 'success',
                    'cancel_url' => $YOUR_DOMAIN . 'cancel',
                ]
            );

            //returning stripe payment url
            return response() -> json([
                'code' => 200,
                'msg' => "Success",
                'data' => $checkoutSession -> url,
             ], 200);

        } catch (\Throwable $th) {
            return response() -> json([
                'error' => $th -> getMessage()
             ], 500);
        }
    }

    public function web_go_hooks()
    {
       Log::info("11211-------");
        Stripe::setApiKey('sk_test_51OJxShLgRmwMqsoUM0zuxU1TpjThyb2dq7VZrx89Twb79wGcMhnjDYqgNsNxLtSDcTeP5l5ofCmE3E2O6p75A8zt00aJ3mJNYd');
        $endpoint_secret = 'whsec_p3NXQMUzCJ2lCbSWiG5Dy0AvA26FcX2U';
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;
       Log::info("payload----" . $payload);

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
           Log::info("UnexpectedValueException" . $e);
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
           Log::info("SignatureVerificationException" . $e);
            http_response_code(400);
            exit();
        }
        Log::info("event---->" . $event);
        // Handle the checkout.session.completed event
        if ($event->type == 'charge.succeeded') {
            $session = $event->data->object;
           Log::info("event->data->object---->" . $session);
            $metadata = $session["metadata"];
            $order_num = $metadata->order_num;
            $user_token = $metadata->user_token;
           Log::info("order_id---->" . $order_num);
            $map = [];
            $map["status"] = 1;
            $map["updated_at"] = Carbon::now();
            $whereMap = [];
            $whereMap["user_token"] = $user_token;
            $whereMap["id"] = $order_num;
            Order::where($whereMap)->update($map);
        }

        http_response_code(200);
    }
}
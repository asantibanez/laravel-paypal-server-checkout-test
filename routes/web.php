<?php

use Illuminate\Support\Facades\Route;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome')
        ->with([
            'clientId' => env('PAYPAL_CLIENT_ID'),
        ]);
});

Route::get('/order-success', function () {
    $orderId = request('id');
    $response = buildClient()->execute(new OrdersGetRequest($orderId));
    dd($response);
});

Route::post('/my-server/get-paypal-transaction', function () {
    //dd('Here', request('orderID'), request()->all());
    $orderId = request('orderID');

    $request = new OrdersCaptureRequest($orderId);

    // 3. Call PayPal to capture an authorization
    $client = buildClient();
    $response = $client->execute($request);

    return response()->json($response);
});

Route::post('/my-server/create-paypal-transaction', function () {
    $request = new OrdersCreateRequest();
    $request->prefer('return=representation');
    $request->body = buildRequestBody();
    // 3. Call PayPal to set up a transaction
    $client = buildClient();
    $response = $client->execute($request);
    if (false)
    {
        print "Status Code: {$response->statusCode}\n";
        print "Status: {$response->result->status}\n";
        print "Order ID: {$response->result->id}\n";
        print "Intent: {$response->result->intent}\n";
        print "Links:\n";
        foreach($response->result->links as $link)
        {
            print "\t{$link->rel}: {$link->href}\tCall Type: {$link->method}\n";
        }

        // To print the whole response body, uncomment the following line
        // echo json_encode($response->result, JSON_PRETTY_PRINT);
    }

    // 4. Return a successful response to the client.
    return response()->json($response);
});

function buildClient()
{
    $clientId = env('PAYPAL_CLIENT_ID');
    $clientSecret = env('PAYPAL_CLIENT_SECRET');

    $sandboxEnvironment = new SandboxEnvironment($clientId, $clientSecret);

    return new PayPalHttpClient($sandboxEnvironment);
}

function buildRequestBody()
{
    return [
        'intent' => 'CAPTURE',
        'application_context' => [
            'return_url' => 'https://example.com/return',
            'cancel_url' => 'https://example.com/cancel'
        ],
        'purchase_units' => [
            [
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => '70.00',
                    'breakdown' => [
                        'item_total' => [
                            'currency_code' => 'USD',
                            'value' => '70.00',
                        ],
                    ],
                ],
                'items' => [
                    [
                        'sku' => '001',
                        'name' => 'Cabellos al Sol',
                        'description' => 'Curso 21 de Mayo',
                        'unit_amount' => [
                            'currency_code' => 'USD',
                            'value' => '35.00'
                        ],
                        'quantity' => '1',
                    ],
                    [
                        'sku' => '002',
                        'name' => 'Rizos y Trenzas',
                        'description' => 'Curso 24 de Mayo',
                        'unit_amount' => [
                            'currency_code' => 'USD',
                            'value' => '35.00'
                        ],
                        'quantity' => '1',
                    ]
                ],
            ]
        ]
    ];
}

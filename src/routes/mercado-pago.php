<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \MercadoPago\Payment as Payment;

$app = new \Slim\App;
MercadoPago\SDK::setAccessToken("TEST-7349260827756112-051700-d7711fe922f8e5332aa24fd8a140cdd4-1125092408");


$app->post('/processPayment',function(Request $request, Response $response){
    try {
        $contents = json_decode(file_get_contents('php://input'), true);
        $parsed_request = $request->withParsedBody($contents);
        $parsed_body = $parsed_request->getParsedBody();
    
        $payment = new MercadoPago\Payment();
        $payment->transaction_amount = $parsed_body['transactionAmount'];
        $payment->token = $parsed_body['token'];
        $payment->description = $parsed_body['description'];
        $payment->installments = $parsed_body['installments'];
        $payment->payment_method_id = $parsed_body['paymentMethodId'];
        $payment->issuer_id = $parsed_body['issuerId'];
    
        $payer = new MercadoPago\Payer();
        $payer->email = $parsed_body['payer']['email'];
        $payer->identification = array(
            "type" => $parsed_body['payer']['identification']['type'],
            "number" => $parsed_body['payer']['identification']['number']
        );
        $payment->payer = $payer;
        $payment->save();
        
        validatePaymentResult($payment);
    
        $response_fields = array(
            'id' => $payment->id,
            'status' => $payment->status,
            'detail' => $payment->status_detail
        );
    
        $response_body = json_encode($response_fields);

        $response->getBody()->write($response_body);

        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);            
        
    } catch(Exception $exception) {
        $response_fields = array('error_message' => $exception->getMessage());

        $response_body = json_encode($response_fields);
        $response->getBody()->write($response_body);

        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

});

function validatePaymentResult($payment) {
    if($payment->id === null) {
        $error_message = 'Unknown error cause';

        if($payment->error !== null) {
            $sdk_error_message = $payment->error->message;
            $error_message = $sdk_error_message !== null ? $sdk_error_message : $error_message;
        }

        throw new Exception($error_message);
    }   
}
?>


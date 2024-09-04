// Send an SMS using Twilio's REST API and PHP
<?php

require 'vendor/autoload.php';

// Your Account SID and Auth Token from console.twilio.com
$sid = "ACa3d09858194c1353aa47b6ae837c2362";
$token = "2564a926d55f1476dbc43c243f0918df";
$client = new Twilio\Rest\Client($sid, $token);

// Use the Client to make requests to the Twilio REST API
$client->messages->create(
    // The number you'd like to send the message to
    '+5541998893575',
    [
        // A Twilio phone number you purchased at https://console.twilio.com
        'from' => '+13185158159',
        // The body of the text message you'd like to send
        'body' => "Hey Jenny! Good luck on the bar exam!"
    ]
);
<?php

$apiUrl = "https://iqsms.airtel.in/api/v1/send-prepaid-sms";

$customerId = "72c5ff0d-4624-4972-bc1c-dcef261dd7f7";
$username = "f8758d62_260c_404b_8d4a_a15ce94593d4";
$password = "jx0NgVQjBT";

$data = [
    "customerId" => $customerId,
    "destinationAddress" => ["918088122761"],
    "message" => "Dear Ajlan, welcome to PROGEEDEE Ventures Private Limited. You have successfully registered for the Golden Dream Savings Plan. Your Customer ID is 12345. Visit https://goldendream.in/ for more details.",
    "sourceAddress" => "PGDVTR",
    "messageType" => "SERVICE_EXPLICIT",
    "dltTemplateId" => "1007289085098641045",
    "entityId" => "1001791223662244844"
];

$ch = curl_init($apiUrl);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "accept: application/json",
    "content-type: application/json",
    "Authorization: Basic " . base64_encode($username . ":" . $password)
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Status: " . $httpCode . "\n";
echo $response;

curl_close($ch);
<?php

// Manually test the SQL Account API with the provided credentials
$url = 'https://estreammsc.sql.com.my/api/customer';
$username = 'INQUIRY_API';
$password = '%9Z9TMPd2&9VO%b7#s%8z1x!YS#&!9$O';

echo "Testing SQL Account API...\n";
echo "URL: $url\n";
echo "Username: $username\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url . '?limit=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$info = curl_getinfo($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "CURL Error: $error\n";
} else {
    echo "HTTP Status Code: " . $info['http_code'] . "\n";
    echo "Response Body:\n";
    echo $response . "\n";
}

echo "\n--- Trying with Capital 'C' ---\n";
$urlCap = 'https://estreammsc.sql.com.my/api/Customer';
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $urlCap . '?limit=1');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_USERPWD, "$username:$password");
curl_setopt($ch2, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
$response2 = curl_exec($ch2);
$info2 = curl_getinfo($ch2);
curl_close($ch2);

echo "URL: $urlCap\n";
echo "HTTP Status Code: " . $info2['http_code'] . "\n";
echo "Response Body:\n";
echo $response2 . "\n";

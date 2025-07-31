<?php
session_start();

// Show form if no code or provider selected
if (!isset($_GET['code']) && !isset($_POST['provider'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Select Provider</title>
    </head>
    <body>
    <form method="post">
        <h1>Select Provider</h1>
        <label><input type="radio" name="provider" value="Google" required> Google</label><br>
        <label><input type="radio" name="provider" value="Yahoo"> Yahoo</label><br>
        <label><input type="radio" name="provider" value="Microsoft"> Microsoft</label><br>
        <label><input type="radio" name="provider" value="Azure"> Azure</label><br>

        <h2>Enter Client Credentials</h2>
        <p>Client ID: <input type="text" name="clientId" required></p>
        <p>Client Secret: <input type="text" name="clientSecret" required></p>
        <p>Tenant ID (only for Azure): <input type="text" name="tenantId"></p>
        <button type="submit">Continue</button>
    </form>
    </body>
    </html>
    <?php
    exit;
}

// Store submitted data
$providerName = $_POST['provider'] ?? $_SESSION['provider'] ?? '';
$clientId = $_POST['clientId'] ?? $_SESSION['clientId'] ?? '';
$clientSecret = $_POST['clientSecret'] ?? $_SESSION['clientSecret'] ?? '';
$tenantId = $_POST['tenantId'] ?? $_SESSION['tenantId'] ?? '';

if (!$providerName || !$clientId || !$clientSecret) {
    exit("Missing required inputs.");
}

// Store in session for later callback
$_SESSION['provider'] = $providerName;
$_SESSION['clientId'] = $clientId;
$_SESSION['clientSecret'] = $clientSecret;
$_SESSION['tenantId'] = $tenantId;

// Build redirect URI
$redirectUri = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

// Here we would normally initialize the provider
echo "<h2>❌ This script requires external libraries to work!</h2>";
echo "<p>You must install OAuth2 libraries via Composer for provider <strong>" . htmlspecialchars($providerName) . "</strong>.</p>";
echo "<p>If you're not using Composer, this file cannot proceed with real authentication.</p>";
echo "<p>Required libraries:</p><ul>";
echo "<li>league/oauth2-client</li>";
echo "<li>league/oauth2-google (for Google)</li>";
echo "<li>stevenmaguire/oauth2-microsoft (for Microsoft)</li>";
echo "<li>hayageek/oauth2-yahoo (for Yahoo)</li>";
echo "<li>greew/oauth2-azure-provider (for Azure)</li>";
echo "</ul>";

echo "<p>Use Composer to install them:</p>";
echo "<pre>composer require league/oauth2-google</pre>";

exit;

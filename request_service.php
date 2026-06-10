<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to request a service";
    header('Location: login.php');
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

// Validate required fields
$required_fields = ['provider_id', 'sub_service_id', 'service_date', 'service_time', 'address'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error'] = "Please fill all required fields";
        header('Location: index.php');
        exit();
    }
}

try {
    // Check if customer is trying to request their own service
    $stmt = $pdo->prepare("SELECT service_type FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $customer = $stmt->fetch();
    
    $stmt = $pdo->prepare("SELECT service_type FROM users WHERE id = ?");
    $stmt->execute([$_POST['provider_id']]);
    $provider = $stmt->fetch();
    
    // Get sub-service details to verify price
    $stmt = $pdo->prepare("SELECT * FROM sub_services WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['sub_service_id'], $_POST['provider_id']]);
    $sub_service = $stmt->fetch();
    
    if (!$sub_service) {
        throw new Exception("Invalid sub-service selected");
    }
    
    // Insert service request
    $stmt = $pdo->prepare("
        INSERT INTO service_requests (
            customer_id, provider_id, sub_service_id, 
            service_date, service_time, address, 
            special_instructions, total_price, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $_POST['provider_id'],
        $_POST['sub_service_id'],
        $_POST['service_date'],
        $_POST['service_time'],
        $_POST['address'],
        $_POST['special_instructions'] ?? null,
        $sub_service['price']
    ]);
    
    $_SESSION['success'] = "Service request sent successfully! The provider will contact you soon.";
    
} catch (Exception $e) {
    $_SESSION['error'] = "Failed to submit request: " . $e->getMessage();
}

header('Location: index.php');
exit();
?>
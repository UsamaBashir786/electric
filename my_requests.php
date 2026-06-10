<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch customer's service requests
$stmt = $pdo->prepare("
    SELECT sr.*, 
           u.full_name as provider_name, 
           u.phone as provider_phone,
           ss.sub_service_name
    FROM service_requests sr
    JOIN users u ON sr.provider_id = u.id
    JOIN sub_services ss ON sr.sub_service_id = ss.id
    WHERE sr.customer_id = ?
    ORDER BY sr.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$requests = $stmt->fetchAll();

$status_badges = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'accepted' => 'bg-blue-100 text-blue-800',
    'in_progress' => 'bg-purple-100 text-purple-800',
    'completed' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-red-100 text-red-800'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Service Requests - ServiceHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans">

<nav class="sticky top-0 z-50 bg-white border-b shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-2">
                <i class="fas fa-tools text-blue-600 text-xl"></i>
                <span class="font-bold text-xl">ServiceHub</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="index.php" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="logout.php" class="text-red-600 hover:text-red-700">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">My Service Requests</h1>
        <p class="text-gray-600 mt-2">Track all your service requests here</p>
    </div>

    <?php if (empty($requests)): ?>
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <i class="fas fa-calendar-alt text-gray-400 text-5xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Service Requests Yet</h3>
            <p class="text-gray-600 mb-4">You haven't made any service requests yet.</p>
            <a href="index.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-search mr-2"></i> Browse Services
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach($requests as $request): ?>
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">
                                <?php echo htmlspecialchars($request['sub_service_name']); ?>
                            </h3>
                            <p class="text-gray-600 mt-1">
                                <i class="fas fa-user"></i> Provider: <?php echo htmlspecialchars($request['provider_name']); ?>
                            </p>
                            <p class="text-gray-600">
                                <i class="fas fa-phone"></i> Contact: <?php echo htmlspecialchars($request['provider_phone']); ?>
                            </p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $status_badges[$request['status']]; ?>">
                            <?php echo ucfirst($request['status']); ?>
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-500">Service Date & Time</p>
                            <p class="font-medium">
                                <?php echo date('F j, Y', strtotime($request['service_date'])); ?> 
                                at <?php echo date('g:i A', strtotime($request['service_time'])); ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Price</p>
                            <p class="font-medium text-green-600">₨ <?php echo number_format($request['total_price'], 2); ?></p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500">Service Address</p>
                            <p class="font-medium"><?php echo htmlspecialchars($request['address']); ?></p>
                        </div>
                        <?php if($request['special_instructions']): ?>
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-500">Special Instructions</p>
                                <p class="text-gray-700"><?php echo htmlspecialchars($request['special_instructions']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="border-t pt-4">
                        <p class="text-sm text-gray-500">
                            Requested on: <?php echo date('F j, Y g:i A', strtotime($request['created_at'])); ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $request_id = $_POST['request_id'];
    $new_status = $_POST['status'];
    
    // Only allow customer to cancel their own pending/accepted requests
    $allowed_statuses = ['cancelled'];
    
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $pdo->prepare("
            UPDATE service_requests 
            SET status = ?, updated_at = NOW() 
            WHERE id = ? AND customer_id = ? 
            AND status IN ('pending', 'accepted')
        ");
        
        if ($stmt->execute([$new_status, $request_id, $_SESSION['user_id']])) {
            $_SESSION['success'] = "Service request has been cancelled successfully.";
        } else {
            $_SESSION['error'] = "Failed to update status. Please try again.";
        }
    }
    
    header('Location: my_requests.php');
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

// Status update options for customers (only cancellation)
$customer_status_options = [
    'cancelled' => 'Cancel Request'
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

    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

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
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $status_badges[$request['status']]; ?>">
                                <?php echo ucfirst($request['status']); ?>
                            </span>
                            
                            <!-- Status Update Dropdown (Only for pending/accepted requests) -->
                            <?php if (in_array($request['status'], ['pending', 'accepted'])): ?>
                                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                    <button @click="open = !open" 
                                            class="ml-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div x-show="open" 
                                         x-transition
                                         class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border">
                                        <form method="POST" action="" class="p-2">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <select name="status" class="w-full px-3 py-2 border rounded-md text-sm mb-2">
                                                <option value="">Update Status</option>
                                                <option value="cancelled">Cancel Request</option>
                                            </select>
                                            <button type="submit" name="update_status" 
                                                    class="w-full bg-red-600 text-white px-3 py-2 rounded-md text-sm hover:bg-red-700">
                                                Apply Update
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
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
                    
                    <!-- Cancel Button for Pending/Accepted Requests (Alternative Style) -->
                    <?php if (in_array($request['status'], ['pending', 'accepted'])): ?>
                        <div class="border-t pt-4 flex justify-end">
                            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to cancel this service request?');">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" name="update_status" 
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    <i class="fas fa-times mr-2"></i> Cancel Request
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                    
                    <div class="border-t pt-4 <?php echo in_array($request['status'], ['pending', 'accepted']) ? 'mt-4' : ''; ?>">
                        <p class="text-sm text-gray-500">
                            Requested on: <?php echo date('F j, Y g:i A', strtotime($request['created_at'])); ?>
                        </p>
                        <?php if ($request['updated_at'] && $request['updated_at'] != $request['created_at']): ?>
                            <p class="text-sm text-gray-500 mt-1">
                                Last updated: <?php echo date('F j, Y g:i A', strtotime($request['updated_at'])); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add Alpine.js for dropdown functionality -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</body>
</html>
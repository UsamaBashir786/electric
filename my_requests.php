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
    
    // Allow customers to cancel or mark as success (only for their own requests)
    $allowed_statuses = ['cancelled', 'success'];
    
    if (in_array($new_status, $allowed_statuses)) {
        // Different conditions for different statuses
        if ($new_status == 'cancelled') {
            // Can only cancel pending or accepted requests
            $stmt = $pdo->prepare("
                UPDATE service_requests 
                SET status = ?, updated_at = NOW() 
                WHERE id = ? AND customer_id = ? 
                AND status IN ('pending', 'accepted')
            ");
        } elseif ($new_status == 'success') {
            // Can only mark as success if status is completed
            $stmt = $pdo->prepare("
                UPDATE service_requests 
                SET status = ?, updated_at = NOW() 
                WHERE id = ? AND customer_id = ? 
                AND status = 'completed'
            ");
        }
        
        if ($stmt->execute([$new_status, $request_id, $_SESSION['user_id']])) {
            $message = ($new_status == 'cancelled') ? "Service request has been cancelled successfully." : "Service request has been marked as successful!";
            $_SESSION['success'] = $message;
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
    'success' => 'bg-emerald-100 text-emerald-800',
    'cancelled' => 'bg-red-100 text-red-800'
];

// Status icons for better visual representation
$status_icons = [
    'pending' => 'fa-clock',
    'accepted' => 'fa-check-circle',
    'in_progress' => 'fa-spinner',
    'completed' => 'fa-check-double',
    'success' => 'fa-trophy',
    'cancelled' => 'fa-ban'
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
    <style>
        .status-transition {
            transition: all 0.3s ease;
        }
        .success-animation {
            animation: pulse 0.5s ease-in-out;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
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
        <p class="text-gray-600 mt-2">Track and manage all your service requests here</p>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative success-animation">
            <i class="fas fa-check-circle mr-2"></i>
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <i class="fas fa-exclamation-circle mr-2"></i>
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
                                <i class="fas <?php echo $status_icons[$request['status']]; ?> mr-1"></i>
                                <?php echo ucfirst($request['status']); ?>
                            </span>
                            
                            <!-- Status Update Dropdown -->
                            <?php if (in_array($request['status'], ['pending', 'accepted', 'completed'])): ?>
                                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                    <button @click="open = !open" 
                                            class="ml-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div x-show="open" 
                                         x-transition
                                         class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg z-10 border">
                                        <form method="POST" action="" class="p-3">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Update Status</label>
                                            <select name="status" class="w-full px-3 py-2 border rounded-md text-sm mb-3">
                                                <option value="">Select action...</option>
                                                <?php if ($request['status'] == 'pending' || $request['status'] == 'accepted'): ?>
                                                    <option value="cancelled">❌ Cancel Request</option>
                                                <?php endif; ?>
                                                <?php if ($request['status'] == 'completed'): ?>
                                                    <option value="success">✅ Mark as Success</option>
                                                <?php endif; ?>
                                            </select>
                                            <button type="submit" name="update_status" 
                                                    class="w-full bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-700">
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
                    
                    <!-- Action Buttons for Status Updates -->
                    <div class="border-t pt-4 flex flex-wrap gap-3 justify-end">
                        <?php if (in_array($request['status'], ['pending', 'accepted'])): ?>
                            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to cancel this service request?');">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" name="update_status" 
                                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    <i class="fas fa-times mr-2"></i> Cancel Request
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if ($request['status'] == 'completed'): ?>
                            <form method="POST" action="" onsubmit="return confirm('Has this service been completed successfully?');">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <input type="hidden" name="status" value="success">
                                <button type="submit" name="update_status" 
                                        class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                                    <i class="fas fa-trophy mr-2"></i> Mark as Success
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if ($request['status'] == 'success'): ?>
                            <div class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg">
                                <i class="fas fa-star mr-2"></i> Service Completed Successfully!
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($request['status'] == 'cancelled'): ?>
                            <div class="px-4 py-2 bg-red-100 text-red-700 rounded-lg">
                                <i class="fas fa-info-circle mr-2"></i> This request has been cancelled
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="border-t pt-4 mt-4">
                        <p class="text-sm text-gray-500">
                            <i class="far fa-calendar-alt mr-1"></i>
                            Requested on: <?php echo date('F j, Y g:i A', strtotime($request['created_at'])); ?>
                        </p>
                        <?php if ($request['updated_at'] && $request['updated_at'] != $request['created_at']): ?>
                            <p class="text-sm text-gray-500 mt-1">
                                <i class="fas fa-sync-alt mr-1"></i>
                                Last updated: <?php echo date('F j, Y g:i A', strtotime($request['updated_at'])); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Rating Section for Success Status -->
                    <?php if ($request['status'] == 'success'): ?>
                        <div class="mt-4 pt-4 border-t border-emerald-200 bg-emerald-50 p-4 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-emerald-800">
                                        <i class="fas fa-smile-wink mr-1"></i> Thank you for your feedback!
                                    </p>
                                    <p class="text-xs text-emerald-600 mt-1">
                                        Your service request has been marked as successful.
                                    </p>
                                </div>
                                <a href="write_review.php?request_id=<?php echo $request['id']; ?>" 
                                   class="text-emerald-700 hover:text-emerald-900">
                                    <i class="fas fa-star mr-1"></i> Write a Review
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add Alpine.js for dropdown functionality -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</body>
</html>
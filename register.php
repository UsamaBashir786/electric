    <?php
    require_once 'config/database.php';

    $error = '';
    $success = '';
    // If already logged in, redirect to dashboard
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard.php");
        exit();
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $service_type = $_POST['service_type'];
        
        // Validation
        if ($password !== $confirm_password) {
            $error = "Passwords do not match!";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters!";
        } else {
            // Check if email exists
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            
            if ($checkStmt->rowCount() > 0) {
                $error = "Email already registered!";
            } else {
                // Insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, service_type) VALUES (?, ?, ?, ?, ?)");
                
                if ($insertStmt->execute([$full_name, $email, $phone, $hashed_password, $service_type])) {
                    $user_id = $pdo->lastInsertId();
                    
                    // Insert sub-services if provided
                    if (!empty($_POST['sub_service_name']) && !empty($_POST['price'])) {
                        $sub_names = $_POST['sub_service_name'];
                        $prices = $_POST['price'];
                        $descriptions = $_POST['description'] ?? [];
                        
                        $subStmt = $pdo->prepare("INSERT INTO sub_services (user_id, sub_service_name, price, description) VALUES (?, ?, ?, ?)");
                        
                        for ($i = 0; $i < count($sub_names); $i++) {
                            if (!empty($sub_names[$i]) && !empty($prices[$i])) {
                                $subStmt->execute([$user_id, $sub_names[$i], $prices[$i], $descriptions[$i] ?? '']);
                            }
                        }
                    }
                    
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $full_name;
                    $_SESSION['service_type'] = $service_type;
                    
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Register - Become a Service Provider</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">

    <div class="container mx-auto px-4 py-12 max-w-4xl">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-blue-600 text-white px-8 py-6">
                <div class="flex items-center gap-3">
                    <i class="fas fa-handshake text-3xl"></i>
                    <h1 class="text-2xl font-bold">Register as Service Provider</h1>
                </div>
                <p class="mt-2 opacity-90">Join our platform and grow your business</p>
            </div>
            
            <form method="POST" action="" class="p-8" id="registerForm">
                <?php if($error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                        <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                        <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Personal Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Full Name *</label>
                        <input type="text" name="full_name" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your full name">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Email Address *</label>
                        <input type="email" name="email" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="you@example.com">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Phone Number *</label>
                        <input type="tel" name="phone" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="+92 XXX XXXXXXX">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Service Type *</label>
                        <select name="service_type" id="serviceType" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Service</option>
                            <option value="electrician">⚡ Electrician</option>
                            <option value="plumber">🔧 Plumber</option>
                            <option value="painter">🎨 Painter</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Password *</label>
                        <input type="password" name="password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Minimum 6 characters">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Confirm Password *</label>
                        <input type="password" name="confirm_password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Re-enter password">
                    </div>
                </div>
                
                <!-- Sub-Services Section -->
                <div class="border-t border-gray-200 pt-6 mt-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-800"><i class="fas fa-list-ul mr-2"></i>Sub-Services & Pricing</h3>
                        <button type="button" id="addSubService" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition">
                            <i class="fas fa-plus mr-1"></i> Add Sub-Service
                        </button>
                    </div>
                    <p class="text-gray-500 text-sm mb-4">Add what specific services you offer (e.g., "Wiring", "Pipe Leak Fix", "Wall Painting") with pricing</p>
                    
                    <div id="subServicesContainer">
                        <div class="sub-service-group bg-gray-50 p-4 rounded-lg mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-gray-700 font-medium mb-1">Sub-Service Name *</label>
                                    <input type="text" name="sub_service_name[]" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., Wiring Installation">
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-medium mb-1">Price (PKR) *</label>
                                    <input type="number" step="0.01" name="price[]" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., 1500">
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-medium mb-1">Description (Optional)</label>
                                    <input type="text" name="description[]" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Brief description">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold text-lg hover:bg-blue-700 transition duration-300 mt-6">
                    <i class="fas fa-user-check mr-2"></i> Register as Service Provider
                </button>
                
                <p class="text-center text-gray-600 mt-4">
                    Already registered? <a href="index.php" class="text-blue-600 hover:underline">View Providers</a>
                </p>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('addSubService').addEventListener('click', function() {
        const container = document.getElementById('subServicesContainer');
        const newGroup = document.createElement('div');
        newGroup.className = 'sub-service-group bg-gray-50 p-4 rounded-lg mb-4';
        newGroup.innerHTML = `
            <div class="flex justify-end mb-2">
                <button type="button" class="remove-service text-red-500 hover:text-red-700">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Sub-Service Name *</label>
                    <input type="text" name="sub_service_name[]" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., Fan Repair">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Price (PKR) *</label>
                    <input type="number" step="0.01" name="price[]" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., 800">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Description (Optional)</label>
                    <input type="text" name="description[]" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Brief description">
                </div>
            </div>
        `;
        
        newGroup.querySelector('.remove-service').addEventListener('click', function() {
            newGroup.remove();
        });
        
        container.appendChild(newGroup);
    });
    </script>

    </body>
    </html>
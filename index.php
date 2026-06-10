<?php
require_once 'config/database.php';

// Fetch all registered service providers with their sub-services
// Exclude the logged-in user from the list if they are a service provider
if (isset($_SESSION['user_id']) && isset($_SESSION['service_type']) && $_SESSION['service_type']) {
    // If logged in user is a service provider, exclude them from the list
    $stmt = $pdo->prepare("
        SELECT u.*, 
               COUNT(ss.id) as sub_services_count,
               MIN(ss.price) as min_price
        FROM users u 
        LEFT JOIN sub_services ss ON u.id = ss.user_id 
        WHERE u.id != ?
        GROUP BY u.id 
        ORDER BY u.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
} else {
    // If not logged in or logged in as customer, show all providers
    $stmt = $pdo->query("
        SELECT u.*, 
               COUNT(ss.id) as sub_services_count,
               MIN(ss.price) as min_price
        FROM users u 
        LEFT JOIN sub_services ss ON u.id = ss.user_id 
        GROUP BY u.id 
        ORDER BY u.created_at DESC
    ");
}
$providers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServiceHub — Trusted Electrician, Plumber & Painter Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        border: "hsl(214.3 31.8% 91.4%)",
                        background: "hsl(0 0% 100%)",
                        foreground: "hsl(222.2 84% 4.9%)",
                        primary: {
                            DEFAULT: "hsl(221.2 83.2% 53.3%)",
                            foreground: "hsl(210 40% 98%)",
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        secondary: {
                            DEFAULT: "hsl(210 40% 96.1%)",
                            foreground: "hsl(222.2 47.4% 11.2%)",
                        },
                        muted: {
                            DEFAULT: "hsl(210 40% 96.1%)",
                            foreground: "hsl(215.4 16.3% 46.9%)",
                        },
                        accent: {
                            DEFAULT: "hsl(210 40% 96.1%)",
                            foreground: "hsl(222.2 47.4% 11.2%)",
                        },
                        destructive: {
                            DEFAULT: "hsl(0 84.2% 60.2%)",
                            foreground: "hsl(210 40% 98%)",
                        },
                        card: {
                            DEFAULT: "hsl(0 0% 100%)",
                            foreground: "hsl(222.2 84% 4.9%)",
                        },
                    },
                    borderRadius: {
                        lg: "0.5rem",
                        md: "calc(0.5rem - 2px)",
                        sm: "calc(0.5rem - 4px)",
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        .service-card {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .service-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.08), 0 8px 10px -6px rgb(0 0 0 / 0.05);
        }
        .gradient-hero {
            background: linear-gradient(135deg, hsl(221.2 83.2% 53.3%) 0%, hsl(221.2 83.2% 43%) 100%);
        }
        .modal {
            transition: all 0.3s ease;
        }
        .modal.hidden {
            display: none !important;
        }
    </style>
</head>
<body class="bg-background text-foreground antialiased">

<!-- Navigation -->
<nav class="sticky top-0 z-50 w-full border-b border-border bg-white/95 backdrop-blur">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                    <i class="fas fa-tools text-primary text-lg"></i>
                </div>
                <span class="font-bold text-xl tracking-tight bg-gradient-to-r from-primary to-primary/70 bg-clip-text text-transparent">ServiceHub</span>
            </div>
            <div class="flex items-center gap-3">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full bg-secondary/50 text-sm">
                        <i class="fas fa-user-circle text-primary"></i>
                        <span class="font-medium"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    </div>
                    <!-- Dashboard Link -->
                    <a href="dashboard.php" class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-primary/10 text-primary hover:bg-primary/20 h-9 px-4 py-2">
                        <i class="fas fa-tachometer-alt mr-2 text-xs"></i> Dashboard
                    </a>
                    <a href="my_requests.php" class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-primary/10 text-primary hover:bg-primary/20 h-9 px-4 py-2">
                        <i class="fas fa-list mr-2 text-xs"></i> My Requests
                    </a>
                    <a href="logout.php" class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-destructive/20 bg-destructive/10 text-destructive hover:bg-destructive/20 h-9 px-4 py-2">
                        <i class="fas fa-sign-out-alt mr-2 text-xs"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-primary text-white shadow hover:bg-primary/90 h-9 px-4 py-2">
                        <i class="fas fa-sign-in-alt mr-2 text-xs"></i> Login
                    </a>
                    <a href="register.php" class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-primary/20 bg-background text-primary hover:bg-primary/5 h-9 px-4 py-2">
                        <i class="fas fa-user-plus mr-2 text-xs"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="gradient-hero text-white py-20 lg:py-28 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-4 py-1.5 mb-6 text-sm font-medium">
            <i class="fas fa-hand-peace text-sm"></i>
            <span>Trusted by 10,000+ customers</span>
        </div>
        <h1 class="text-4xl md:text-6xl font-bold tracking-tight mb-4">
            Find Trusted Service Professionals
        </h1>
        <p class="text-lg md:text-xl text-white/90 mb-8 max-w-2xl mx-auto">
            Electricians, Plumbers, Painters — Verified experts ready to serve at your doorstep
        </p>
        <div class="flex flex-wrap justify-center gap-3">
            <span class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-medium"><i class="fas fa-bolt"></i> Electricians</span>
            <span class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-medium"><i class="fas fa-wrench"></i> Plumbers</span>
            <span class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-medium"><i class="fas fa-paint-roller"></i> Painters</span>
        </div>
    </div>
</section>

<!-- Service Providers Grid -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
    <div class="text-center mb-12">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 text-primary text-sm font-medium mb-4">
            <i class="fas fa-users"></i>
            <span>Expert Network</span>
        </div>
        <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-foreground mb-3">
            Registered Service Providers
        </h2>
        <p class="text-muted-foreground max-w-2xl mx-auto">
            Connect with skilled professionals offering transparent pricing and quality service
        </p>
    </div>

    <?php if (empty($providers)): ?>
        <div class="text-center py-16 bg-secondary/30 rounded-2xl border border-border">
            <div class="w-20 h-20 mx-auto rounded-full bg-primary/10 flex items-center justify-center mb-4">
                <i class="fas fa-handshake text-primary text-3xl"></i>
            </div>
            <p class="text-muted-foreground text-lg mb-4">No service providers registered yet.</p>
            <a href="register.php" class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-primary text-white shadow hover:bg-primary/90 h-10 px-6 py-2">
                Become the First Provider
                <i class="fas fa-arrow-right ml-2 text-xs"></i>
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($providers as $provider): ?>
                <?php
                // Fetch sub-services for this provider
                $stmt2 = $pdo->prepare("SELECT * FROM sub_services WHERE user_id = ? ORDER BY price ASC");
                $stmt2->execute([$provider['id']]);
                $subServices = $stmt2->fetchAll();
                
                $serviceColor = match($provider['service_type']) {
                    'electrician' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'plumber' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                    'painter' => 'bg-purple-50 text-purple-700 border-purple-200',
                    default => 'bg-gray-50 text-gray-700 border-gray-200'
                };
                
                $serviceBadgeIcon = match($provider['service_type']) {
                    'electrician' => 'fa-bolt',
                    'plumber' => 'fa-wrench',
                    'painter' => 'fa-paint-roller',
                    default => 'fa-tag'
                };
                ?>
                <div class="group relative rounded-xl border border-border bg-card shadow-card transition-all duration-200 service-card">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary/80 to-primary/20 rounded-t-xl"></div>
                    
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center border border-primary/20">
                                        <i class="fas fa-user text-primary text-lg"></i>
                                    </div>
                                    <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-green-500 border-2 border-white"></div>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-lg tracking-tight text-foreground"><?php echo htmlspecialchars($provider['full_name']); ?></h3>
                                    <div class="flex items-center gap-1 text-xs text-muted-foreground mt-0.5">
                                        <i class="fas fa-phone-alt text-[10px]"></i>
                                        <span><?php echo htmlspecialchars($provider['phone']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border <?php echo $serviceColor; ?>">
                                <i class="fas <?php echo $serviceBadgeIcon; ?> text-xs"></i>
                                <?php echo ucfirst($provider['service_type']); ?>
                            </span>
                        </div>
                        
                        <div class="border-t border-border my-4"></div>
                        
                        <!-- Sub-services section -->
                        <div class="mb-2 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <i class="fas fa-list-ul text-xs"></i>
                                <span>Sub-services</span>
                            </div>
                            <span class="text-sm font-medium bg-secondary/50 px-2 py-0.5 rounded-full">
                                <?php echo count($subServices); ?> offered
                            </span>
                        </div>
                        
                        <?php if(!empty($subServices)): ?>
                            <div class="space-y-2 mt-3 max-h-60 overflow-y-auto pr-1">
                                <?php foreach($subServices as $index => $sub): ?>
                                    <div class="bg-secondary/30 rounded-lg p-3 transition-all hover:bg-secondary/50">
                                        <div class="flex justify-between items-start gap-2">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-1.5 mb-1">
                                                    <i class="fas fa-circle text-[6px] text-primary/60"></i>
                                                    <span class="font-medium text-sm text-foreground"><?php echo htmlspecialchars($sub['sub_service_name']); ?></span>
                                                </div>
                                                <?php if(!empty($sub['description'])): ?>
                                                    <p class="text-xs text-muted-foreground line-clamp-2"><?php echo htmlspecialchars($sub['description']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-primary/10 text-primary text-sm font-semibold">
                                                    ₨ <?php echo number_format($sub['price'], 0); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4 bg-secondary/20 rounded-lg mt-3">
                                <i class="fas fa-inbox text-muted-foreground/50 text-lg mb-1 block"></i>
                                <p class="text-xs text-muted-foreground">No sub-services listed yet</p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Request Service Button -->
                        <div class="mt-5 pt-2">
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <button onclick="openRequestModal(<?php echo $provider['id']; ?>, '<?php echo htmlspecialchars($provider['full_name']); ?>', <?php echo htmlspecialchars(json_encode($subServices)); ?>)" 
                                        class="request-btn w-full inline-flex items-center justify-center gap-2 rounded-md bg-primary text-white text-sm font-medium px-3 py-2 hover:bg-primary/90 transition-colors">
                                    <i class="fas fa-calendar-check"></i>
                                    Request Service
                                </button>
                            <?php else: ?>
                                <a href="login.php" class="w-full inline-flex items-center justify-center gap-2 rounded-md border border-border bg-background text-foreground text-sm font-medium px-3 py-2 hover:bg-secondary transition-colors">
                                    <i class="fas fa-sign-in-alt text-xs"></i>
                                    Login to Connect
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Service Request Modal -->
<div id="requestModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4" style="display: none;">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">Request Service</h3>
            <button onclick="closeRequestModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form action="request_service.php" method="POST" class="p-6" id="requestForm">
            <input type="hidden" name="provider_id" id="provider_id">
            
            <div class="space-y-4">
                <!-- Provider Info -->
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Service Provider</p>
                    <p class="font-semibold text-gray-900" id="provider_name"></p>
                </div>
                
                <!-- Select Sub-service -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Service *</label>
                    <select name="sub_service_id" id="sub_service_id" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Choose a service</option>
                    </select>
                </div>
                
                <!-- Service Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Service Date *</label>
                    <input type="date" name="service_date" id="service_date" required
                           min="<?php echo date('Y-m-d'); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                
                <!-- Service Time -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Service Time *</label>
                    <select name="service_time" id="service_time" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="">Select time slot</option>
                        <option value="09:00:00">09:00 AM - 10:00 AM</option>
                        <option value="10:00:00">10:00 AM - 11:00 AM</option>
                        <option value="11:00:00">11:00 AM - 12:00 PM</option>
                        <option value="12:00:00">12:00 PM - 01:00 PM</option>
                        <option value="14:00:00">02:00 PM - 03:00 PM</option>
                        <option value="15:00:00">03:00 PM - 04:00 PM</option>
                        <option value="16:00:00">04:00 PM - 05:00 PM</option>
                    </select>
                </div>
                
                <!-- Address -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Service Address *</label>
                    <textarea name="address" id="address" rows="3" required
                              placeholder="Enter your complete address"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                </div>
                
                <!-- Special Instructions -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Special Instructions (Optional)</label>
                    <textarea name="special_instructions" id="special_instructions" rows="2"
                              placeholder="Any specific requirements or instructions"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                </div>
            </div>
            
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeRequestModal()" 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Trust & Stats Section (shadcn style card grid) -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-card border border-border rounded-xl p-6 text-center hover:shadow-card-hover transition-all">
            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-user-check text-primary text-xl"></i>
            </div>
            <h4 class="text-2xl font-bold text-foreground">500+</h4>
            <p class="text-sm text-muted-foreground">Verified Professionals</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-6 text-center hover:shadow-card-hover transition-all">
            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-clock text-primary text-xl"></i>
            </div>
            <h4 class="text-2xl font-bold text-foreground">2hr avg</h4>
            <p class="text-sm text-muted-foreground">Response Time</p>
        </div>
        <div class="bg-card border border-border rounded-xl p-6 text-center hover:shadow-card-hover transition-all">
            <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-star text-primary text-xl"></i>
            </div>
            <h4 class="text-2xl font-bold text-foreground">4.9 ★</h4>
            <p class="text-sm text-muted-foreground">Customer Rating</p>
        </div>
    </div>
</div>

<!-- Modern Footer -->
<footer class="border-t border-border bg-secondary/30 mt-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-md bg-primary/10 flex items-center justify-center">
                    <i class="fas fa-tools text-primary text-sm"></i>
                </div>
                <span class="font-semibold text-foreground">ServiceHub</span>
                <span class="text-xs text-muted-foreground">© 2026</span>
            </div>
            <div class="flex gap-6 text-sm text-muted-foreground">
                <a href="#" class="hover:text-primary transition-colors">About</a>
                <a href="#" class="hover:text-primary transition-colors">Privacy</a>
                <a href="#" class="hover:text-primary transition-colors">Terms</a>
                <a href="#" class="hover:text-primary transition-colors">Support</a>
            </div>
            <div class="flex gap-3">
                <a href="#" class="w-8 h-8 rounded-full bg-card border border-border flex items-center justify-center text-muted-foreground hover:text-primary hover:border-primary/30 transition-all">
                    <i class="fab fa-twitter text-sm"></i>
                </a>
                <a href="#" class="w-8 h-8 rounded-full bg-card border border-border flex items-center justify-center text-muted-foreground hover:text-primary hover:border-primary/30 transition-all">
                    <i class="fab fa-linkedin-in text-sm"></i>
                </a>
                <a href="#" class="w-8 h-8 rounded-full bg-card border border-border flex items-center justify-center text-muted-foreground hover:text-primary hover:border-primary/30 transition-all">
                    <i class="fab fa-facebook-f text-sm"></i>
                </a>
            </div>
        </div>
        <div class="text-center text-xs text-muted-foreground mt-6 pt-4 border-t border-border">
            <p>Connect with trusted service providers — Electricians, Plumbers, Painters at your doorstep</p>
        </div>
    </div>
</footer>
<!-- Success/Error Messages -->
<?php if(isset($_SESSION['success'])): ?>
    <div class="fixed bottom-4 right-4 bg-green-100 border border-green-400 text-green-700 px-6 py-3 rounded-lg shadow-lg z-50">
        <i class="fas fa-check-circle mr-2"></i>
        <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
        ?>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <div class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-6 py-3 rounded-lg shadow-lg z-50">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
        ?>
    </div>
<?php endif; ?>

<script>
function openRequestModal(providerId, providerName, subServices) {
    console.log('Opening modal for provider:', providerId, providerName);
    
    // Set provider info
    document.getElementById('provider_id').value = providerId;
    document.getElementById('provider_name').innerHTML = providerName;
    
    // Populate sub-services dropdown
    const subServiceSelect = document.getElementById('sub_service_id');
    subServiceSelect.innerHTML = '<option value="">Choose a service</option>';
    
    if (subServices && subServices.length > 0) {
        subServices.forEach(service => {
            const option = document.createElement('option');
            option.value = service.id;
            option.textContent = `${service.sub_service_name} - ₨ ${service.price}`;
            subServiceSelect.appendChild(option);
        });
    } else {
        const option = document.createElement('option');
        option.value = "";
        option.textContent = "No services available";
        option.disabled = true;
        subServiceSelect.appendChild(option);
    }
    
    // Set minimum date for service date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('service_date').min = today;
    
    // Show modal
    const modal = document.getElementById('requestModal');
    modal.style.display = 'flex';
    modal.classList.remove('hidden');
}

function closeRequestModal() {
    const modal = document.getElementById('requestModal');
    modal.style.display = 'none';
    modal.classList.add('hidden');
    
    // Reset form
    document.getElementById('requestForm').reset();
}

// Close modal when clicking outside
document.getElementById('requestModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRequestModal();
    }
});

// Auto-hide messages after 5 seconds
setTimeout(() => {
    const messages = document.querySelectorAll('.fixed.bottom-4.right-4');
    messages.forEach(msg => {
        msg.style.display = 'none';
    });
}, 5000);
</script>

</body>
</html>
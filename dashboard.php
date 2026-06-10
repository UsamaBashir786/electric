<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$service_type = $_SESSION['service_type'];

// Fetch user's sub-services
$stmt = $pdo->prepare("SELECT * FROM sub_services WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$subServices = $stmt->fetchAll();

// Handle adding new sub-service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sub_service'])) {
    $sub_name = trim($_POST['sub_service_name']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);

    if (!empty($sub_name) && $price > 0) {
        $insert = $pdo->prepare("INSERT INTO sub_services (user_id, sub_service_name, price, description) VALUES (?, ?, ?, ?)");
        $insert->execute([$user_id, $sub_name, $price, $description]);
        header("Location: dashboard.php");
        exit();
    }
}

// Handle delete sub-service
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delStmt = $pdo->prepare("DELETE FROM sub_services WHERE id = ? AND user_id = ?");
    $delStmt->execute([$delete_id, $user_id]);
    header("Location: dashboard.php");
    exit();
}

// Get service icon and color based on type
$serviceIcon = match($service_type) {
    'electrician' => 'fa-bolt',
    'plumber' => 'fa-wrench',
    'painter' => 'fa-paint-roller',
    default => 'fa-tools'
};

$serviceColor = match($service_type) {
    'electrician' => 'bg-amber-50 text-amber-700 border-amber-200',
    'plumber' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
    'painter' => 'bg-purple-50 text-purple-700 border-purple-200',
    default => 'bg-gray-50 text-gray-700 border-gray-200'
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Manage Your Services | ServiceHub</title>
    <!-- Tailwind CSS + shadcn inspired config -->
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
                        xl: "0.75rem",
                        '2xl': "1rem",
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                    },
                    boxShadow: {
                        'card': '0 1px 3px 0 rgb(0 0 0 / 0.05), 0 1px 2px -1px rgb(0 0 0 / 0.05)',
                        'card-hover': '0 20px 25px -5px rgb(0 0 0 / 0.05), 0 8px 10px -6px rgb(0 0 0 / 0.05)',
                        'dropdown': '0 10px 15px -3px rgb(0 0 0 / 0.05), 0 4px 6px -4px rgb(0 0 0 / 0.05)',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                    },
                },
            },
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,100;14..32,200;14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800;14..32,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        body {
            background: linear-gradient(135deg, hsl(210 40% 98%) 0%, hsl(210 40% 96%) 100%);
        }
        .table-row-hover:hover {
            background-color: hsl(210 40% 96.1%);
            transition: background-color 0.2s ease;
        }
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: hsl(210 40% 96.1%);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: hsl(214.3 31.8% 86.4%);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: hsl(215.4 16.3% 56.9%);
        }
        .input-focus:focus {
            box-shadow: 0 0 0 2px hsl(221.2 83.2% 53.3% / 0.2);
            border-color: hsl(221.2 83.2% 53.3%);
            outline: none;
        }
    </style>
</head>
<body class="bg-background text-foreground antialiased">

<!-- Modern Navigation (shadcn style) -->
<nav class="sticky top-0 z-50 w-full border-b border-border bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                    <i class="fas fa-tachometer-alt text-primary text-lg"></i>
                </div>
                <span class="font-bold text-xl tracking-tight bg-gradient-to-r from-primary to-primary/70 bg-clip-text text-transparent">Dashboard</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full bg-secondary/50 text-sm">
                    <i class="fas fa-user-circle text-primary"></i>
                    <span class="font-medium text-secondary-foreground"><?php echo htmlspecialchars($user_name); ?></span>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border <?php echo $serviceColor; ?>">
                    <i class="fas <?php echo $serviceIcon; ?> text-xs"></i>
                    <?php echo ucfirst($service_type); ?>
                </span>
                <a href="index.php" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring border border-border bg-background text-foreground hover:bg-secondary h-9 px-4 py-2">
                    <i class="fas fa-home mr-2 text-xs"></i> Home
                </a>
                <a href="logout.php" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring bg-destructive text-destructive-foreground shadow hover:bg-destructive/90 h-9 px-4 py-2">
                    <i class="fas fa-sign-out-alt mr-2 text-xs"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-10">
    
    <!-- Welcome Banner -->
    <div class="mb-8 animate-fade-in">
        <div class="bg-gradient-to-r from-primary/5 via-primary/10 to-primary/5 rounded-2xl border border-primary/20 p-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold tracking-tight text-foreground mb-1">
                        Welcome back, <?php echo htmlspecialchars($user_name); ?>! 👋
                    </h1>
                    <p class="text-muted-foreground text-sm">
                        Manage your service offerings, update pricing, and keep your profile up to date.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                        <i class="fas fa-chart-line text-primary"></i>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-muted-foreground">Total Services</div>
                        <div class="text-2xl font-bold text-primary"><?php echo count($subServices); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add New Service Card (shadcn style) -->
    <div class="bg-card border border-border rounded-xl shadow-card overflow-hidden mb-8 transition-all animate-slide-up">
        <div class="border-b border-border bg-secondary/30 px-6 py-4">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                    <i class="fas fa-plus-circle text-green-600"></i>
                </div>
                <h2 class="text-lg font-semibold text-foreground">Add New Sub-Service</h2>
            </div>
        </div>
        <div class="p-6">
            <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">Service Name <span class="text-destructive">*</span></label>
                    <input type="text" name="sub_service_name" placeholder="e.g., AC Repair, Wall Painting" required
                        class="w-full px-3 py-2 rounded-md border border-border bg-background text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all input-focus">
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">Price (PKR) <span class="text-destructive">*</span></label>
                    <input type="number" step="0.01" name="price" placeholder="e.g., 1500" required
                        class="w-full px-3 py-2 rounded-md border border-border bg-background text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all input-focus">
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-foreground">Description</label>
                    <input type="text" name="description" placeholder="Brief description (optional)"
                        class="w-full px-3 py-2 rounded-md border border-border bg-background text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all input-focus">
                </div>
                <div class="flex items-end">
                    <button type="submit" name="add_sub_service"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-md bg-primary text-primary-foreground shadow hover:bg-primary/90 h-10 px-4 py-2 text-sm font-medium transition-colors">
                        <i class="fas fa-save text-xs"></i> Add Service
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Your Services Table -->
    <div class="bg-card border border-border rounded-xl shadow-card overflow-hidden animate-slide-up" style="animation-delay: 0.1s;">
        <div class="border-b border-border bg-secondary/30 px-6 py-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                        <i class="fas fa-list text-primary"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-foreground">Your Offered Services</h2>
                </div>
                <div class="text-xs text-muted-foreground">
                    <i class="fas fa-info-circle mr-1"></i> Manage your service catalog
                </div>
            </div>
        </div>
        
        <div class="p-0">
            <?php if (empty($subServices)): ?>
                <div class="text-center py-12">
                    <div class="w-20 h-20 mx-auto rounded-full bg-secondary/50 flex items-center justify-center mb-4">
                        <i class="fas fa-box-open text-3xl text-muted-foreground"></i>
                    </div>
                    <p class="text-muted-foreground text-sm mb-2">You haven't added any sub-services yet.</p>
                    <p class="text-xs text-muted-foreground/70">Use the form above to add your first service offering!</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-secondary/50 border-b border-border">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                    <i class="fas fa-tag mr-1"></i> Sub-Service
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                    <i class="fas fa-rupee-sign mr-1"></i> Price (PKR)
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                    <i class="fas fa-align-left mr-1"></i> Description
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                    <i class="fas fa-cog mr-1"></i> Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <?php foreach ($subServices as $service): ?>
                                <tr class="table-row-hover transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded bg-primary/5 flex items-center justify-center">
                                                <i class="fas fa-circle text-[6px] text-primary"></i>
                                            </div>
                                            <span class="font-medium text-foreground"><?php echo htmlspecialchars($service['sub_service_name']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-green-50 text-green-700 text-sm font-semibold">
                                            ₨ <?php echo number_format($service['price'], 0); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-muted-foreground max-w-xs">
                                        <?php echo htmlspecialchars($service['description']) ?: '<span class="text-muted-foreground/50 italic">No description</span>'; ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="?delete=<?php echo $service['id']; ?>"
                                            onclick="return confirm('Are you sure you want to remove this service? This action cannot be undone.')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-destructive hover:bg-destructive/10 transition-all text-sm font-medium">
                                            <i class="fas fa-trash-alt text-xs"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Footer with stats -->
        <?php if (!empty($subServices)): ?>
            <div class="border-t border-border bg-secondary/20 px-6 py-3 flex justify-between items-center text-xs text-muted-foreground">
                <div class="flex items-center gap-2">
                    <i class="fas fa-chart-simple"></i>
                    <span>Showing <?php echo count($subServices); ?> service(s)</span>
                </div>
                <div>
                    <i class="fas fa-calendar-alt mr-1"></i>
                    Last updated: <?php echo date('M d, Y'); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>


    <!-- Back Link -->
    <div class="mt-6 text-center">
        <a href="index.php" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-primary transition-colors">
            <i class="fas fa-arrow-left text-xs"></i> Back to Homepage
        </a>
    </div>
</div>

<!-- Footer -->
<footer class="border-t border-border bg-secondary/30 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-3 text-xs text-muted-foreground">
            <div class="flex items-center gap-2">
                <i class="fas fa-tools text-primary"></i>
                <span>ServiceHub Dashboard</span>
            </div>
            <div>© 2026 — Manage your professional services with ease</div>
        </div>
    </div>
</footer>

<script>
    // Add animation on scroll (simple)
    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.animate-slide-up').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(el);
    });
</script>
</body>
</html>
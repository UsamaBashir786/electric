<?php
require_once 'config/database.php';


// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

// Handle remember me functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password!";
    } else {
        // Fetch user from database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['service_type'] = $user['service_type'];
            
            // Set remember me cookie (30 days)
            if ($remember) {
                setcookie('remember_email', $email, time() + (86400 * 30), "/");
            } else {
                if (isset($_COOKIE['remember_email'])) {
                    setcookie('remember_email', '', time() - 3600, "/");
                }
            }
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    }
}

// Check for remember me cookie
$remembered_email = isset($_COOKIE['remember_email']) ? $_COOKIE['remember_email'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — ServiceHub Provider Portal</title>
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
                        'dialog': '0 25px 50px -12px rgb(0 0 0 / 0.25)',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.4s ease-out',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
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
            background: linear-gradient(135deg, hsl(210 40% 98%) 0%, hsl(214.3 31.8% 91.4%) 100%);
        }
        .gradient-bg {
            background: linear-gradient(135deg, hsl(221.2 83.2% 53.3%) 0%, hsl(221.2 83.2% 43%) 100%);
        }
        .input-focus:focus {
            box-shadow: 0 0 0 2px hsl(221.2 83.2% 53.3% / 0.2);
            border-color: hsl(221.2 83.2% 53.3%);
            outline: none;
        }
        .card-hover {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.05);
        }
    </style>
</head>
<body class="bg-background text-foreground antialiased min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md animate-fade-in">

    
    <!-- Login Card (shadcn/ui style) -->
    <div class="bg-card border border-border rounded-xl shadow-dialog overflow-hidden animate-slide-up">
        <!-- Card Header -->
        <div class="gradient-bg px-8 py-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur flex items-center justify-center">
                    <i class="fas fa-sign-in-alt text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-white text-xl font-semibold tracking-tight">Welcome Back!</h2>
                    <p class="text-white/80 text-sm mt-0.5">Login to manage your services</p>
                </div>
            </div>
        </div>
        
        <!-- Card Body -->
        <form method="POST" action="" class="p-8">
            <!-- Error/Success Messages -->
            <?php if($error): ?>
                <div class="mb-6 p-4 rounded-lg bg-destructive/10 border border-destructive/20 text-destructive text-sm flex items-start gap-3">
                    <i class="fas fa-exclamation-circle mt-0.5 flex-shrink-0"></i>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm flex items-start gap-3">
                    <i class="fas fa-check-circle mt-0.5 flex-shrink-0"></i>
                    <div><?php echo htmlspecialchars($success); ?></div>
                </div>
            <?php endif; ?>
            
            <!-- Email Input -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-foreground mb-2">
                    <i class="fas fa-envelope mr-2 text-primary text-xs"></i>Email Address
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-muted-foreground text-sm"></i>
                    </div>
                    <input type="email" name="email" required 
                           class="w-full pl-10 pr-4 py-2.5 rounded-md border border-border bg-background text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all input-focus"
                           placeholder="provider@example.com"
                           value="<?php echo $remembered_email ? htmlspecialchars($remembered_email) : (isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''); ?>">
                </div>
            </div>
            
            <!-- Password Input -->
            <div class="mb-5">
                <label class="block text-sm font-medium text-foreground mb-2">
                    <i class="fas fa-lock mr-2 text-primary text-xs"></i>Password
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-key text-muted-foreground text-sm"></i>
                    </div>
                    <input type="password" name="password" required 
                           class="w-full pl-10 pr-12 py-2.5 rounded-md border border-border bg-background text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all input-focus"
                           placeholder="••••••••" id="password">
                    <button type="button" onclick="togglePassword()" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-muted-foreground hover:text-foreground transition-colors">
                        <i class="fas fa-eye text-sm" id="toggleIcon"></i>
                    </button>
                </div>
            </div>
            
            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-border text-primary focus:ring-primary/20 focus:ring-offset-0" 
                           <?php echo $remembered_email ? 'checked' : ''; ?>>
                    <span class="ml-2 text-sm text-muted-foreground">Remember me</span>
                </label>
            </div>
            
            <!-- Login Button -->
            <button type="submit" 
                    class="w-full gradient-bg text-white py-2.5 rounded-md font-semibold text-sm transition-all duration-200 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
                <i class="fas fa-arrow-right-to-bracket mr-2"></i>Login to Dashboard
            </button>
            
            <!-- Register Link -->
            <div class="mt-6 pt-5 border-t border-border text-center">
                <p class="text-sm text-muted-foreground">
                    Don't have an account? 
                    <a href="register.php" class="text-primary font-semibold hover:text-primary/80 hover:underline transition-colors">
                        Register as Service Provider
                    </a>
                </p>
                <p class="text-xs text-muted-foreground/60 mt-3 flex items-center justify-center gap-1">
                    <i class="fas fa-shield-alt text-primary/60"></i>
                    <span>Secure login powered by ServiceHub</span>
                </p>
            </div>
        </form>
    </div>
    
    <!-- Features Badges (shadcn style) -->
    <div class="mt-8 text-center">
        <div class="flex justify-center gap-3 flex-wrap">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-card border border-border text-xs font-medium text-muted-foreground shadow-card">
                <i class="fas fa-bolt text-amber-500 text-xs"></i> Electricians
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-card border border-border text-xs font-medium text-muted-foreground shadow-card">
                <i class="fas fa-wrench text-cyan-500 text-xs"></i> Plumbers
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-card border border-border text-xs font-medium text-muted-foreground shadow-card">
                <i class="fas fa-paint-roller text-purple-500 text-xs"></i> Painters
            </span>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Add ripple effect to button (optional)
document.querySelector('button[type="submit"]').addEventListener('click', function(e) {
    const ripple = document.createElement('span');
    ripple.classList.add('ripple');
    // You can add ripple styles if desired
});
</script>

</body>
</html>
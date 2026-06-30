<?php
// Obtener acción por defecto para mostrar login o registro
$action = $_GET['action'] ?? 'login';
$showRegister = ($action === 'register');

// Determinar ruta base para enlaces
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($base, -1) !== '/') {
    $base .= '/';
}
?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>smartSACH - Acceso</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary": "#2d5a46",
                        "primary-container": "#2d5a46",
                        "on-primary": "#ffffff",
                        "surface": "#fbf9f8",
                        "surface-container": "#f0eded",
                        "on-surface": "#1b1c1c",
                        "on-surface-variant": "#414944",
                        "outline": "#717973",
                        "secondary": "#006e2a",
                        "tertiary": "#163a6c"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "md": "24px",
                        "xl": "80px",
                        "sm": "12px",
                        "lg": "48px",
                        "gutter": "16px",
                        "xs": "4px",
                        "max-width": "1200px",
                        "margin-mobile": "20px",
                        "margin-desktop": "auto",
                        "base": "8px"
                    },
                    "fontFamily": {
                        "body-md": ["Inter"],
                        "headline-lg": ["Inter"],
                        "label-md": ["Inter"]
                    }
                },
            },
        }
    </script>
    <!-- Estilos Separados -->
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css" />
</head>
<body class="bg-surface text-on-surface font-body-md overflow-x-hidden min-h-screen flex flex-col">
    <main class="flex flex-col md:flex-row flex-grow">
        <!-- Left Panel (Branding & Welcome) -->
        <section class="w-full md:w-[50%] bg-primary text-on-primary flex flex-col items-center justify-center p-8 relative overflow-visible z-20 min-h-[400px] md:min-h-screen">
            <!-- Organic shape separator -->
            <div class="organic-curve"></div>
            <div class="max-w-md w-full flex flex-col items-center text-center relative z-30">
                <!-- Branding -->
                <div class="mb-8">
                    <div class="w-48 h-48 flex items-center justify-center mb-4 mx-auto">
                        <img alt="smartSACH Logo" class="w-full h-full object-contain" src="<?= $base ?>assets/sachlogo.png"/>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight">Smartsach</h1>
                </div>
                <!-- Text Content -->
                <h2 class="text-4xl font-bold mb-4">Bienvenidos panameños</h2>
                <p class="text-base opacity-90 leading-relaxed mb-8 max-w-sm mx-auto">
                    Nuestra página web, donde encontrarás información valiosa, como agendas y rutas de recolección en Chiriquí.
                </p>
                <!-- Botón para volver al Home estático -->
                <a href="<?= $base ?>home" class="border-2 border-white/60 hover:border-white text-white px-8 py-2 rounded-full font-semibold transition-all hover:bg-white/10 active:scale-95">
                    Volver al Inicio
                </a>
                
                <nav class="mt-12 flex items-center gap-4 text-white/70 text-sm font-medium">
                    <a class="hover:text-white transition-colors" href="<?= $base ?>home#nosotros">Nosotros</a>
                    <span class="opacity-30">|</span>
                    <a class="hover:text-white transition-colors" href="#">Ayuda</a>
                </nav>
            </div>
        </section>

        <!-- Right Panel (Forms) -->
        <section class="w-full md:w-[50%] flex flex-col items-center justify-center p-8 bg-white relative z-10">
            <div class="w-full max-w-[400px] flex flex-col items-center">
                <!-- Center Logo -->
                <div class="mb-8 w-full flex justify-center">
                    <img alt="smartSACH Logo" class="h-16 md:h-20 w-auto object-contain" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCEyFjWknnizd612_uBEc-h8DbkWzMAM0nNU1rgJOYVXuoui8h1AApLST-ModXuX7iuTZh4z-5XVtPBQe9I7Wt6o5Dv_stmPWZaofVzM9DRHCx5OUxgzRikurRlzCW_NKLnbDvuZ0uYTfNCfxeEf26UHdMJDauIUtiu591iYMxVdtF9hz-S9rWBxsPBk4dGy2cveq_x-sAj9G8SU213IU2tpjVjBgFQv5WkPXOreBNurEKhRbIlEAbEG5pKNvlZzTnsSWedNQChdts"/>
                </div>

                <!-- Notificaciones de errores de backend en el flujo auth -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-full text-center text-sm w-full mb-4">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-full text-center text-sm w-full mb-4">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <!-- Login Container -->
                <div class="<?= $showRegister ? 'auth-hidden' : 'auth-visible' ?> w-full text-center" id="login-container">
                    <p class="text-on-surface-variant mb-6">Inicie sesión para acceder a la plataforma</p>
                    <form class="space-y-4" action="<?= $base ?>auth?action=login" method="POST">
                        <div>
                            <input name="email" class="w-full bg-[#9bb2a8]/30 border-none rounded-full py-3.5 px-6 text-on-surface placeholder:text-on-surface/50 focus:ring-2 focus:ring-primary transition-all outline-none text-center" placeholder="Correo electrónico" type="email" required/>
                        </div>
                        <div>
                            <input name="password" class="w-full bg-[#9bb2a8]/30 border-none rounded-full py-3.5 px-6 text-on-surface placeholder:text-on-surface/50 focus:ring-2 focus:ring-primary transition-all outline-none text-center" placeholder="Contraseña" type="password" required/>
                        </div>
                        <button type="submit" class="w-full bg-[#1e4638] py-3 rounded-full text-white font-semibold hover:bg-primary transition-all shadow-lg active:scale-95 mt-4">
                            Ingresar
                        </button>
                    </form>
                    <div class="mt-8 text-sm">
                        <span class="text-on-surface-variant">¿No tienes cuenta?</span>
                        <button class="text-[#00c46a] font-bold ml-1 hover:underline focus:outline-none" onclick="toggleAuth(false)">Crear cuenta</button>
                    </div>
                </div>

                <!-- Registration Form -->
                <div class="<?= $showRegister ? 'auth-visible' : 'auth-hidden' ?> w-full" id="register-container">
                    <div class="mb-6 text-center">
                        <button class="flex items-center gap-1 text-on-surface-variant hover:text-primary mb-4 font-semibold mx-auto focus:outline-none" onclick="toggleAuth(true)">
                            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Volver
                        </button>
                        <h3 class="text-2xl font-bold text-primary">Crear cuenta</h3>
                    </div>
                    <form class="space-y-4" action="<?= $base ?>auth?action=register" method="POST">
                        <input name="nombre" class="w-full bg-[#9bb2a8]/30 border-none rounded-full py-3 px-6 text-on-surface focus:ring-2 focus:ring-primary outline-none" placeholder="Nombre Completo" type="text" required/>
                        <input name="email" class="w-full bg-[#9bb2a8]/30 border-none rounded-full py-3 px-6 text-on-surface focus:ring-2 focus:ring-primary outline-none" placeholder="Correo electrónico" type="email" required/>
                        <input name="password" class="w-full bg-[#9bb2a8]/30 border-none rounded-full py-3 px-6 text-on-surface focus:ring-2 focus:ring-primary outline-none" placeholder="Contraseña (mínimo 6 caracteres)" type="password" required/>
                        <button type="submit" class="w-full bg-secondary py-3 rounded-full text-white font-semibold hover:brightness-110 shadow-md transition-all mt-4">
                            Registrarse
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>



    <script>
        function toggleAuth(showLogin) {
            const login = document.getElementById('login-container');
            const register = document.getElementById('register-container');
            
            if (showLogin) {
                register.classList.replace('auth-visible', 'auth-hidden');
                login.classList.replace('auth-hidden', 'auth-visible');
            } else {
                login.classList.replace('auth-visible', 'auth-hidden');
                register.classList.replace('auth-hidden', 'auth-visible');
            }
        }
    </script>
</body>
</html>

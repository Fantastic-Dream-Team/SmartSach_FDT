<?php
// Determinar ruta base para los enlaces relativos
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($base, -1) !== '/') {
    $base .= '/';
}

// Consultar contadores si el usuario es cliente
$notifReportesCount = 0;
$notifRepliesCount = 0;
if (isset($_SESSION['user_id']) && $_SESSION['user_rol'] === 'cliente') {
    require_once ROOT_PATH . '/backend/src/models/Reporte.php';
    $headerReporteModel = new Reporte();
    $notifReportesCount = $headerReporteModel->getReportesCountByUsuario($_SESSION['user_id']);
    $notifRepliesCount = $headerReporteModel->getUnreadRepliesCount($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>smartSACH - Servicios Ambientales de Chiriquí</title>
    <!-- Tailwind CSS con plugins -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <!-- Leaflet.js (Mapas) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Leaflet Routing Machine para simulación en calles (solo clientes y gestores) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

    <!-- Configuración del tema Tailwind -->
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#2d5a46",
                        "primary-container": "#2d5a46",
                        "on-primary": "#ffffff",
                        surface: "#fbf9f8",
                        "surface-container": "#f0eded",
                        "on-surface": "#1b1c1c",
                        "on-surface-variant": "#414944",
                        outline: "#717973",
                        secondary: "#006e2a",
                        tertiary: "#163a6c"
                    },
                    borderRadius: {
                        DEFAULT: "0.5rem",
                        lg: "1rem",
                        xl: "1.5rem",
                        full: "9999px"
                    },
                    spacing: {
                        md: "24px",
                        xl: "80px",
                        sm: "12px",
                        lg: "48px",
                        gutter: "16px",
                        xs: "4px",
                        "max-width": "1200px",
                        "margin-mobile": "20px",
                        "margin-desktop": "auto",
                        base: "8px"
                    },
                    fontFamily: {
                        sans: ["Inter", "sans-serif"]
                    }
                },
            },
        }
    </script>
    <!-- Estilos Separados -->
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css" />
</head>
<body class="bg-surface text-on-surface min-h-screen flex flex-col">
    <!-- Navbar -->
    <header class="bg-primary text-on-primary shadow-md sticky top-0 z-[1000]">
        <div class="max-w-[1200px] mx-auto px-4 py-3 flex items-center justify-between">
            <!-- Logo y Nombre de la Empresa -->
            <a href="<?= $base ?>" class="flex items-center gap-2">
                <div class="w-12 h-12 flex items-center justify-center">
                    <img alt="smartSACH Logo" class="w-full h-full object-contain" src="<?= $base ?>assets/sachlogo.png"/>
                </div>
                <span class="font-bold text-xl tracking-tight text-white">smartSACH</span>
            </a>

            <!-- Navegación Dinámica por Rol -->
            <nav class="hidden md:flex items-center gap-5 font-medium text-sm">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php $rol = $_SESSION['user_rol']; ?>
                    
                    <?php if ($rol === 'cliente'): ?>
                        <a href="<?= $base ?>home" class="hover:text-[#9fcfb6] transition-colors">Inicio</a>
                        <a href="<?= $base ?>dashboard" class="hover:text-[#9fcfb6] transition-colors">Dashboard</a>
                        <a href="<?= $base ?>payments" class="hover:text-[#9fcfb6] transition-colors">Pagos</a>
                        
                        <!-- Link unificado de Reportes y Ayuda con su contador -->
                        <a href="<?= $base ?>help" class="hover:text-[#9fcfb6] transition-colors flex items-center gap-1">
                            Reportes y Ayuda
                            <span id="badge-unread-replies" class="<?= ($notifRepliesCount > 0) ? 'inline-block' : 'hidden' ?> bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-bold animate-bounce">
                                <?= $notifRepliesCount ?>
                            </span>
                        </a>
                        
                        <!-- Link de Perfil sin contador -->
                        <a href="<?= $base ?>profile" class="hover:text-[#9fcfb6] transition-colors flex items-center gap-1">
                            Perfil
                        </a>

                    <?php elseif ($rol === 'gestor'): ?>
                        <a href="<?= $base ?>home" class="hover:text-[#9fcfb6] transition-colors">Inicio</a>
                        <a href="<?= $base ?>dashboard" class="hover:text-[#9fcfb6] transition-colors flex items-center gap-1">
                            Dashboard
                        </a>
                        <a href="<?= $base ?>help" class="hover:text-[#9fcfb6] transition-colors">Administrar Reportes</a>
                        <a href="<?= $base ?>profile" class="hover:text-[#9fcfb6] transition-colors">Zonas y Personal</a>
                        <a href="<?= $base ?>news" class="hover:text-[#9fcfb6] transition-colors">Gestionar Noticias</a>

                    <?php elseif ($rol === 'conductor'): ?>
                        <a href="<?= $base ?>dashboard" class="hover:text-[#9fcfb6] transition-colors flex items-center gap-1">
                            Mi Ruta
                        </a>
                        <a href="<?= $base ?>profile" class="hover:text-[#9fcfb6] transition-colors">Mi Perfil</a>
                    <?php endif; ?>

                <?php else: ?>
                    <a href="<?= $base ?>" class="hover:text-[#9fcfb6] transition-colors">Inicio</a>
                    <a href="<?= $base ?>home#nosotros" class="hover:text-[#9fcfb6] transition-colors">Nosotros</a>
                <?php endif; ?>
            </nav>

            <!-- Acciones -->
            <div class="flex items-center gap-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= $base ?>logout" class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-full text-xs font-semibold transition-all">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="<?= $base ?>auth" class="bg-[#00c46a] hover:bg-[#00ab5d] text-white px-5 py-2 rounded-full text-xs font-semibold transition-all shadow-md">Iniciar Sesión</a>
                <?php endif; ?>
                
                <!-- Hamburguesa móvil -->
                <button class="md:hidden flex items-center text-white" onclick="toggleMobileMenu()">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>
            </div>
        </div>

        <!-- Menú Móvil -->
        <div id="mobile-menu" class="hidden md:hidden bg-primary/95 border-t border-white/10 px-4 py-4 space-y-3">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php $rol = $_SESSION['user_rol']; ?>
                <?php if ($rol === 'cliente'): ?>
                    <a href="<?= $base ?>home" class="block text-white py-1">Inicio</a>
                    <a href="<?= $base ?>dashboard" class="block text-white py-1">Dashboard</a>
                    <a href="<?= $base ?>payments" class="block text-white py-1">Pagos</a>
                    <a href="<?= $base ?>help" class="block text-white py-1">Reportes y Ayuda (<?= $notifRepliesCount ?>)</a>
                    <a href="<?= $base ?>profile" class="block text-white py-1">Perfil</a>
                <?php elseif ($rol === 'gestor'): ?>
                    <a href="<?= $base ?>home" class="block text-white py-1">Inicio</a>
                    <a href="<?= $base ?>dashboard" class="block text-white py-1">Dashboard</a>
                    <a href="<?= $base ?>help" class="block text-white py-1">Administrar Reportes</a>
                    <a href="<?= $base ?>profile" class="block text-white py-1">Zonas y Personal</a>
                    <a href="<?= $base ?>news" class="block text-white py-1">Gestionar Noticias</a>
                <?php elseif ($rol === 'conductor'): ?>
                    <a href="<?= $base ?>dashboard" class="block text-white py-1">Mi Ruta</a>
                    <a href="<?= $base ?>profile" class="block text-white py-1">Mi Perfil</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?= $base ?>" class="block text-white py-1">Inicio</a>
                <a href="<?= $base ?>auth" class="block text-white py-1">Iniciar Sesión</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Botón flotante Volver Arriba -->
    <button id="btn-scroll-top" onclick="scrollToTop()" class="bg-secondary hover:bg-[#00ab5d] text-white p-3 rounded-full shadow-lg transition-all transform hover:scale-110 active:scale-95 flex items-center justify-center">
        <span class="material-symbols-outlined">arrow_upward</span>
    </button>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }

        // Mostrar / Ocultar botón Volver Arriba al scrollear
        window.onscroll = function() {
            var btn = document.getElementById("btn-scroll-top");
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                btn.style.display = "flex";
            } else {
                btn.style.display = "none";
            }
        };

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // AJAX Polling para actualizar en tiempo real las notificaciones
        function checkUnreadNotifications() {
            fetch('<?= $base ?>notifications')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        var badge = document.getElementById('badge-unread-replies');
                        if (badge) {
                            var count = parseInt(data.unread_count, 10);
                            if (count > 0) {
                                badge.innerText = count;
                                badge.classList.remove('hidden');
                                badge.classList.add('inline-block');
                            } else {
                                badge.classList.add('hidden');
                                badge.classList.remove('inline-block');
                            }
                        }
                    }
                })
                .catch(err => console.error('Error fetching notifications:', err));
        }

        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_rol'] === 'cliente'): ?>
            document.addEventListener("DOMContentLoaded", function() {
                checkUnreadNotifications();
                setInterval(checkUnreadNotifications, 10000);
            });
        <?php endif; ?>
    </script>

    <main class="flex-grow">
        <!-- Notificaciones de éxito/error de sesión -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="max-w-[1200px] mx-auto mt-4 px-4">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">¡Éxito!</strong>
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success']) ?></span>
                    <?php unset($_SESSION['success']); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="max-w-[1200px] mx-auto mt-4 px-4">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error:</strong>
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error']) ?></span>
                    <?php unset($_SESSION['error']); ?>
                </div>
            </div>
        <?php endif; ?>

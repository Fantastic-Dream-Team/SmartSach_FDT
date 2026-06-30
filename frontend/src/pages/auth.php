<?php
// Obtener acción por defecto para mostrar login o registro
$action = $_GET['action'] ?? 'login';
$showRegister = ($action === 'register');

// Determinar ruta base para enlaces
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($base, -1) !== '/') {
    $base .= '/';
}

$supabaseUrl = getenv('SUPABASE_URL') ?: '';
$supabaseAnonKey = getenv('SUPABASE_ANON_KEY') ?: '';
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
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css" />
    <script>
        // Inyectar variables de entorno de Supabase al frontend
        window.SUPABASE_URL = "<?= $supabaseUrl ?>";
        window.SUPABASE_ANON_KEY = "<?= $supabaseAnonKey ?>";
    </script>
</head>
<body class="bg-surface text-on-surface font-body-md overflow-x-hidden min-h-screen flex flex-col">
    <main class="flex flex-col md:flex-row flex-grow">
        <!-- Left Panel -->
        <section class="w-full md:w-[50%] bg-primary text-on-primary flex flex-col items-center justify-center p-8 relative overflow-visible z-20 min-h-[400px] md:min-h-screen">
            <div class="organic-curve"></div>
            <div class="max-w-md w-full flex flex-col items-center text-center relative z-30">
                <div class="mb-8">
                    <div class="w-48 h-48 flex items-center justify-center mb-4 mx-auto">
                        <img alt="smartSACH Logo" class="w-full h-full object-contain" src="<?= $base ?>assets/sachlogo.png"/>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight">Smartsach</h1>
                </div>
                <h2 class="text-4xl font-bold mb-4">Bienvenidos panameños</h2>
                <p class="text-base opacity-90 leading-relaxed mb-8 max-w-sm mx-auto">
                    Nuestra página web, donde encontrarás información valiosa, como agendas y rutas de recolección en Chiriquí.
                </p>
                <a href="<?= $base ?>home" class="border-2 border-white/60 hover:border-white text-white px-8 py-2 rounded-full font-semibold transition-all hover:bg-white/10 active:scale-95">
                    Volver al Inicio
                </a>
            </div>
        </section>

        <!-- Right Panel (Forms) -->
        <section class="w-full md:w-[50%] flex flex-col items-center justify-center p-8 bg-white relative z-10">
            <div class="w-full max-w-[400px] flex flex-col items-center">
                <div class="mb-8 w-full flex justify-center">
                    <img alt="smartSACH Logo" class="h-16 md:h-20 w-auto object-contain" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCEyFjWknnizd612_uBEc-h8DbkWzMAM0nNU1rgJOYVXuoui8h1AApLST-ModXuX7iuTZh4z-5XVtPBQe9I7Wt6o5Dv_stmPWZaofVzM9DRHCx5OUxgzRikurRlzCW_NKLnbDvuZ0uYTfNCfxeEf26UHdMJDauIUtiu591iYMxVdtF9hz-S9rWBxsPBk4dGy2cveq_x-sAj9G8SU213IU2tpjVjBgFQv5WkPXOreBNurEKhRbIlEAbEG5pKNvlZzTnsSWedNQChdts"/>
                </div>

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

                <div id="js-error" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-full text-center text-sm w-full mb-4"></div>
                <div id="js-success" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-full text-center text-sm w-full mb-4"></div>

                <!-- Formulario invisible para enviar sesión PHP -->
                <form id="php-session-form" action="<?= $base ?>auth?action=login" method="POST" style="display: none;">
                    <input type="hidden" name="email" id="php_email">
                    <input type="hidden" name="auth_id" id="php_auth_id">
                </form>

                <!-- Login Container -->
                <div class="<?= $showRegister ? 'auth-hidden' : 'auth-visible' ?> w-full text-center" id="login-container">
                    <p class="text-on-surface-variant mb-6">Inicie sesión para acceder a la plataforma</p>
                    <form id="login-form" class="space-y-4">
                        <div>
                            <input id="login-email" class="w-full bg-[#9bb2a8]/30 border-none rounded-full py-3.5 px-6 text-on-surface placeholder:text-on-surface/50 focus:ring-2 focus:ring-primary transition-all outline-none text-center" placeholder="Correo electrónico" type="email" required/>
                        </div>
                        <div>
                            <input id="login-password" class="w-full bg-[#9bb2a8]/30 border-none rounded-full py-3.5 px-6 text-on-surface placeholder:text-on-surface/50 focus:ring-2 focus:ring-primary transition-all outline-none text-center" placeholder="Contraseña" type="password" required/>
                        </div>
                        <button type="submit" id="btn-login" class="w-full bg-[#1e4638] py-3 rounded-full text-white font-semibold hover:bg-primary transition-all shadow-lg active:scale-95 mt-4">
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
                    <form id="register-form" class="space-y-4">
                        <input id="reg-nombre" class="w-full bg-[#9bb2a8]/30 border-none rounded-full py-3 px-6 text-on-surface focus:ring-2 focus:ring-primary outline-none" placeholder="Nombre Completo" type="text" required/>
                        <input id="reg-email" class="w-full bg-[#9bb2a8]/30 border-none rounded-full py-3 px-6 text-on-surface focus:ring-2 focus:ring-primary outline-none" placeholder="Correo electrónico" type="email" required/>
                        <input id="reg-password" class="w-full bg-[#9bb2a8]/30 border-none rounded-full py-3 px-6 text-on-surface focus:ring-2 focus:ring-primary outline-none" placeholder="Contraseña (mínimo 6 caracteres)" type="password" required/>
                        <button type="submit" id="btn-register" class="w-full bg-secondary py-3 rounded-full text-white font-semibold hover:brightness-110 shadow-md transition-all mt-4">
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

        function showMessage(type, message) {
            const errEl = document.getElementById('js-error');
            const succEl = document.getElementById('js-success');
            errEl.classList.add('hidden');
            succEl.classList.add('hidden');
            if (type === 'error') {
                errEl.textContent = message;
                errEl.classList.remove('hidden');
            } else {
                succEl.textContent = message;
                succEl.classList.remove('hidden');
            }
        }
    </script>
    
    <script type="module">
        import { supabase } from '<?= $base ?>frontend/src/services/supabaseClient.js';

        // Asegurar que si el usuario visita esta página, su sesión de Supabase frontend esté limpia
        supabase.auth.signOut();

        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-login');
            btn.disabled = true;
            btn.textContent = "Iniciando sesión...";

            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;

            const { data, error } = await supabase.auth.signInWithPassword({
                email: email,
                password: password
            });

            if (error) {
                showMessage('error', error.message);
                btn.disabled = false;
                btn.textContent = "Ingresar";
            } else {
                // Notificar al backend PHP
                document.getElementById('php_email').value = data.user.email;
                document.getElementById('php_auth_id').value = data.user.id;
                document.getElementById('php-session-form').submit();
            }
        });

        document.getElementById('register-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-register');
            btn.disabled = true;
            btn.textContent = "Registrando...";

            const nombre = document.getElementById('reg-nombre').value;
            const email = document.getElementById('reg-email').value;
            const password = document.getElementById('reg-password').value;

            // En Supabase, el registro pasa la metadata extra
            const { data, error } = await supabase.auth.signUp({
                email: email,
                password: password,
                options: {
                    data: {
                        nombre: nombre
                    }
                }
            });

            if (error) {
                showMessage('error', error.message);
                btn.disabled = false;
                btn.textContent = "Registrarse";
            } else {
                // Dependiendo de si se requiere confirmación por email
                if (data.user && data.user.identities && data.user.identities.length === 0) {
                    showMessage('error', 'Este correo ya existe en Supabase o se requiere verificación.');
                    btn.disabled = false;
                    btn.textContent = "Registrarse";
                } else {
                    showMessage('success', 'Registro completado en Supabase. Si confirmaste, inicia sesión.');
                    setTimeout(() => { toggleAuth(true); }, 2000);
                }
            }
        });
    </script>
</body>
</html>


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
            <div class="hidden md:block absolute right-[-100px] top-0 h-full w-[250px] bg-primary rounded-[0_50%_50%_0] z-10"></div>
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
                <div class="<?= $showRegister ? 'hidden' : '' ?> w-full text-center" id="login-container">
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
                <div class="<?= $showRegister ? '' : 'hidden' ?> w-full" id="register-container">
                    <div class="mb-6 text-center">
                        <button class="flex items-center gap-1 text-on-surface-variant hover:text-primary mb-4 font-semibold mx-auto focus:outline-none" onclick="toggleAuth(true)">
                            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Volver
                        </button>
                        <h3 class="text-2xl font-bold text-primary">Crear cuenta</h3>
                    </div>
                    <form id="register-form" class="space-y-3">
                        <!-- Nombre y Apellido -->
                        <div>
                            <div class="flex gap-2">
                                <input id="reg-nombre" class="w-1/2 bg-[#9bb2a8]/30 border-none rounded-full py-3 px-6 text-on-surface placeholder:text-on-surface/50 focus:ring-2 focus:ring-primary outline-none" placeholder="Nombre" type="text"/>
                                <input id="reg-apellido" class="w-1/2 bg-[#9bb2a8]/30 border-none rounded-full py-3 px-6 text-on-surface placeholder:text-on-surface/50 focus:ring-2 focus:ring-primary outline-none" placeholder="Apellido" type="text"/>
                            </div>
                            <div class="flex gap-2 mt-1">
                                <p id="err-nombre" class="hidden text-red-500 text-xs w-1/2 px-4"></p>
                                <p id="err-apellido" class="hidden text-red-500 text-xs w-1/2 px-4"></p>
                            </div>
                        </div>
                        <!-- Cédula -->
                        <div>
                            <input id="reg-cedula" maxlength="10" class="w-full bg-[#9bb2a8]/30 border-none rounded-full py-3 px-6 text-on-surface placeholder:text-on-surface/50 focus:ring-2 focus:ring-primary outline-none" placeholder="Cédula (X-XXX-XXX ó XX-XXX-XXX)" type="text"/>
                            <p id="err-cedula" class="hidden text-red-500 text-xs mt-1 px-4"></p>
                        </div>
                        <!-- Celular -->
                        <div>
                            <input id="reg-telefono" maxlength="9" class="w-full bg-[#9bb2a8]/30 border-none rounded-full py-3 px-6 text-on-surface placeholder:text-on-surface/50 focus:ring-2 focus:ring-primary outline-none" placeholder="Celular (XXXX-XXXX)" type="text"/>
                            <p id="err-telefono" class="hidden text-red-500 text-xs mt-1 px-4"></p>
                        </div>
                        <!-- Dirección -->
                        <div>
                            <input id="reg-direccion" class="w-full bg-[#9bb2a8]/30 border-none rounded-full py-3 px-6 text-on-surface placeholder:text-on-surface/50 focus:ring-2 focus:ring-primary outline-none" placeholder="Dirección" type="text"/>
                            <p id="err-direccion" class="hidden text-red-500 text-xs mt-1 px-4"></p>
                        </div>
                        <!-- Correo -->
                        <div>
                            <input id="reg-email" class="w-full bg-[#9bb2a8]/30 border-none rounded-full py-3 px-6 text-on-surface placeholder:text-on-surface/50 focus:ring-2 focus:ring-primary outline-none" placeholder="Correo electrónico" type="email"/>
                            <p class="text-on-surface/40 text-xs mt-1 px-4">Debe contener @ &mdash; Ej: tucorreo@gmail.com</p>
                            <p id="err-email" class="hidden text-red-500 text-xs mt-0.5 px-4"></p>
                        </div>
                        <!-- Contraseña -->
                        <div>
                            <input id="reg-password" class="w-full bg-[#9bb2a8]/30 border-none rounded-full py-3 px-6 text-on-surface placeholder:text-on-surface/50 focus:ring-2 focus:ring-primary outline-none" placeholder="Contraseña" type="password"/>
                            <p class="text-on-surface/40 text-xs mt-1 px-4">Mín. 8 caracteres &bull; Mayúscula &bull; Minúscula &bull; Número &bull; Símbolo ($ &amp; # !)</p>
                            <p id="err-password" class="hidden text-red-500 text-xs mt-0.5 px-4"></p>
                        </div>
                        <!-- Confirmar Contraseña -->
                        <div>
                            <input id="reg-password-confirm" class="w-full bg-[#9bb2a8]/30 border-none rounded-full py-3 px-6 text-on-surface placeholder:text-on-surface/50 focus:ring-2 focus:ring-primary outline-none" placeholder="Confirmar contraseña" type="password"/>
                            <p id="err-password-confirm" class="hidden text-red-500 text-xs mt-1 px-4"></p>
                        </div>
                        <button type="submit" id="btn-register" class="w-full bg-secondary py-3 rounded-full text-white font-semibold hover:brightness-110 shadow-md transition-all mt-2 active:scale-95">
                            Registrarse
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <script>
        function toggleAuth(showLogin) {
            const login    = document.getElementById('login-container');
            const register = document.getElementById('register-container');
            if (showLogin) {
                register.classList.add('hidden');
                login.classList.remove('hidden');
            } else {
                login.classList.add('hidden');
                register.classList.remove('hidden');
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

        // --- Helpers de errores por campo ---
        function showFieldError(fieldId, message) {
            const errEl = document.getElementById('err-' + fieldId);
            if (errEl) {
                errEl.textContent = message;
                errEl.classList.remove('hidden');
            }
        }

        function clearAllErrors() {
            document.querySelectorAll('[id^="err-"]').forEach(el => {
                el.textContent = '';
                el.classList.add('hidden');
            });
        }

        // --- Máscara de Cédula: solo dígitos y guiones (X-XXX-XXX ó XX-XXX-XXX) ---
        const cedulaInput = document.getElementById('reg-cedula');
        cedulaInput.addEventListener('input', function() {
            let val = this.value.replace(/[^0-9-]/g, ''); // Solo dígitos y guiones
            val = val.replace(/--+/g, '-');               // Sin guiones dobles
            val = val.replace(/^-/, '');                  // Sin guión al inicio
            if (val.length > 10) val = val.slice(0, 10); // Máx 10 chars (XX-XXX-XXX)
            this.value = val;
        });

        // --- Máscara de Celular: auto-formato XXXX-XXXX ---
        const telefonoInput = document.getElementById('reg-telefono');
        telefonoInput.addEventListener('input', function() {
            let digits = this.value.replace(/\D/g, '');
            if (digits.length > 4) {
                this.value = digits.slice(0, 4) + '-' + digits.slice(4, 8);
            } else {
                this.value = digits;
            }
        });

        document.getElementById('register-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            clearAllErrors();

            const btn = document.getElementById('btn-register');
            let hasError = false;

            // Recoger valores
            const nombre       = document.getElementById('reg-nombre').value.trim();
            const apellido     = document.getElementById('reg-apellido').value.trim();
            const cedula       = document.getElementById('reg-cedula').value.trim();
            const telefono     = document.getElementById('reg-telefono').value.trim();
            const direccion    = document.getElementById('reg-direccion').value.trim();
            const email        = document.getElementById('reg-email').value.trim();
            const password     = document.getElementById('reg-password').value;
            const passConfirm  = document.getElementById('reg-password-confirm').value;

            // --- Validaciones ---
            if (!nombre)   { showFieldError('nombre',   'El nombre es obligatorio.');   hasError = true; }
            if (!apellido) { showFieldError('apellido', 'El apellido es obligatorio.'); hasError = true; }

            // Regex cédula: X-XXX-XXX ó XX-XXX-XXX
            const cedulaRegex = /^\d{1,2}-\d{3}-\d{3}$/;
            if (!cedula) {
                showFieldError('cedula', 'La cédula es obligatoria.');
                hasError = true;
            } else if (!cedulaRegex.test(cedula)) {
                showFieldError('cedula', 'Formato inválido. Debe ser X-XXX-XXX ó XX-XXX-XXX.');
                hasError = true;
            }

            // Regex celular: XXXX-XXXX
            const telefonoRegex = /^\d{4}-\d{4}$/;
            if (!telefono) {
                showFieldError('telefono', 'El número de celular es obligatorio.');
                hasError = true;
            } else if (!telefonoRegex.test(telefono)) {
                showFieldError('telefono', 'Formato inválido. Debe ser XXXX-XXXX.');
                hasError = true;
            }

            if (!direccion) { showFieldError('direccion', 'La dirección es obligatoria.'); hasError = true; }

            // Validación de email con regex + presencia de @
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email) {
                showFieldError('email', 'El correo electrónico es obligatorio.');
                hasError = true;
            } else if (!email.includes('@') || !emailRegex.test(email)) {
                showFieldError('email', 'Ingresa un correo válido (debe contener @).');
                hasError = true;
            }

            // Validación de contraseña: mínimo 8 chars + complejidad
            if (!password) {
                showFieldError('password', 'La contraseña es obligatoria.');
                hasError = true;
            } else {
                const faltantes = [];
                if (password.length < 8)            faltantes.push('al menos 8 caracteres');
                if (!/[A-Z]/.test(password))         faltantes.push('una letra mayúscula (A-Z)');
                if (!/[a-z]/.test(password))         faltantes.push('una letra minúscula (a-z)');
                if (!/[0-9]/.test(password))         faltantes.push('un número (0-9)');
                if (!/[$&()#!@%*?_\-]/.test(password)) faltantes.push('un símbolo ($ & # ! @ % *)');

                if (faltantes.length > 0) {
                    showFieldError('password', 'Tu contraseña necesita: ' + faltantes.join(', ') + '.');
                    hasError = true;
                }
            }

            // Confirmación de contraseña
            if (!passConfirm) {
                showFieldError('password-confirm', 'Debes confirmar tu contraseña.');
                hasError = true;
            } else if (password !== passConfirm) {
                showFieldError('password-confirm', 'Las contraseñas no coinciden.');
                hasError = true;
            }

            // Detener si hay errores
            if (hasError) return;

            btn.disabled = true;
            btn.textContent = 'Registrando...';

            // Registro en Supabase con metadata ampliada
            const { data, error } = await supabase.auth.signUp({
                email: email,
                password: password,
                options: {
                    data: {
                        nombre:    nombre,
                        apellido:  apellido,
                        cedula:    cedula,
                        telefono:  telefono,
                        direccion: direccion
                    }
                }
            });

            if (error) {
                // Mapear errores de Supabase a mensajes amigables en español
                if (error.message.includes('already registered') || error.message.includes('User already registered')) {
                    showFieldError('email', 'Este correo ya está registrado. Intenta iniciar sesión.');
                } else if (error.message.includes('Password should be') || error.message.includes('password')) {
                    showFieldError('password', 'Contraseña inválida. Recuerda incluir: mayúscula, minúscula, número y símbolo ($ & # !).');
                } else if (error.message.includes('invalid email') || error.message.includes('Invalid email')) {
                    showFieldError('email', 'El correo no tiene un formato válido. Asegúrate de incluir el @.');
                } else {
                    showMessage('error', 'Error al registrar: ' + error.message);
                }
                btn.disabled = false;
                btn.textContent = 'Registrarse';
            } else {
                if (data.user && data.user.identities && data.user.identities.length === 0) {
                    showFieldError('email', 'Este correo ya existe. Verifica tu bandeja de entrada o inicia sesión.');
                    btn.disabled = false;
                    btn.textContent = 'Registrarse';
                } else {
                    showMessage('success', '¡Registro exitoso! Revisa tu correo para confirmar tu cuenta.');
                    setTimeout(() => { toggleAuth(true); }, 2500);
                    btn.disabled = false;
                    btn.textContent = 'Registrarse';
                }
            }
        });
    </script>
</body>
</html>


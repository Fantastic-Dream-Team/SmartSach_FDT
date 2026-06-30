<?php
require_once __DIR__ . '/../components/header.php';

// Determinar ruta base para enlaces
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($base, -1) !== '/') {
    $base .= '/';
}
?>

<div class="max-w-[1200px] mx-auto px-6 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-primary">Mi Perfil de Trabajo</h1>
        <p class="text-on-surface-variant text-sm mt-1">Detalles de su cuenta y credenciales operativas de conductor.</p>
    </div>

    <div class="max-w-md mx-auto">
        <div class="bg-white p-8 rounded-xl border border-surface-container-high shadow-sm text-center space-y-6">
            
            <!-- Icono Conductor -->
            <div class="w-32 h-32 bg-primary/10 rounded-full flex items-center justify-center text-primary mx-auto shadow-inner border border-primary/20">
                <span class="material-symbols-outlined text-6xl font-bold">local_shipping</span>
            </div>

            <div>
                <span class="bg-blue-100 text-blue-800 text-[10px] font-bold uppercase px-3 py-1 rounded-full">Personal Operativo</span>
                <h3 class="text-2xl font-black text-on-surface mt-3"><?= htmlspecialchars($user['nombre']) ?></h3>
                <p class="text-xs text-on-surface-variant"><?= htmlspecialchars($user['email']) ?></p>
            </div>

            <!-- Restricción Read-Only Prominente -->
            <div class="bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-lg text-left text-xs leading-relaxed flex items-start gap-2.5">
                <span class="material-symbols-outlined text-amber-600 text-lg flex-shrink-0">info</span>
                <div>
                    <strong>Información de Cuenta de Solo Lectura:</strong>
                    <p class="mt-1 opacity-90">Por motivos de seguridad laboral y trazabilidad de rutas, sus datos personales no pueden ser editados directamente.</p>
                    <p class="mt-2 font-bold uppercase text-[10px] text-amber-700">Para modificaciones, contacte a su Gestor.</p>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../components/footer.php';
?>

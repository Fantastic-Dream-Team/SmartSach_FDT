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

            <form class="space-y-4 text-left mt-6" onsubmit="return false;">
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">Nombre Completo</label>
                    <input type="text" value="<?= htmlspecialchars(trim(($user['nombre'] ?? '') . ' ' . ($user['apellido'] ?? ''))) ?>" readonly disabled class="w-full bg-surface-container/40 border-none rounded-lg py-2.5 px-4 text-sm text-on-surface cursor-not-allowed outline-none" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">Cédula</label>
                    <input type="text" value="<?= htmlspecialchars($user['cedula'] ?? 'N/A') ?>" readonly disabled class="w-full bg-surface-container/40 border-none rounded-lg py-2.5 px-4 text-sm text-on-surface cursor-not-allowed outline-none font-mono" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">Teléfono</label>
                    <input type="text" value="<?= htmlspecialchars($user['telefono'] ?? 'N/A') ?>" readonly disabled class="w-full bg-surface-container/40 border-none rounded-lg py-2.5 px-4 text-sm text-on-surface cursor-not-allowed outline-none font-mono" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">Correo Electrónico</label>
                    <input type="email" value="<?= htmlspecialchars($user['email'] ?? $user['correo_electronico'] ?? 'N/A') ?>" readonly disabled class="w-full bg-surface-container/40 border-none rounded-lg py-2.5 px-4 text-sm text-on-surface cursor-not-allowed outline-none" />
                </div>
            </form>
            
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../components/footer.php';
?>

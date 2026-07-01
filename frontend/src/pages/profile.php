<?php
require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../models/ReporteIncidencia.php';

// Determinar ruta base para enlaces
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($base, -1) !== '/') {
    $base .= '/';
}

// Obtener contadores
$reporteModelHelper = new ReporteIncidencia();
$totalReportes = count($reporteModelHelper->findByUsuarioId($user['usuario_id']));

$avatarUrl = 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png'; // Avatar por defecto
?>

<div class="max-w-[1200px] mx-auto px-6 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-primary">Mi Perfil</h1>
        <p class="text-on-surface-variant text-sm mt-1">Gestione sus credenciales de acceso y sus ubicaciones de servicio.</p>
    </div>

    <!-- Alertas de Sesión -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-6 p-4 bg-green-50 text-green-800 border-l-4 border-[#00c46a] rounded-r-lg text-sm">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-6 p-4 bg-red-50 text-red-800 border-l-4 border-red-500 rounded-r-lg text-sm">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Panel Izquierdo: Información del Usuario en modo Lectura/Edición -->
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm text-center">
                <!-- Foto -->
                <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-primary/20 mx-auto shadow-md relative mb-4">
                    <img src="<?= $avatarUrl ?>" alt="Foto de perfil" class="w-full h-full object-cover"/>
                </div>
                <h3 class="text-xl font-bold text-on-surface" id="view-fullname"><?= htmlspecialchars($user['nombre'] . ' ' . ($user['apellido'] ?? '')) ?></h3>
                <p class="text-xs text-on-surface-variant mb-6"><?= htmlspecialchars($user['correo_electronico']) ?></p>
                
                <!-- Contadores Dinámicos -->
                <div class="bg-surface-container/30 p-4 rounded-xl border border-surface-container/60 mb-6">
                    <span class="block text-2xl font-black text-primary"><?= $totalReportes ?></span>
                    <span class="text-[10px] text-on-surface-variant uppercase font-semibold">Reportes Generados</span>
                </div>

                <!-- MODO LECTURA -->
                <div id="profile-read-mode" class="text-left space-y-4">
                    <div class="border-b border-surface-container/60 pb-2">
                        <span class="text-xs font-semibold text-primary uppercase block">Correo electrónico</span>
                        <span class="text-sm text-on-surface font-medium"><?= htmlspecialchars($user['correo_electronico']) ?></span>
                    </div>
                    <div class="border-b border-surface-container/60 pb-2">
                        <span class="text-xs font-semibold text-primary uppercase block">Teléfono</span>
                        <span class="text-sm text-on-surface font-medium"><?= htmlspecialchars($user['telefono'] ?: 'No registrado') ?></span>
                    </div>
                    <div class="border-b border-surface-container/60 pb-2">
                        <span class="text-xs font-semibold text-primary uppercase block">Cédula</span>
                        <span class="text-sm text-on-surface font-medium"><?= htmlspecialchars($user['cedula'] ?: 'No registrada') ?></span>
                    </div>
                    <div class="border-b border-surface-container/60 pb-2">
                        <span class="text-xs font-semibold text-primary uppercase block">Dirección Base</span>
                        <span class="text-sm text-on-surface font-medium"><?= htmlspecialchars($user['direccion'] ?: 'No registrada') ?></span>
                    </div>
                    <button onclick="enableEditMode()" class="w-full bg-[#1e4638] hover:bg-primary text-white py-2.5 rounded-full font-bold shadow-md transition-all active:scale-95 text-sm mt-4 flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-sm">edit</span>
                        Modificar datos
                    </button>
                </div>

                <!-- MODO EDICIÓN (Oculto por defecto) -->
                <form action="<?= $base ?>profile?action=update" method="POST" id="profile-edit-form" onsubmit="return validateProfileEdit(event)" class="hidden text-left space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Nombre:</label>
                        <input name="nombre" id="edit-nombre" value="<?= htmlspecialchars($user['nombre'] ?? '') ?>" type="text" required class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-sm text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                        <p id="err-edit-nombre" class="hidden text-red-500 text-[10px] mt-1 px-4"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Apellido:</label>
                        <input name="apellido" id="edit-apellido" value="<?= htmlspecialchars($user['apellido'] ?? '') ?>" type="text" required class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-sm text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                        <p id="err-edit-apellido" class="hidden text-red-500 text-[10px] mt-1 px-4"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Teléfono:</label>
                        <input name="telefono" id="edit-telefono" maxlength="9" value="<?= htmlspecialchars($user['telefono'] ?? '') ?>" type="text" class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-sm text-on-surface focus:ring-2 focus:ring-primary outline-none" placeholder="XXXX-XXXX"/>
                        <p id="err-edit-telefono" class="hidden text-red-500 text-[10px] mt-1 px-4"></p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Cédula (Solo lectura):</label>
                        <input value="<?= htmlspecialchars($user['cedula'] ?? '') ?>" type="text" disabled class="w-full bg-surface-container/30 border-none rounded-full py-2.5 px-4 text-sm text-on-surface-variant cursor-not-allowed outline-none"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Dirección Base:</label>
                        <input name="direccion" id="edit-direccion" value="<?= htmlspecialchars($user['direccion'] ?? '') ?>" type="text" class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-sm text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                        <p id="err-edit-direccion" class="hidden text-red-500 text-[10px] mt-1 px-4"></p>
                    </div>
                    
                    <div class="flex gap-2 pt-2">
                        <button type="button" onclick="disableEditMode()" class="w-1/2 bg-surface-container text-on-surface py-2.5 rounded-full font-bold transition-all text-xs text-center">
                            Cancelar
                        </button>
                        <button type="submit" class="w-1/2 bg-secondary hover:bg-[#00ab5d] text-white py-2.5 rounded-full font-bold shadow-md transition-all active:scale-95 text-xs">
                            Confirmar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Panel Derecho: Direcciones y Colapsable de nueva dirección -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Direcciones Actuales -->
            <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">holiday_village</span>
                        Mis Rutas (Ubicaciones)
                    </h3>
                    
                    <button onclick="toggleRouteForm()" class="bg-secondary hover:bg-[#00ab5d] text-white px-4 py-2 rounded-full text-xs font-bold shadow-sm transition-all flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm" id="toggle-icon">add</span>
                        Modificar rutas
                    </button>
                </div>

                <?php if (empty($ubicaciones)): ?>
                    <p class="text-on-surface-variant text-sm py-4">No tiene ubicaciones de recolección asociadas a su cuenta.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($ubicaciones as $u): ?>
                            <div class="bg-surface-container/20 p-4 rounded-lg border border-surface-container flex justify-between items-start">
                                <div>
                                    <div class="font-bold text-on-surface text-sm"><?= htmlspecialchars($u['nombre_referencia']) ?></div>
                                    <div class="text-[11px] text-on-surface-variant mt-1"><?= htmlspecialchars($u['descripcion_direccion'] ?: 'Sin referencias') ?></div>
                                    <div class="text-[10px] font-mono text-on-surface-variant/70 mt-2">
                                        Lat: <?= htmlspecialchars($u['latitud']) ?><br>
                                        Lon: <?= htmlspecialchars($u['longitud']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Formulario colapsable para Añadir Dirección -->
            <div id="add-route-section" class="hidden bg-white p-6 rounded-xl border border-surface-container-high shadow-md transition-all">
                <h3 class="text-lg font-bold text-primary mb-1 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">add_location_alt</span>
                    Marcar Nueva Ubicación
                </h3>
                <p class="text-on-surface-variant text-[11px] mb-4">Haga clic en el mapa de Chiriquí para colocar un marcador y registrar las coordenadas de recolección.</p>

                <form action="<?= $base ?>profile?action=add_route" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-primary mb-1 uppercase">Nombre de Referencia:</label>
                            <input name="nombre" placeholder="Ej. Casa, Trabajo" type="text" required class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary mb-1 uppercase">Descripción o color de fachada:</label>
                            <input name="descripcion" placeholder="Ej. Casa verde de dos pisos" type="text" class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-primary mb-1 uppercase">Latitud:</label>
                            <input id="form-lat" name="latitud" type="text" readonly required placeholder="Seleccione en el mapa" class="w-full bg-surface-container/40 border-none rounded-full py-2.5 px-4 text-sm text-on-surface cursor-not-allowed outline-none font-mono"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary mb-1 uppercase">Longitud:</label>
                            <input id="form-lon" name="longitud" type="text" readonly required placeholder="Seleccione en el mapa" class="w-full bg-surface-container/40 border-none rounded-full py-2.5 px-4 text-sm text-on-surface cursor-not-allowed outline-none font-mono"/>
                        </div>
                    </div>

                    <!-- Mapa de Selección -->
                    <div class="relative w-full h-[300px] rounded-lg overflow-hidden border border-surface-container-high shadow-inner">
                        <div id="selection-map" class="w-full h-full"></div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="bg-secondary hover:bg-[#00ab5d] text-white px-8 py-3 rounded-full font-bold shadow-md transition-all active:scale-95 flex items-center gap-2">
                            <span class="material-symbols-outlined">save</span>
                            Guardar Ubicación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    var selectionMapInitialized = false;
    var map = null;
    var marker = null;

    function enableEditMode() {
        document.getElementById('profile-read-mode').classList.add('hidden');
        document.getElementById('profile-edit-form').classList.remove('hidden');
    }

    function disableEditMode() {
        document.getElementById('profile-edit-form').classList.add('hidden');
        document.getElementById('profile-read-mode').classList.remove('hidden');
        clearProfileErrors();
    }

    function clearProfileErrors() {
        document.querySelectorAll('[id^="err-edit-"]').forEach(el => {
            el.textContent = '';
            el.classList.add('hidden');
        });
    }

    // Máscara de Celular (igual a registro)
    const telInput = document.getElementById('edit-telefono');
    telInput.addEventListener('input', function() {
        let digits = this.value.replace(/\D/g, '');
        if (digits.length > 4) {
            this.value = digits.slice(0, 4) + '-' + digits.slice(4, 8);
        } else {
            this.value = digits;
        }
    });

    function validateProfileEdit(e) {
        clearProfileErrors();
        let hasError = false;

        const nombre = document.getElementById('edit-nombre').value.trim();
        const apellido = document.getElementById('edit-apellido').value.trim();
        const telefono = document.getElementById('edit-telefono').value.trim();
        const direccion = document.getElementById('edit-direccion').value.trim();

        if (!nombre) {
            document.getElementById('err-edit-nombre').textContent = 'El nombre es obligatorio.';
            document.getElementById('err-edit-nombre').classList.remove('hidden');
            hasError = true;
        }
        if (!apellido) {
            document.getElementById('err-edit-apellido').textContent = 'El apellido es obligatorio.';
            document.getElementById('err-edit-apellido').classList.remove('hidden');
            hasError = true;
        }
        
        const telRegex = /^\d{4}-\d{4}$/;
        if (telefono && !telRegex.test(telefono)) {
            document.getElementById('err-edit-telefono').textContent = 'Celular inválido. Debe ser XXXX-XXXX.';
            document.getElementById('err-edit-telefono').classList.remove('hidden');
            hasError = true;
        }

        if (hasError) {
            e.preventDefault();
            return false;
        }

        return confirm('¿Está seguro de que desea guardar los cambios en su perfil?');
    }

    function toggleRouteForm() {
        var section = document.getElementById('add-route-section');
        var icon = document.getElementById('toggle-icon');
        
        if (section.classList.contains('hidden')) {
            section.classList.remove('hidden');
            icon.innerText = "close";
            
            if (!selectionMapInitialized) {
                setTimeout(function() {
                    var defaultLat = 8.42867;
                    var defaultLon = -82.42875;

                    map = L.map('selection-map').setView([defaultLat, defaultLon], 13);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);

                    map.on('click', function(e) {
                        var lat = e.latlng.lat;
                        var lon = e.latlng.lng;

                        document.getElementById('form-lat').value = lat.toFixed(8);
                        document.getElementById('form-lon').value = lon.toFixed(8);

                        if (marker === null) {
                            marker = L.marker([lat, lon]).addTo(map);
                        } else {
                            marker.setLatLng([lat, lon]);
                        }
                    });

                    selectionMapInitialized = true;
                }, 100);
            }
        } else {
            section.classList.add('hidden');
            icon.innerText = "add";
        }
    }
</script>

<?php
require_once __DIR__ . '/../components/footer.php';
?>

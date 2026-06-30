<?php
require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../models/Reporte.php';

// Determinar ruta base para enlaces
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($base, -1) !== '/') {
    $base .= '/';
}

// Obtener contadores del modelo de Reportes
$reporteModelHelper = new Reporte();
$totalReportes = $reporteModelHelper->getReportesCountByUsuario($user['id']);
$totalNoLeidos = $reporteModelHelper->getUnreadRepliesCount($user['id']);

$avatarUrl = !empty($user['foto_perfil']) ? $base . $user['foto_perfil'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
?>

<div class="max-w-[1200px] mx-auto px-6 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-primary">Mi Perfil</h1>
        <p class="text-on-surface-variant text-sm mt-1">Gestione sus credenciales de acceso y sus direcciones de recogida.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Panel Izquierdo: Información del Usuario y Notificaciones -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm text-center">
                <!-- Foto -->
                <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-primary/20 mx-auto shadow-md relative mb-4">
                    <img src="<?= $avatarUrl ?>" alt="Foto de perfil" class="w-full h-full object-cover"/>
                </div>
                <h3 class="text-xl font-bold text-on-surface"><?= htmlspecialchars($user['nombre']) ?></h3>
                <p class="text-xs text-on-surface-variant"><?= htmlspecialchars($user['email']) ?></p>
                
                <!-- Contadores Dinámicos (Notificaciones) -->
                <div class="grid grid-cols-2 gap-4 mt-6 bg-surface-container/30 p-4 rounded-xl border border-surface-container/60">
                    <div class="text-center">
                        <span class="block text-2xl font-black text-primary"><?= $totalReportes ?></span>
                        <span class="text-[10px] text-on-surface-variant uppercase font-semibold">Reportes Creados</span>
                    </div>
                    <div class="text-center border-l border-surface-container-high">
                        <span class="block text-2xl font-black <?= ($totalNoLeidos > 0) ? 'text-red-500 animate-pulse' : 'text-primary' ?>"><?= $totalNoLeidos ?></span>
                        <span class="text-[10px] text-on-surface-variant uppercase font-semibold">Respuestas Nuevas</span>
                    </div>
                </div>

                <!-- Formulario de Edición -->
                <form action="<?= $base ?>profile" method="POST" enctype="multipart/form-data" class="mt-8 text-left space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Nombre:</label>
                        <input name="nombre" value="<?= htmlspecialchars($user['nombre']) ?>" type="text" required class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-sm text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Email:</label>
                        <input name="email" value="<?= htmlspecialchars($user['email']) ?>" type="email" required class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-sm text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Cambiar Foto:</label>
                        <input name="foto_perfil" type="file" accept="image/*" class="block w-full text-xs text-on-surface-variant file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer"/>
                    </div>
                    <button type="submit" class="w-full bg-[#1e4638] hover:bg-primary text-white py-2.5 rounded-full font-bold shadow-md transition-all active:scale-95 text-sm mt-2">
                        Guardar Cambios
                    </button>
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
                        Casas Registradas
                    </h3>
                    
                    <!-- Botón para Colapsar/Desplegar Formulario -->
                    <button onclick="toggleRouteForm()" class="bg-secondary hover:bg-[#00ab5d] text-white px-4 py-2 rounded-full text-xs font-bold shadow-sm transition-all flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm" id="toggle-icon">add</span>
                        Nueva Casa
                    </button>
                </div>

                <?php if (empty($rutas)): ?>
                    <p class="text-on-surface-variant text-sm py-4">No tiene direcciones asociadas a su cuenta.</p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($rutas as $r): ?>
                            <div class="bg-surface-container/20 p-4 rounded-lg border border-surface-container flex justify-between items-start">
                                <div>
                                    <div class="font-bold text-on-surface text-sm"><?= htmlspecialchars($r['nombre']) ?></div>
                                    <div class="text-[11px] text-on-surface-variant mt-1"><?= htmlspecialchars($r['descripcion'] ?: 'Sin referencias') ?></div>
                                    <div class="text-[10px] font-mono text-on-surface-variant/70 mt-2">Conductor: <?= htmlspecialchars($r['conductor_nombre'] ?: 'No asignado') ?></div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-bold uppercase <?= ($r['estado'] === 'en_ruta') ? 'bg-amber-100 text-amber-700 animate-pulse' : (($r['estado'] === 'finalizada') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') ?>">
                                        <?= htmlspecialchars($r['estado']) ?>
                                    </span>
                                    <div class="text-xs font-bold text-primary mt-2">$<?= number_format($r['costo'], 2) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Formulario colapsable para Añadir Dirección (Oculto por defecto) -->
            <div id="add-route-section" class="hidden bg-white p-6 rounded-xl border border-surface-container-high shadow-md transition-all">
                <h3 class="text-lg font-bold text-primary mb-1 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">add_location_alt</span>
                    Marcar Nueva Dirección
                </h3>
                <p class="text-on-surface-variant text-[11px] mb-4">Haga clic en el mapa de Chiriquí para colocar un marcador y registrar las coordenadas de recolección.</p>

                <form action="<?= $base ?>profile?action=add_route" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-primary mb-1 uppercase">Nombre de Dirección:</label>
                            <input name="nombre" placeholder="Ej. Casa de campo, Trabajo" type="text" required class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary mb-1 uppercase">Referencias visuales:</label>
                            <input name="descripcion" placeholder="Ej. Frente al taller mecánico" type="text" class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary mb-1 uppercase">Zona de Recolección:</label>
                            <select name="zona_id" required class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none">
                                <option value="">-- Seleccionar Zona --</option>
                                <?php foreach ($zonas as $z): ?>
                                    <option value="<?= $z['id'] ?>"><?= htmlspecialchars($z['nombre_zona']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-primary mb-1 uppercase">Latitud (Automática):</label>
                            <input id="form-lat" name="latitud" type="text" readonly required placeholder="Haz clic en el mapa" class="w-full bg-surface-container/40 border-none rounded-full py-2.5 px-4 text-sm text-on-surface cursor-not-allowed outline-none font-mono"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary mb-1 uppercase">Longitud (Automática):</label>
                            <input id="form-lon" name="longitud" type="text" readonly required placeholder="Haz clic en el mapa" class="w-full bg-surface-container/40 border-none rounded-full py-2.5 px-4 text-sm text-on-surface cursor-not-allowed outline-none font-mono"/>
                        </div>
                    </div>

                    <!-- Mapa de Selección -->
                    <div class="relative w-full h-[300px] rounded-lg overflow-hidden border border-surface-container-high shadow-inner">
                        <div id="selection-map" class="w-full h-full"></div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="bg-secondary hover:bg-[#00ab5d] text-white px-8 py-3 rounded-full font-bold shadow-md transition-all active:scale-95 flex items-center gap-2">
                            <span class="material-symbols-outlined">save</span>
                            Guardar Nueva Casa
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

    function toggleRouteForm() {
        var section = document.getElementById('add-route-section');
        var icon = document.getElementById('toggle-icon');
        
        if (section.classList.contains('hidden')) {
            section.classList.remove('hidden');
            icon.innerText = "close";
            
            // Inicializar mapa de selección si no se ha hecho
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

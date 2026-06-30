<?php
require_once __DIR__ . '/layouts/header.php';

// Determinar ruta base para enlaces
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($base, -1) !== '/') {
    $base .= '/';
}
?>

<div class="max-w-[1200px] mx-auto px-6 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-primary">Consola de Administración</h1>
        <p class="text-on-surface-variant text-sm mt-1">Gestión integral de Zonas de Recolección, plantilla de Conductores y direcciones de Clientes.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Columna Izquierda: Zonas de Recolección & CRUD de Conductores -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Crear Zona de Recolección -->
            <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm">
                <h3 class="text-lg font-bold text-primary mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#00c46a]">lan</span>
                    Crear Zona
                </h3>
                <form action="<?= $base ?>profile?action=create_zone" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Nombre de la Zona:</label>
                        <input name="nombre_zona" placeholder="Ej. David Sur" type="text" required class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Descripción:</label>
                        <input name="descripcion" placeholder="Ej. Urbanizaciones y barriadas del sector sur" type="text" class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                    </div>
                    <button type="submit" class="w-full bg-secondary hover:bg-[#00ab5d] text-white py-2.5 rounded-full font-bold shadow-md transition-all active:scale-95 text-xs">
                        Crear Zona
                    </button>
                </form>
            </div>

            <!-- Registro de Conductor -->
            <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm">
                <h3 class="text-lg font-bold text-primary mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#00c46a]">person_add</span>
                    Registrar Conductor
                </h3>
                <form action="<?= $base ?>profile?action=create_driver" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Nombre Completo:</label>
                        <input name="nombre" placeholder="Ej. Pedro Pérez" type="text" required class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Correo:</label>
                        <input name="email" placeholder="conductor@smartsach.com" type="email" required class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Contraseña:</label>
                        <input name="password" placeholder="Mínimo 6 caracteres" type="password" required class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Zona Asignada:</label>
                        <select name="zona_id" class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none">
                            <option value="">-- Sin Zona --</option>
                            <?php foreach ($zonas as $z): ?>
                                <option value="<?= $z['id'] ?>"><?= htmlspecialchars($z['nombre_zona']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-secondary hover:bg-[#00ab5d] text-white py-2.5 rounded-full font-bold shadow-md transition-all active:scale-95 text-xs">
                        Crear y Asignar Conductor
                    </button>
                </form>
            </div>

            <!-- Listado y Edición de Conductores -->
            <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm">
                <h3 class="text-lg font-bold text-primary mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary font-bold">group</span>
                    Plantilla de Conductores (<?= count($conductores) ?>)
                </h3>
                
                <?php if (empty($conductores)): ?>
                    <p class="text-xs text-on-surface-variant italic">No hay conductores registrados.</p>
                <?php else: ?>
                    <div class="space-y-4 max-h-[350px] overflow-y-auto pr-1">
                        <?php foreach ($conductores as $c): ?>
                            <div class="bg-surface-container/20 p-3 rounded-lg border border-surface-container space-y-2 text-xs">
                                <form action="<?= $base ?>profile?action=update_driver" method="POST" class="space-y-2">
                                    <input type="hidden" name="driver_id" value="<?= $c['id'] ?>"/>
                                    <div>
                                        <label class="text-[9px] uppercase font-bold text-primary block">Nombre:</label>
                                        <input name="nombre" value="<?= htmlspecialchars($c['nombre']) ?>" type="text" required class="w-full bg-white border border-surface-container-high rounded px-2 py-1 text-xs text-on-surface outline-none"/>
                                    </div>
                                    <div>
                                        <label class="text-[9px] uppercase font-bold text-primary block">Correo:</label>
                                        <input name="email" value="<?= htmlspecialchars($c['email']) ?>" type="email" required class="w-full bg-white border border-surface-container-high rounded px-2 py-1 text-xs text-on-surface outline-none"/>
                                    </div>
                                    <div>
                                        <label class="text-[9px] uppercase font-bold text-primary block">Zona Asignada:</label>
                                        <select name="zona_id" class="w-full bg-white border border-surface-container-high rounded px-2 py-1 text-xs text-on-surface outline-none">
                                            <option value="">-- Sin Zona --</option>
                                            <?php foreach ($zonas as $z): ?>
                                                <option value="<?= $z['id'] ?>" <?= ($c['zona_id'] == $z['id']) ? 'selected' : '' ?>><?= htmlspecialchars($z['nombre_zona']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="flex gap-2 justify-end pt-1">
                                        <button type="submit" class="bg-primary text-white px-3 py-1 rounded text-[10px] font-bold hover:bg-primary-dark">
                                            Guardar
                                        </button>
                                </form>
                                <form action="<?= $base ?>profile?action=delete_driver" method="POST" onsubmit="return confirm('¿Seguro que desea dar de baja a este conductor?');" class="inline">
                                    <input type="hidden" name="driver_id" value="<?= $c['id'] ?>"/>
                                    <button type="submit" class="bg-red-50 text-red-600 px-3 py-1 rounded text-[10px] font-bold hover:bg-red-100">
                                        Baja
                                    </button>
                                </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Columna Central y Derecha: Zonas Operacionales y Direcciones de Clientes -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Listado y Toggles de Control de Zonas -->
            <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm">
                <h3 class="text-xl font-bold text-primary mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">local_shipping</span>
                    Control de Zonas Operativas
                </h3>
                <p class="text-on-surface-variant text-xs mb-4">Monitoree o cambie el estado operativo general de cada sector.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($zonas as $z): ?>
                        <?php $bloqueada = ($z['estado'] === 'en_ruta'); ?>
                        <div class="p-4 rounded-xl border flex flex-col justify-between gap-3 <?= $bloqueada ? 'bg-amber-50/20 border-amber-300' : 'bg-surface-container/15 border-surface-container' ?>">
                            <div>
                                <div class="flex justify-between items-start gap-2">
                                    <h4 class="font-extrabold text-sm text-primary"><?= htmlspecialchars($z['nombre_zona']) ?></h4>
                                    <span class="text-[9px] font-bold px-2 py-0.5 rounded-full uppercase <?= $bloqueada ? 'bg-amber-600 text-white animate-pulse' : ($z['estado'] === 'finalizada' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') ?>">
                                        <?= htmlspecialchars($z['estado']) ?>
                                    </span>
                                </div>
                                <p class="text-[11px] text-on-surface-variant/80 mt-1"><?= htmlspecialchars($z['descripcion'] ?: 'Sin descripción.') ?></p>
                                <p class="text-[10px] text-primary/80 mt-2 font-bold flex items-center gap-0.5">
                                    <span class="material-symbols-outlined text-xs">person</span>
                                    Conductor: <?= htmlspecialchars($z['conductor_nombre'] ?: 'Ninguno asignado') ?>
                                </p>
                            </div>

                            <div class="flex items-center gap-1 border-t border-surface-container/60 pt-2 text-[10px]">
                                <span class="text-on-surface-variant font-bold uppercase mr-1">Forzar:</span>
                                
                                <form action="<?= $base ?>profile?action=toggle_zone" method="POST" class="inline">
                                    <input type="hidden" name="zona_id" value="<?= $z['id'] ?>"/>
                                    <input type="hidden" name="estado" value="inactiva"/>
                                    <button type="submit" <?= ($z['estado'] === 'inactiva') ? 'disabled' : '' ?> class="bg-red-50 text-red-700 hover:bg-red-100 px-2 py-1 rounded font-bold disabled:opacity-40 disabled:cursor-not-allowed">
                                        Apagar
                                    </button>
                                </form>

                                <form action="<?= $base ?>profile?action=toggle_zone" method="POST" class="inline">
                                    <input type="hidden" name="zona_id" value="<?= $z['id'] ?>"/>
                                    <input type="hidden" name="estado" value="en_ruta"/>
                                    <button type="submit" <?= ($z['estado'] === 'en_ruta') ? 'disabled' : '' ?> class="bg-green-50 text-green-700 hover:bg-green-100 px-2 py-1 rounded font-bold disabled:opacity-40 disabled:cursor-not-allowed">
                                        Activar
                                    </button>
                                </form>

                                <form action="<?= $base ?>profile?action=toggle_zone" method="POST" class="inline">
                                    <input type="hidden" name="zona_id" value="<?= $z['id'] ?>"/>
                                    <input type="hidden" name="estado" value="finalizada"/>
                                    <button type="submit" <?= ($z['estado'] === 'finalizada') ? 'disabled' : '' ?> class="bg-blue-50 text-blue-700 hover:bg-blue-100 px-2 py-1 rounded font-bold disabled:opacity-40 disabled:cursor-not-allowed">
                                        Terminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Registrar Vivienda de Cliente en Zona -->
            <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm">
                <h3 class="text-xl font-bold text-primary mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">add_location_alt</span>
                    Registrar Casa en Zona
                </h3>
                <p class="text-on-surface-variant text-xs mb-4">Ingrese el correo de un cliente registrado, elija la zona a la que pertenece y haga clic en el mapa.</p>

                <form action="<?= $base ?>profile?action=create_route" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-primary mb-1 uppercase">Correo del Cliente:</label>
                            <input name="cliente_email" placeholder="cliente@smartsach.com" type="email" required class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary mb-1 uppercase">Nombre de Identificación:</label>
                            <input name="nombre" placeholder="Ej. Casa Principal David" type="text" required class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary mb-1 uppercase">Zona Asociada:</label>
                            <select name="zona_id" required class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none">
                                <option value="">-- Seleccionar Zona --</option>
                                <?php foreach ($zonas as $z): ?>
                                    <option value="<?= $z['id'] ?>"><?= htmlspecialchars($z['nombre_zona']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Referencias visuales:</label>
                        <input name="descripcion" placeholder="Ej. Frente al Parque Cervantes, rejas negras" type="text" class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-primary mb-1 uppercase">Latitud (Mapa):</label>
                            <input id="route-lat" name="latitud" type="text" readonly required placeholder="Haz clic en el mapa" class="w-full bg-surface-container/40 border-none rounded-full py-2.5 px-4 text-xs text-on-surface cursor-not-allowed outline-none font-mono"/>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary mb-1 uppercase">Longitud (Mapa):</label>
                            <input id="route-lon" name="longitude" type="text" readonly required placeholder="Haz clic en el mapa" class="w-full bg-surface-container/40 border-none rounded-full py-2.5 px-4 text-xs text-on-surface cursor-not-allowed outline-none font-mono"/>
                        </div>
                    </div>

                    <!-- Mapa de Asignación -->
                    <div class="relative w-full h-[250px] rounded-lg overflow-hidden border border-surface-container-high shadow-inner">
                        <div id="gestor-selector-map" class="w-full h-full"></div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-secondary hover:bg-[#00ab5d] text-white px-8 py-3 rounded-full font-bold shadow-md transition-all active:scale-95 text-xs flex items-center gap-2">
                            <span class="material-symbols-outlined">map</span>
                            Registrar Vivienda en Zona
                        </button>
                    </div>
                </form>
            </div>

            <!-- Listado y Modificación de Viviendas Existenciales -->
            <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm">
                <h3 class="text-xl font-bold text-primary mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">edit_road</span>
                    Listado de Viviendas de Clientes (CRUD)
                </h3>

                <?php if (empty($rutas)): ?>
                    <p class="text-xs text-on-surface-variant italic">No hay direcciones registradas.</p>
                <?php else: ?>
                    <div class="space-y-6">
                        <?php foreach ($rutas as $r): ?>
                            <?php $bloqueada = ($r['zona_estado'] === 'en_ruta'); ?>
                            <div class="p-4 rounded-xl border flex flex-col gap-3 <?= $bloqueada ? 'bg-amber-50/20 border-amber-300' : 'bg-surface-container/10 border-surface-container' ?>">
                                <div class="flex justify-between items-center flex-wrap gap-2">
                                    <div>
                                        <h4 class="font-bold text-sm text-on-surface"><?= htmlspecialchars($r['nombre']) ?></h4>
                                        <span class="text-[10px] text-on-surface-variant/80">Cliente: <?= htmlspecialchars($r['cliente_nombre']) ?></span>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <?php if ($bloqueada): ?>
                                            <span class="text-[10px] bg-amber-600 text-white font-bold px-2 py-0.5 rounded-full animate-pulse flex items-center gap-0.5">
                                                <span class="material-symbols-outlined text-xs">lock</span>
                                                BLOQUEADA (ZONA EN RUTA)
                                            </span>
                                        <?php endif; ?>
                                        <span class="text-[10px] bg-primary/10 text-primary font-bold px-2.5 py-0.5 rounded-full uppercase">
                                            Zona: <?= htmlspecialchars($r['nombre_zona'] ?: 'Sin Asignar') ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Formulario de Modificación de Vivienda -->
                                <form action="<?= $base ?>profile?action=edit_route" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                                    <input type="hidden" name="route_id" value="<?= $r['id'] ?>"/>
                                    
                                    <div class="md:col-span-1">
                                        <label class="block text-[9px] font-semibold text-primary uppercase mb-0.5">Nombre:</label>
                                        <input name="nombre" value="<?= htmlspecialchars($r['nombre']) ?>" type="text" required <?= $bloqueada ? 'disabled' : '' ?> class="w-full bg-white border border-surface-container-high rounded-full py-1.5 px-3 text-xs text-on-surface outline-none disabled:bg-slate-100 disabled:cursor-not-allowed"/>
                                    </div>
                                    <div class="md:col-span-1">
                                        <label class="block text-[9px] font-semibold text-primary uppercase mb-0.5">Referencias:</label>
                                        <input name="descripcion" value="<?= htmlspecialchars($r['descripcion'] ?: '') ?>" type="text" <?= $bloqueada ? 'disabled' : '' ?> class="w-full bg-white border border-surface-container-high rounded-full py-1.5 px-3 text-xs text-on-surface outline-none disabled:bg-slate-100 disabled:cursor-not-allowed"/>
                                    </div>
                                    <div class="md:col-span-1">
                                        <label class="block text-[9px] font-semibold text-primary uppercase mb-0.5">Zona:</label>
                                        <select name="zona_id" <?= $bloqueada ? 'disabled' : '' ?> class="w-full bg-white border border-surface-container-high rounded-full py-1.5 px-3 text-xs text-on-surface outline-none disabled:bg-slate-100 disabled:cursor-not-allowed">
                                            <option value="">-- Sin Zona --</option>
                                            <?php foreach ($zonas as $z): ?>
                                                <option value="<?= $z['id'] ?>" <?= ($r['zona_id'] == $z['id']) ? 'selected' : '' ?>><?= htmlspecialchars($z['nombre_zona']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="md:col-span-1">
                                        <button type="submit" <?= $bloqueada ? 'disabled' : '' ?> class="w-full bg-[#1e4638] hover:bg-primary text-white py-2 rounded-full font-bold shadow-sm text-xs disabled:bg-slate-300 disabled:text-slate-500 disabled:cursor-not-allowed">
                                            Actualizar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Inicializar mapa de Chiriquí para marcar ubicaciones de viviendas
        var map = L.map('gestor-selector-map').setView([8.42867, -82.42875], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var routeMarker = null;

        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lon = e.latlng.lng;

            document.getElementById('route-lat').value = lat.toFixed(8);
            document.getElementById('route-lon').value = lon.toFixed(8);

            if (routeMarker === null) {
                routeMarker = L.marker([lat, lon]).addTo(map);
            } else {
                routeMarker.setLatLng([lat, lon]);
            }
        });
    });
</script>

<?php
require_once __DIR__ . '/layouts/footer.php';
?>

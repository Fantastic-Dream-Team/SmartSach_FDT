<?php
require_once __DIR__ . '/../components/header.php';

// Determinar ruta base para enlaces
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($base, -1) !== '/') {
    $base .= '/';
}
?>

<div class="max-w-[1200px] mx-auto px-6 py-8">
    <!-- Header del Dashboard -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-primary">Mi Panel de Monitoreo</h1>
            <p class="text-on-surface-variant text-sm mt-1">Siga en tiempo real la ruta de recolección hacia su hogar.</p>
        </div>
        
        <!-- Selector de Rutas / Acordeón -->
        <div class="w-full lg:w-auto relative">
            <label for="route-selector" class="block text-xs font-semibold text-primary mb-1 uppercase">Mis Casas Registradas:</label>
            <select id="route-selector" onchange="window.location.href='<?= $base ?>dashboard?ruta_id=' + this.value" class="bg-[#9bb2a8]/20 border-none rounded-full py-2.5 px-6 text-on-surface focus:ring-2 focus:ring-primary outline-none cursor-pointer font-semibold text-sm">
                <?php if (empty($rutas)): ?>
                    <option value="">Sin direcciones registradas</option>
                <?php else: ?>
                    <?php foreach ($rutas as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= ($selectedRuta && intval($selectedRuta['id']) === intval($r['id'])) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['nombre']) ?> (Conductor: <?= htmlspecialchars($r['conductor_nombre'] ?: 'No asignado') ?> - <?= htmlspecialchars($r['estado']) ?>)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <?php if (!$selectedRuta): ?>
        <!-- Alerta de que no hay rutas -->
        <div class="bg-amber-50 border border-amber-200 text-amber-800 p-6 rounded-lg text-center shadow-sm">
            <span class="material-symbols-outlined text-4xl text-amber-500 mb-2">home_pin</span>
            <h3 class="text-lg font-bold">Aún no has registrado ninguna dirección</h3>
            <p class="text-sm opacity-90 mt-1 mb-4">Para ver el mapa de recolección, primero registra tu casa en tu perfil.</p>
            <a href="profile" class="bg-primary hover:bg-[#224f3c] text-white px-6 py-2.5 rounded-full font-bold shadow-md inline-block">Registrar mi primera casa</a>
        </div>
    <?php else: ?>
        
        <!-- Estado de la ruta actual -->
        <?php 
        $estadoRuta = $selectedRuta['zona_estado'] ?? 'inactiva'; 
        $esInactiva = ($estadoRuta === 'inactiva' || $estadoRuta === 'finalizada');
        ?>
        
        <!-- Contenedor del Mapa Principal -->
        <div class="relative w-full h-[480px] rounded-xl overflow-hidden border border-surface-container-high shadow-lg">
            
            <!-- Banner Inactivo (Superpuesto) -->
            <?php if ($esInactiva): ?>
                <div class="absolute inset-0 bg-black/40 z-[999] flex flex-col items-center justify-center text-center p-6 backdrop-blur-[2px]">
                    <div class="bg-red-600 text-white px-6 py-4 rounded-lg shadow-2xl max-w-md">
                        <span class="material-symbols-outlined text-4xl mb-2">warning</span>
                        <h2 class="text-2xl font-black uppercase tracking-tight">
                            <?= ($estadoRuta === 'finalizada') ? 'Ruta Finalizada' : 'Hoy no pasará el camión' ?>
                        </h2>
                        <p class="text-sm mt-2 font-medium">
                            <?= ($estadoRuta === 'finalizada') ? 'El camión ya completó la recolección en esta ruta.' : 'Esta ruta se encuentra actualmente inactiva. El conductor aún no inicia el recorrido.' ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Panel de Estado en Tiempo Real (Notificación de paso) -->
            <div id="truck-status-panel" class="hidden absolute top-4 left-4 z-[999] bg-white/95 backdrop-blur-md px-4 py-3 rounded-xl border border-surface-container-high shadow-lg text-xs max-w-xs transition-all duration-300">
                <div class="flex items-center gap-2.5">
                    <span id="status-dot" class="w-3.5 h-3.5 bg-amber-500 rounded-full inline-block animate-pulse"></span>
                    <div>
                        <strong id="status-text" class="text-primary block font-bold">Iniciando monitoreo...</strong>
                        <span id="status-subtext" class="text-[10px] text-on-surface-variant block mt-0.5">Esperando señal GPS de la zona.</span>
                    </div>
                </div>
            </div>

            <!-- Mapa Div -->
            <div id="map" class="w-full h-full <?= $esInactiva ? 'map-inactive' : '' ?>"></div>
        </div>

        <!-- Paneles Inferiores -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
            
            <!-- Panel Izquierdo: Resumen de Rutas -->
            <div class="bg-white p-6 rounded-lg border border-surface-container-high shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="text-xl font-bold text-primary mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">description</span>
                        Detalle de la Dirección
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <span class="text-xs font-semibold text-on-surface-variant/70 uppercase">Nombre:</span>
                            <p class="text-base font-bold text-on-surface"><?= htmlspecialchars($selectedRuta['nombre']) ?></p>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-on-surface-variant/70 uppercase">Descripción / Puntos de Referencia:</span>
                            <p class="text-sm text-on-surface-variant"><?= htmlspecialchars($selectedRuta['descripcion'] ?: 'Sin descripción provista') ?></p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-xs font-semibold text-on-surface-variant/70 uppercase">Conductor Asignado:</span>
                                <p class="text-sm font-bold text-on-surface"><?= htmlspecialchars($selectedRuta['conductor_nombre'] ?: 'Sin asignar') ?></p>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-on-surface-variant/70 uppercase">Costo mensual:</span>
                                <p class="text-sm font-bold text-primary">$<?= number_format($selectedRuta['costo'], 2) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 pt-4 border-t border-surface-container flex items-center justify-between">
                    <span class="text-xs text-on-surface-variant/70">¿Deseas registrar otra casa?</span>
                    <a href="profile" class="text-secondary hover:underline font-bold text-sm flex items-center gap-1">
                        Gestionar Rutas <span class="material-symbols-outlined text-xs">arrow_forward</span>
                    </a>
                </div>
            </div>

            <!-- Panel Derecho: Estado de Cuenta -->
            <div class="bg-white p-6 rounded-lg border border-surface-container-high shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="text-xl font-bold text-primary mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">account_balance_wallet</span>
                        Estado de Cuenta Financiero
                    </h3>
                    
                    <div class="flex items-center gap-4 my-6">
                        <?php if ($estadoCuenta === 'Paz y Salvo'): ?>
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center text-[#00c46a]">
                                <span class="material-symbols-outlined text-3xl font-bold">verified_user</span>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-on-surface-variant/70 uppercase">Estado actual:</span>
                                <p class="text-2xl font-black text-[#00c46a]">Paz y Salvo</p>
                            </div>
                        <?php else: ?>
                            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center text-red-600">
                                <span class="material-symbols-outlined text-3xl font-bold">error</span>
                            </div>
                            <div>
                                <span class="text-xs font-semibold text-on-surface-variant/70 uppercase">Estado actual:</span>
                                <p class="text-2xl font-black text-red-600">Moroso</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="bg-surface-container p-4 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-semibold text-on-surface-variant">Saldo pendiente total:</span>
                            <span class="text-xl font-extrabold text-primary">$<?= number_format($saldoPendiente, 2) ?></span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-surface-container flex items-center justify-between">
                    <span class="text-xs text-on-surface-variant/70">Revisa tus facturas y haz pagos simulados.</span>
                    <a href="payments" class="bg-secondary hover:bg-[#00ab5d] text-white px-5 py-2 rounded-full text-xs font-bold shadow-md transition-all">
                        Ir a Pagos
                    </a>
                </div>
            </div>
        </div>

        <!-- SECCIÓN DE NOTICIAS (Hacer la página scrollable) -->
        <section class="mt-16 border-t border-surface-container pt-12">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-primary">Noticias e Informaciones Ambientales</h2>
                <p class="text-on-surface-variant text-sm mt-1">Conozca las últimas noticias de reciclaje y recolección de basura en Chiriquí.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php if (empty($noticias)): ?>
                    <div class="bg-white rounded-lg border border-surface-container-high overflow-hidden shadow-sm hover:shadow-md transition-all">
                        <div class="h-44 bg-slate-200">
                            <img src="https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?auto=format&fit=crop&q=80&w=400" alt="Reciclaje" class="w-full h-full object-cover"/>
                        </div>
                        <div class="p-6">
                            <span class="text-[#00c46a] text-xs font-bold uppercase">Campañas</span>
                            <h3 class="text-base font-bold text-primary mt-2">Gran Jornada de Reciclaje en David</h3>
                            <p class="text-xs text-on-surface-variant mt-2 leading-relaxed">
                                Este sábado se llevará a cabo una colecta masiva de plásticos PET, aluminio y cartón en el Parque de David. ¡Trae tus materiales limpios y secos!
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($noticias as $n): ?>
                        <div class="bg-white rounded-lg border border-surface-container-high overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div class="p-6">
                                <span class="text-[#00c46a] text-xs font-bold uppercase">Novedad</span>
                                <h3 class="text-base font-bold text-primary mt-2"><?= htmlspecialchars($n['titulo']) ?></h3>
                                <p class="text-xs text-on-surface-variant mt-2 leading-relaxed">
                                    <?= htmlspecialchars($n['contenido']) ?>
                                </p>
                            </div>
                            <div class="px-6 pb-4 pt-2 border-t border-surface-container/50 flex justify-between items-center text-[10px] text-on-surface-variant/80">
                                <span>Por: <?= htmlspecialchars($n['autor_nombre'] ?: 'Sistema') ?></span>
                                <span><?= date('d M Y', strtotime($n['fecha_publicacion'])) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Inicialización del Mapa de Leaflet.js con Routing Machine -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var userLat = <?= $selectedRuta['latitud'] ?>;
                var userLon = <?= $selectedRuta['longitud'] ?>;
                var estadoRuta = "<?= $estadoRuta ?>";
                var esInactiva = <?= $esInactiva ? 'true' : 'false' ?>;
                var paradas = <?= json_encode($zonaRutas) ?>;

                // Inicializar mapa centrado en la casa del usuario
                var map = L.map('map').setView([userLat, userLon], 16);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                // Icono para la Casa del Usuario (SVG de Casa en verde primario)
                var houseIcon = L.divIcon({
                    html: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#2d5a46" width="34" height="34"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>',
                    className: '',
                    iconSize: [34, 34],
                    iconAnchor: [17, 34]
                });

                // Icono secundario para otras casas de la zona (SVG de Casa en gris)
                var neighborIcon = L.divIcon({
                    html: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#717973" width="24" height="24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>',
                    className: '',
                    iconSize: [24, 24],
                    iconAnchor: [12, 24]
                });

                // Trazar marcador del usuario
                var userMarker = L.marker([userLat, userLon], {icon: houseIcon}).addTo(map);
                userMarker.bindPopup("<b><?= htmlspecialchars($selectedRuta['nombre']) ?></b><br>Su vivienda registrada.").openPopup();

                // Dibujar el resto de viviendas en la misma zona
                paradas.forEach(function(p) {
                    var pLat = parseFloat(p.latitud);
                    var pLon = parseFloat(p.longitud);
                    // No duplicar el marcador del cliente principal
                    if (Math.abs(pLat - userLat) > 0.00001 || Math.abs(pLon - userLon) > 0.00001) {
                        L.marker([pLat, pLon], {icon: neighborIcon}).addTo(map)
                            .bindPopup("<b>Parada de la Zona: " + p.nombre + "</b><br>Cliente: " + p.cliente_nombre);
                    }
                });

                // Función Haversine para distancia en metros
                function getDistance(lat1, lon1, lat2, lon2) {
                    var R = 6371e3; // Metros
                    var phi1 = lat1 * Math.PI/180;
                    var phi2 = lat2 * Math.PI/180;
                    var deltaPhi = (lat2-lat1) * Math.PI/180;
                    var deltaLambda = (lon2-lon1) * Math.PI/180;

                    var a = Math.sin(deltaPhi/2) * Math.sin(deltaPhi/2) +
                            Math.cos(phi1) * Math.cos(phi2) *
                            Math.sin(deltaLambda/2) * Math.sin(deltaLambda/2);
                    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                    return R * c;
                }

                // Si la ruta está activa ('en_ruta'), trazar recorrido completo de la zona e iniciar animación
                if (!esInactiva && paradas.length > 0) {
                    // Mostrar panel de estado
                    var statusPanel = document.getElementById('truck-status-panel');
                    var statusDot = document.getElementById('status-dot');
                    var statusText = document.getElementById('status-text');
                    var statusSubtext = document.getElementById('status-subtext');
                    statusPanel.classList.remove('hidden');

                    // Icono de camión de basura personalizado (SVG de Camión)
                    var truckIcon = L.divIcon({
                        html: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#2d5a46" width="40" height="40"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm12 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>',
                        className: '',
                        iconSize: [40, 40],
                        iconAnchor: [20, 20]
                    });

                    // Salida simulada (primera parada + desfase)
                    var firstLat = parseFloat(paradas[0].latitud);
                    var firstLon = parseFloat(paradas[0].longitud);
                    var startLat = firstLat + 0.005;
                    var startLon = firstLon - 0.005;

                    var truckMarker = L.marker([startLat, startLon], {icon: truckIcon}).addTo(map);
                    truckMarker.bindPopup("<b>Camión smartSACH</b><br>Recolectando en la zona...").openPopup();

                    // Construir waypoints de la ruta continua
                    var waypoints = [L.latLng(startLat, startLon)];
                    paradas.forEach(function(p) {
                        waypoints.push(L.latLng(parseFloat(p.latitud), parseFloat(p.longitud)));
                    });

                    // Calcular ruta continua snapped a calles usando OSRM
                    var routingControl = L.Routing.control({
                        waypoints: waypoints,
                        router: L.Routing.osrmv1({
                            serviceUrl: 'https://router.project-osrm.org/route/v1',
                            profile: 'car'
                        }),
                        createMarker: function() { return null; },
                        show: false
                    }).addTo(map);

                    var hasPassedUserHouse = false;

                    routingControl.on('routesfound', function(e) {
                        var routes = e.routes;
                        var coordinates = routes[0].coordinates;
                        
                        var pathLine = L.polyline(coordinates, {
                            color: '#2d5a46',
                            weight: 5,
                            opacity: 0.8
                        }).addTo(map);

                        var currentIdx = 0;
                        var totalSteps = coordinates.length;

                        var interval = setInterval(function() {
                            if (currentIdx >= totalSteps) {
                                clearInterval(interval);
                                return;
                            }
                            
                            var nextPoint = coordinates[currentIdx];
                            truckMarker.setLatLng([nextPoint.lat, nextPoint.lng]);

                            // Medir distancia a la casa del usuario
                            var dist = getDistance(nextPoint.lat, nextPoint.lng, userLat, userLon);

                            if (dist < 22) {
                                // El camión está en la vivienda
                                hasPassedUserHouse = true;
                                statusDot.className = "w-3.5 h-3.5 bg-green-500 rounded-full inline-block animate-pulse";
                                statusText.innerText = "¡Recolectando en su casa!";
                                statusText.className = "text-green-700 block font-bold";
                                statusSubtext.innerText = "El camión está frente a su casa en este instante.";
                                truckMarker.bindPopup("<b>Camión smartSACH</b><br>¡Recolectando en su vivienda!").openPopup();
                            } else if (hasPassedUserHouse) {
                                // El camión ya pasó
                                statusDot.className = "w-3.5 h-3.5 bg-blue-500 rounded-full inline-block";
                                statusText.innerText = "¡Ya pasó por su vivienda!";
                                statusText.className = "text-blue-700 block font-bold";
                                statusSubtext.innerText = "Recogida completada. El camión avanza al resto de la zona.";
                            } else {
                                // Camión acercándose
                                statusDot.className = "w-3.5 h-3.5 bg-amber-500 rounded-full inline-block animate-pulse";
                                statusText.innerText = "Camión en camino...";
                                statusText.className = "text-amber-700 block font-bold";
                                statusSubtext.innerText = "El camión se encuentra a " + Math.round(dist) + " metros de su casa.";
                            }
                            
                            if (currentIdx % 6 === 0) {
                                map.panTo([nextPoint.lat, nextPoint.lng]);
                            }

                            currentIdx++;
                        }, 350);
                    });
                }
            });
        </script>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../components/footer.php';
?>

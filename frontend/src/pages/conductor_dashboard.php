<?php
require_once __DIR__ . '/../components/header.php';

// Determinar ruta base para enlaces
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($base, -1) !== '/') {
    $base .= '/';
}

// Variables disponibles desde el controlador:
// $rutasDisponibles, $selectedRuta, $clientes, $rutaId
?>

<!-- Hojas de estilo y scripts de Leaflet Routing Machine para multipunto -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<!-- Leaflet.markercluster para agrupar casas con coordenadas idénticas -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

<div class="max-w-[1200px] mx-auto px-6 py-8">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-primary">Consola del Conductor</h1>
            <p class="text-on-surface-variant text-sm mt-1">Gestione su ruta y visualice los clientes asignados.</p>
        </div>
        <!-- Selector de Ruta -->
        <div class="flex items-center gap-2">
            <label for="rutaSelector" class="text-sm font-semibold text-on-surface-variant">Ruta:</label>
            <select id="rutaSelector" class="bg-surface-container border border-outline rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-primary">
                <option value="">Selecciona una ruta...</option>
                <?php foreach ($rutasDisponibles as $ruta): ?>
                    <option value="<?= $ruta['ruta_id'] ?>" <?= ($rutaId == $ruta['ruta_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ruta['nombre_ruta']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <?php if (empty($rutasDisponibles)): ?>
        <!-- Sin rutas disponibles -->
        <div class="bg-blue-50 border border-blue-200 text-blue-800 p-8 rounded-xl text-center shadow-sm">
            <span class="material-symbols-outlined text-4xl text-blue-500 mb-2">error</span>
            <h3 class="text-lg font-bold">No hay rutas disponibles</h3>
            <p class="text-sm opacity-90 mt-1">Contacta al administrador para crear rutas en el sistema.</p>
        </div>
    <?php elseif (!$selectedRuta): ?>
        <!-- Ruta seleccionada no válida -->
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-8 rounded-xl text-center shadow-sm">
            <span class="material-symbols-outlined text-4xl text-yellow-500 mb-2">warning</span>
            <h3 class="text-lg font-bold">Selecciona una ruta</h3>
            <p class="text-sm opacity-90 mt-1">Elige una ruta del selector para ver los clientes asignados.</p>
        </div>
    <?php else: ?>
        
        <?php 
        $estado = $selectedRuta['estado_ruta'] ?? 'inactiva';
        $estaActivo = ($estado === 'activa');
        ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Panel Izquierdo: Control de Operación -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Tarjeta de Control -->
                <div class="<?= $estaActivo ? 'bg-primary text-white' : 'bg-slate-900 text-white' ?> p-6 rounded-xl shadow-xl flex flex-col justify-between min-h-[360px] pb-6">
                    <div>
                        <span class="<?= $estaActivo ? 'bg-[#00c46a] animate-pulse' : 'bg-[#ba1a1a]' ?> text-white text-[10px] font-bold uppercase px-2.5 py-1 rounded-full inline-block">
                            <?= $estaActivo ? '🟢 En Tránsito (Activa)' : '🔴 Ruta Inactiva' ?>
                        </span>
                        
                        <h3 class="text-xl font-bold mt-4"><?= htmlspecialchars($selectedRuta['nombre_ruta']) ?></h3>
                        <p class="text-xs opacity-80 mt-2 leading-relaxed">
                            <strong>Sector:</strong> <?= htmlspecialchars($selectedRuta['zona_sector'] ?: 'No especificado') ?>
                        </p>
                        <p class="text-xs opacity-80 mt-1">
                            <strong>Horario:</strong> <?= htmlspecialchars($selectedRuta['horario_estimado'] ?: 'No especificado') ?>
                        </p>
                        <div class="mt-4 bg-white/10 p-3 rounded-lg text-[11px] leading-relaxed space-y-1">
                            <p><strong>Clientes asignados:</strong> <?= count($clientes) ?></p>
                            <p class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-[#00c46a] rounded-full inline-block"></span> Verde: Al Día</p>
                            <p class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-[#ba1a1a] rounded-full inline-block"></span> Rojo: Moroso</p>
                        </div>
                    </div>

                    <div class="space-y-2 mt-6">
                        <?php if (!$estaActivo): ?>
                            <form action="<?= $base ?>conductor/dashboard?action=start&ruta_id=<?= $rutaId ?>" method="POST">
                                <button type="submit" class="w-full bg-[#00c46a] hover:bg-[#00ab5d] text-white py-4 rounded-full font-bold shadow-lg text-sm transition-all active:scale-95 flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined font-bold text-base">play_arrow</span>
                                    Iniciar Ruta
                                </button>
                            </form>
                        <?php else: ?>
                            <form action="<?= $base ?>conductor/dashboard?action=finish&ruta_id=<?= $rutaId ?>" method="POST">
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-4 rounded-full font-bold shadow-lg text-sm transition-all active:scale-95 flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-base">stop</span>
                                    Finalizar Ruta
                                </button>
                            </form>
                            
                            <!-- Controles de simulación de recorrido -->
                            <div class="mt-4 pt-4 border-t border-white/20 space-y-2">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-200 text-center">Simulador de Recorrido / GPS</p>
                                <div class="flex gap-2">
                                    <button type="button" id="btnIniciarRecorrido" class="flex-1 bg-[#00c46a] hover:bg-[#00ab5d] text-white text-xs py-2.5 px-3 rounded-lg font-bold transition-all flex items-center justify-center gap-1 active:scale-95">
                                        <span class="material-symbols-outlined text-sm">play_circle</span> Iniciar Recorrido
                                    </button>
                                    <button type="button" id="btnFinalizarRecorrido" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-slate-900 text-xs py-2.5 px-3 rounded-lg font-bold transition-all flex items-center justify-center gap-1 active:scale-95 opacity-50 cursor-not-allowed" disabled>
                                        <span class="material-symbols-outlined text-sm">pause_circle</span> Interrumpir
                                    </button>
                                </div>
                                <div id="simulation-status" class="text-[10px] text-center text-slate-200 italic mt-1">Estado: Recorrido no iniciado</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Resumen de clientes -->
                <div class="bg-white p-4 rounded-xl border border-surface-container-high shadow-sm">
                    <h4 class="text-sm font-bold text-primary mb-2">Resumen de Clientes</h4>
                    <div class="flex justify-between text-xs">
                        <span class="text-green-600">Al Día: <?= count(array_filter($clientes, fn($c) => $c['estado_financiero'] === 'al_dia')) ?></span>
                        <span class="text-red-600">Morosos: <?= count(array_filter($clientes, fn($c) => $c['estado_financiero'] === 'moroso')) ?></span>
                        <span class="text-slate-600">Total: <?= count($clientes) ?></span>
                    </div>
                </div>
            </div>

            <!-- Panel Derecho: Mapa -->
            <div class="lg:col-span-2">
                <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm h-[480px] flex flex-col">
                    <h3 class="text-lg font-bold text-primary mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">navigation</span>
                        Mapa de Recolección
                    </h3>
                    
                    <div class="relative flex-grow rounded-lg overflow-hidden border border-surface-container shadow-inner">
                        <?php if (!$estaActivo): ?>
                            <div class="absolute inset-0 bg-slate-950/85 z-[999] flex flex-col items-center justify-center text-center p-6 text-white">
                                <span class="material-symbols-outlined text-5xl text-slate-400 mb-2">visibility_off</span>
                                <h3 class="text-xl font-bold uppercase tracking-tight text-slate-200">Mapa Apagado</h3>
                                <p class="text-xs text-slate-400 mt-2 max-w-sm">Inicie la ruta para activar el mapa y ver las paradas de sus clientes.</p>
                            </div>
                        <?php endif; ?>
                        
                        <div id="driver-map" class="w-full h-full"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inicialización del mapa -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var clientes = <?= json_encode($clientes) ?>;
                var estaActivo = <?= $estaActivo ? 'true' : 'false' ?>;
                var rutaId = <?= $rutaId ?: 0 ?>;

                // Centro por defecto (David, Chiriquí)
                var centerLat = 8.42867;
                var centerLon = -82.42875;
                
                if (clientes.length > 0) {
                    centerLat = parseFloat(clientes[0].latitud) || centerLat;
                    centerLon = parseFloat(clientes[0].longitud) || centerLon;
                }

                var map = L.map('driver-map').setView([centerLat, centerLon], 14);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                // Iconos para clientes
                var greenIcon = L.divIcon({
                    html: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#00c46a" width="28" height="28"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>',
                    className: '',
                    iconSize: [28, 28],
                    iconAnchor: [14, 28]
                });

                var redIcon = L.divIcon({
                    html: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ba1a1a" width="28" height="28"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>',
                    className: '',
                    iconSize: [28, 28],
                    iconAnchor: [14, 28]
                });

                // Dibujar marcadores y preparar waypoints
                var markers = L.markerClusterGroup({
                    maxClusterRadius: 30, // Agrupar solo si están en la misma casa o muy cerca
                    spiderfyOnMaxZoom: true,
                    showCoverageOnHover: false,
                    zoomToBoundsOnClick: true
                });
                
                var waypoints = [];
                var truckMarker = null;
                var routeCoordinates = [];
                var currentRouteIndex = 0;

                if (clientes.length > 0 && estaActivo) {
                    // Marcar posición inicial del camión (simulada)
                    var truckLat = centerLat + 0.003;
                    var truckLon = centerLon - 0.003;
                    
                    var truckIcon = L.divIcon({
                        html: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#2d5a46" width="34" height="34"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm12 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>',
                        className: '',
                        iconSize: [34, 34],
                        iconAnchor: [17, 17]
                    });
                    
                    truckMarker = L.marker([truckLat, truckLon], {icon: truckIcon}).addTo(map)
                        .bindPopup("<b>🚛 Ubicación del Camión</b>").openPopup();
                    
                    waypoints.push(L.latLng(truckLat, truckLon));
                }

                // Agregar marcadores de clientes
                clientes.forEach(function(c) {
                    var lat = parseFloat(c.latitud);
                    var lon = parseFloat(c.longitud);
                    var isMoroso = (c.estado_financiero === 'moroso');
                    var icon = isMoroso ? redIcon : greenIcon;
                    
                    var popupMsg = "<b>" + htmlspecialchars(c.cliente_nombre) + "</b><br>" +
                                   "Dirección: " + htmlspecialchars(c.descripcion_direccion || c.direccion || 'N/A') + "<br>" +
                                   "Estado: <span class='font-bold " + (isMoroso ? "text-red-600" : "text-green-600") + "'>" + 
                                   (isMoroso ? "🔴 Moroso" : "🟢 Al Día") + "</span>";
                    
                    var marker = L.marker([lat, lon], {icon: icon}).bindPopup(popupMsg);
                    markers.addLayer(marker);
                    
                    if (estaActivo) {
                        waypoints.push(L.latLng(lat, lon));
                    }
                });
                
                map.addLayer(markers);

                // Si está activo y hay clientes, trazar ruta optimizada
                if (estaActivo && waypoints.length > 1) {
                    var routingControl = L.Routing.control({
                        waypoints: waypoints,
                        router: L.Routing.osrmv1({
                            serviceUrl: 'https://router.project-osrm.org/route/v1'
                        }),
                        createMarker: function() { return null; },
                        lineOptions: {
                            styles: [{ color: '#2d5a46', opacity: 0.8, weight: 5 }]
                        },
                        show: false,
                        addWaypoints: false,
                        routeWhileDragging: false
                    }).addTo(map);

                    // Escuchar el evento de ruta encontrada para obtener las coordenadas del trayecto físico
                    routingControl.on('routesfound', function(e) {
                        var routes = e.routes;
                        if (routes && routes.length > 0) {
                            routeCoordinates = routes[0].coordinates;
                            currentRouteIndex = 0;
                            console.log("Ruta física cargada. Coordenadas encontradas: ", routeCoordinates.length);
                        }
                    });
                }

                // Lógica de simulación de recorrido y tracking (POST /api/track cada 5 segundos)
                var trackingInterval = null;
                var simulatedLat = (clientes.length > 0) ? (parseFloat(clientes[0].latitud) + 0.003) : centerLat + 0.003;
                var simulatedLng = (clientes.length > 0) ? (parseFloat(clientes[0].longitud) - 0.003) : centerLon - 0.003;

                var btnIniciar = document.getElementById('btnIniciarRecorrido');
                var btnFinalizar = document.getElementById('btnFinalizarRecorrido');
                var statusText = document.getElementById('simulation-status');

                if (btnIniciar && btnFinalizar) {
                    btnIniciar.addEventListener('click', function() {
                        if (trackingInterval) return;

                        // Cambiar estados de los botones en la interfaz
                        btnIniciar.disabled = true;
                        btnIniciar.classList.add('opacity-50', 'cursor-not-allowed');
                        btnFinalizar.disabled = false;
                        btnFinalizar.classList.remove('opacity-50', 'cursor-not-allowed');
                        statusText.innerText = "Estado: Transmitiendo ubicación...";
                        statusText.classList.add('text-green-400');

                        trackingInterval = setInterval(function() {
                            // Si hay ruta calculada por Leaflet Routing Machine, seguimos el trayecto
                            if (routeCoordinates.length > 0) {
                                if (currentRouteIndex < routeCoordinates.length) {
                                    var nextPoint = routeCoordinates[currentRouteIndex];
                                    simulatedLat = nextPoint.lat;
                                    simulatedLng = nextPoint.lng;
                                    
                                    if (truckMarker) {
                                        truckMarker.setLatLng([simulatedLat, simulatedLng]);
                                    }
                                    
                                    // Avanzar de forma proporcional
                                    var stepJump = Math.max(1, Math.floor(routeCoordinates.length / 50));
                                    currentRouteIndex += stepJump;
                                } else {
                                    // Bucle de simulación al llegar al final
                                    currentRouteIndex = 0;
                                }
                            } else {
                                // Desplazamiento lineal básico si no hay ruta cargada
                                simulatedLat += (centerLat - simulatedLat) * 0.05;
                                simulatedLng += (centerLon - simulatedLng) * 0.05;
                            }

                            // Aplicar geo-fencing (David, Chiriquí)
                            if (simulatedLat < 8.3800) simulatedLat = 8.3800;
                            if (simulatedLat > 8.4800) simulatedLat = 8.4800;
                            if (simulatedLng < -82.4800) simulatedLng = -82.4800;
                            if (simulatedLng > -82.4000) simulatedLng = -82.4000;

                            if (truckMarker) {
                                truckMarker.setLatLng([simulatedLat, simulatedLng]);
                            }

                            // Petición POST al endpoint asíncrono
                            fetch('<?= $base ?>api/track', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    ruta_id: rutaId,
                                    latitud: simulatedLat,
                                    longitud: simulatedLng
                                })
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Estado HTTP ' + response.status);
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log('Transmisión de ubicación exitosa:', data);
                            })
                            .catch(error => {
                                console.error('Fallo en la transmisión de ubicación:', error);
                            });

                        }, 5000);
                    });

                    btnFinalizar.addEventListener('click', function() {
                        if (trackingInterval) {
                            clearInterval(trackingInterval);
                            trackingInterval = null;
                        }
                        btnIniciar.disabled = false;
                        btnIniciar.classList.remove('opacity-50', 'cursor-not-allowed');
                        btnFinalizar.disabled = true;
                        btnFinalizar.classList.add('opacity-50', 'cursor-not-allowed');
                        statusText.innerText = "Estado: Recorrido pausado / finalizado";
                        statusText.classList.remove('text-green-400');
                    });
                }

                // Selector de ruta: recargar al cambiar
                document.getElementById('rutaSelector').addEventListener('change', function() {
                    if (this.value) {
                        window.location.href = '<?= $base ?>conductor/dashboard?ruta_id=' + this.value;
                    }
                });

                // Función auxiliar para escapar HTML en JS
                function htmlspecialchars(str) {
                    if (str === null || str === undefined) return '';
                    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                }
            });
        </script>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../components/footer.php';
?>
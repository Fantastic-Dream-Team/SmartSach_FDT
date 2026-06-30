<?php
require_once __DIR__ . '/../components/header.php';

// Determinar ruta base para enlaces
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($base, -1) !== '/') {
    $base .= '/';
}
?>

<!-- Hojas de estilo y scripts de Leaflet Routing Machine para multipunto -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<div class="max-w-[1200px] mx-auto px-6 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-primary">Consola del Conductor</h1>
        <p class="text-on-surface-variant text-sm mt-1">Gestione el estado de su zona asignada y notifique su recorrido a los clientes.</p>
    </div>

    <?php if (!$selectedZona): ?>
        <!-- Sin zona asignada -->
        <div class="bg-blue-50 border border-blue-200 text-blue-800 p-8 rounded-xl text-center shadow-sm">
            <span class="material-symbols-outlined text-4xl text-blue-500 mb-2">airport_shuttle</span>
            <h3 class="text-lg font-bold">No tienes una zona asignada actualmente</h3>
            <p class="text-sm opacity-90 mt-1">Por favor, contacta a tu Gestor administrativo para que te vincule una zona de recolección.</p>
        </div>
    <?php else: ?>
        
        <?php 
        $estado = $selectedZona['estado'];
        $estaActivo = ($estado === 'en_ruta');
        ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Panel Izquierdo: Control de Operación de la Zona -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Tarjeta Principal de Control (Si está Inactiva/Finalizada) -->
                <?php if (!$estaActivo): ?>
                    <div class="bg-slate-900 text-white p-6 rounded-xl shadow-xl flex flex-col justify-between h-[360px]">
                        <div>
                            <span class="bg-[#ba1a1a] text-white text-[10px] font-bold uppercase px-2.5 py-1 rounded-full">Zona Inactiva</span>
                            
                            <h3 class="text-xl font-bold mt-4 text-[#9fcfb6]"><?= htmlspecialchars($selectedZona['nombre_zona']) ?></h3>
                            <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                                <strong>Descripción:</strong> <?= htmlspecialchars($selectedZona['descripcion'] ?: 'Sin descripción registrada.') ?>
                            </p>
                            <p class="text-xs text-slate-400 mt-2">
                                <strong>Paradas Totales:</strong> <?= count($rutas) ?> clientes registrados en este sector.
                            </p>
                        </div>

                        <form action="<?= $base ?>dashboard?action=start" method="POST" class="mt-6">
                            <button type="submit" class="w-full bg-[#00c46a] hover:bg-[#00ab5d] text-white py-4 rounded-full font-bold shadow-lg text-sm transition-all active:scale-95 flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined font-bold text-base">play_arrow</span>
                                Iniciar Ruta
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Tarjeta de Control (Si está Activa) -->
                    <div class="bg-primary text-white p-6 rounded-xl shadow-xl flex flex-col justify-between h-[360px] border border-[#9fcfb6]/20">
                        <div>
                            <span class="bg-[#00c46a] text-white text-[10px] font-bold uppercase px-2.5 py-1 rounded-full animate-pulse">En Tránsito (Zona Activa)</span>
                            
                            <h3 class="text-xl font-bold mt-4 text-white"><?= htmlspecialchars($selectedZona['nombre_zona']) ?></h3>
                            <p class="text-xs text-[#9fcfb6] mt-2 leading-relaxed">
                                <strong>Detalle:</strong> <?= htmlspecialchars($selectedZona['descripcion'] ?: 'Sin descripción.') ?>
                            </p>
                            <div class="mt-4 bg-white/10 p-3 rounded-lg text-[11px] leading-relaxed space-y-1">
                                <p><strong>Código de Color en Mapa:</strong></p>
                                <p class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-[#00c46a] rounded-full inline-block"></span> Verde: Paz y Salvo (Recolectar)</p>
                                <p class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-[#ba1a1a] rounded-full inline-block"></span> Rojo: Moroso (Suspender)</p>
                            </div>
                        </div>

                        <div class="space-y-2 mt-6">
                            <!-- Botón Finalizar -->
                            <form action="<?= $base ?>dashboard?action=finish" method="POST">
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-3.5 rounded-full font-bold shadow-md text-sm transition-all active:scale-95 flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-base">stop</span>
                                    Finalizar Ruta
                                </button>
                            </form>
                            
                            <!-- Botón Restablecer -->
                            <form action="<?= $base ?>dashboard?action=reset" method="POST">
                                <button type="submit" class="w-full bg-white/10 hover:bg-white/20 text-white py-2 rounded-full font-semibold text-xs transition-all active:scale-95">
                                    Reestablecer a Inactiva
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Panel Derecho: Mapa de Operación (Multipunto y Snapped a calles) -->
            <div class="lg:col-span-2">
                <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm h-[480px] flex flex-col">
                    <h3 class="text-lg font-bold text-primary mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">navigation</span>
                        Navegador de Recogida de Basura
                    </h3>
                    
                    <div class="relative flex-grow rounded-lg overflow-hidden border border-surface-container shadow-inner">
                        <?php if (!$estaActivo): ?>
                            <!-- Pantalla Oscura sobre el mapa para simular inactividad -->
                            <div class="absolute inset-0 bg-slate-950/85 z-[999] flex flex-col items-center justify-center text-center p-6 text-white">
                                <span class="material-symbols-outlined text-5xl text-slate-400 mb-2">visibility_off</span>
                                <h3 class="text-xl font-bold uppercase tracking-tight text-slate-200">Mapa Apagado</h3>
                                <p class="text-xs text-slate-400 mt-2 max-w-sm">Inicie la ruta usando el botón "Iniciar Ruta" de la izquierda para desplegar el mapa multipunto y las paradas ordenadas de sus clientes.</p>
                            </div>
                        <?php endif; ?>
                        
                        <div id="driver-map" class="w-full h-full"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inicialización del mapa para el conductor -->
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var paradas = <?= json_encode($rutas) ?>;
                var estaActivo = <?= $estaActivo ? 'true' : 'false' ?>;

                // Si no hay paradas en la zona, centrar por defecto en David
                var centerLat = 8.42867;
                var centerLon = -82.42875;
                if (paradas.length > 0) {
                    centerLat = parseFloat(paradas[0].latitud);
                    centerLon = parseFloat(paradas[0].longitud);
                }

                var map = L.map('driver-map').setView([centerLat, centerLon], 14);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                // Iconos coloreados para estado de cuenta (SVG de Casas)
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

                // Dibujar marcadores dinámicos y preparar waypoints
                var waypoints = [];

                if (paradas.length > 0) {
                    // Si está activo, simular la posición inicial del camión ligeramente desfasada
                    if (estaActivo) {
                        var truckLat = centerLat + 0.005;
                        var truckLon = centerLon - 0.005;
                        
                        var truckIcon = L.divIcon({
                            html: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#2d5a46" width="34" height="34"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm12 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>',
                            className: '',
                            iconSize: [34, 34],
                            iconAnchor: [17, 17]
                        });
                        
                        L.marker([truckLat, truckLon], {icon: truckIcon}).addTo(map)
                            .bindPopup("<b>Ubicación del Camión (Salida)</b>").openPopup();
                        
                        waypoints.push(L.latLng(truckLat, truckLon));
                    }

                    // Iterar clientes e insertarlos
                    paradas.forEach(function(p) {
                        var lat = parseFloat(p.latitud);
                        var lon = parseFloat(p.longitud);
                        var isMoroso = parseInt(p.deudas_pendientes) > 0;
                        var icon = isMoroso ? redIcon : greenIcon;
                        
                        var popupMsg = "<b>" + p.nombre + "</b><br>" +
                                       "Cliente: " + p.cliente_nombre + "<br>" +
                                       "Estado: <span class='font-bold " + (isMoroso ? "text-red-600" : "text-green-600") + "'>" + 
                                       (isMoroso ? "Moroso (Suspender)" : "Paz y Salvo (Recolectar)") + "</span>";
                        
                        L.marker([lat, lon], {icon: icon}).addTo(map).bindPopup(popupMsg);
                        
                        if (estaActivo) {
                            waypoints.push(L.latLng(lat, lon));
                        }
                    });

                    // Si está activo, trazar el enrutamiento multipunto continuo snapped a calles usando OSRM
                    if (estaActivo && waypoints.length > 1) {
                        L.Routing.control({
                            waypoints: waypoints,
                            router: L.Routing.osrmv1({
                                serviceUrl: 'https://router.project-osrm.org/route/v1'
                            }),
                            createMarker: function() { return null; }, // No duplicar marcadores
                            lineOptions: {
                                styles: [{ color: '#2d5a46', opacity: 0.8, weight: 5 }]
                            },
                            show: false, // Ocultar panel de instrucciones escrito
                            addWaypoints: false,
                            routeWhileDragging: false
                        }).addTo(map);
                    }
                }
            });
        </script>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../components/footer.php';
?>

<?php
require_once __DIR__ . '/layouts/header.php';

// Determinar ruta base para enlaces
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($base, -1) !== '/') {
    $base .= '/';
}

// Obtener estadísticas de zonas activas
$zonasActivas = [];
$totalCasas = count($routes);
$casasEnZonasActivas = 0;

foreach ($routes as $r) {
    if ($r['zona_estado'] === 'en_ruta') {
        $casasEnZonasActivas++;
        $zId = $r['zona_id'];
        if (!isset($zonasActivas[$zId])) {
            $zonasActivas[$zId] = [
                'nombre' => $r['nombre_zona'],
                'casas' => []
            ];
        }
        $zonasActivas[$zId]['casas'][] = $r;
    }
}
?>

<div class="max-w-[1200px] mx-auto px-6 py-8">
    <div class="mb-8 flex justify-between items-center flex-wrap gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-primary">Consola del Gestor - Monitoreo Global</h1>
            <p class="text-on-surface-variant text-sm mt-1">Supervisión en tiempo real de zonas de recolección activas, paradas y estado de cuentas de clientes.</p>
        </div>
        <a href="<?= $base ?>profile" class="bg-secondary text-white hover:bg-secondary/90 px-5 py-2.5 rounded-full font-bold text-xs shadow-md transition-all flex items-center gap-1">
            <span class="material-symbols-outlined text-base">settings</span>
            Configurar Zonas
        </a>
    </div>

    <!-- Contenedor del Mapa Principal de Monitoreo -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Sidebar: Zonas Activas -->
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white p-5 rounded-xl border border-surface-container-high shadow-sm">
                <h3 class="text-sm font-bold text-primary mb-3 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-green-600 font-bold animate-pulse text-lg">circle</span>
                    Monitoreo en Vivo (<?= count($zonasActivas) ?>)
                </h3>
                
                <?php if (empty($zonasActivas)): ?>
                    <p class="text-xs text-on-surface-variant italic py-2">No hay conductores trabajando en ruta en este momento.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($zonasActivas as $za): ?>
                            <div class="bg-green-50/40 p-3 rounded-lg border border-green-200">
                                <div class="font-bold text-xs text-primary flex justify-between items-center">
                                    <span><?= htmlspecialchars($za['nombre']) ?></span>
                                    <span class="text-[9px] bg-green-600 text-white font-black px-2 py-0.5 rounded-full uppercase animate-pulse">En Ruta</span>
                                </div>
                                <p class="text-[10px] text-on-surface-variant mt-1"><?= count($za['casas']) ?> paradas estimadas en el trayecto.</p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Leyenda de Colores de Clientes -->
            <div class="bg-white p-5 rounded-xl border border-surface-container-high shadow-sm space-y-2.5">
                <h3 class="text-xs font-bold text-primary uppercase tracking-wider">Estado de Cuenta de Clientes</h3>
                <div class="space-y-1.5 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-[#00c46a] inline-block shadow-sm"></span>
                        <span class="font-semibold text-on-surface">Paz y Salvo</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-[#ba1a1a] inline-block shadow-sm"></span>
                        <span class="font-semibold text-on-surface">Moroso (Deuda Pendiente)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mapa -->
        <div class="lg:col-span-3">
            <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm">
                <h3 class="text-lg font-bold text-primary mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">map</span>
                    Mapa de Recorrido Multipunto de Chiriquí
                </h3>
                
                <div class="relative w-full h-[500px] rounded-lg overflow-hidden border border-surface-container-high shadow-inner">
                    <div id="gestor-map" class="w-full h-full"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Actividad -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="bg-white p-6 rounded-lg border border-surface-container-high shadow-sm">
            <span class="text-xs font-semibold text-on-surface-variant/70 uppercase">Direcciones Totales</span>
            <h3 class="text-3xl font-black text-primary mt-1"><?= $totalCasas ?></h3>
        </div>
        <div class="bg-white p-6 rounded-lg border border-surface-container-high shadow-sm">
            <span class="text-xs font-semibold text-on-surface-variant/70 uppercase">Paradas Activas (En Tránsito)</span>
            <h3 class="text-3xl font-black text-secondary mt-1"><?= $casasEnZonasActivas ?></h3>
        </div>
        <div class="bg-white p-6 rounded-lg border border-surface-container-high shadow-sm">
            <span class="text-xs font-semibold text-on-surface-variant/70 uppercase">Paradas Inactivas</span>
            <h3 class="text-3xl font-black text-[#163a6c] mt-1"><?= $totalCasas - $casasEnZonasActivas ?></h3>
        </div>
    </div>
</div>

<!-- Inicialización de Mapa de Monitoreo General -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Centrar mapa en David, Chiriquí
        var map = L.map('gestor-map').setView([8.42867, -82.42875], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Iconos de parada coloreados
        // Iconos coloreados para viviendas (SVG de Casas)
        var greenIcon = L.divIcon({
            html: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#00c46a" width="26" height="26"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>',
            className: '',
            iconSize: [26, 26],
            iconAnchor: [13, 26]
        });

        var redIcon = L.divIcon({
            html: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ba1a1a" width="26" height="26"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>',
            className: '',
            iconSize: [26, 26],
            iconAnchor: [13, 26]
        });

        // Marcadores de paradas
        var houses = <?= json_encode($routes) ?>;
        var activeZonas = {};

        houses.forEach(function(h) {
            var lat = parseFloat(h.latitud);
            var lon = parseFloat(h.longitud);
            var isMoroso = parseInt(h.deudas_pendientes) > 0;
            var icon = isMoroso ? redIcon : greenIcon;
            
            var popupContent = "<b>" + h.nombre + "</b><br>" +
                               "Cliente: " + h.cliente_nombre + "<br>" +
                               "Zona: " + (h.nombre_zona ? h.nombre_zona : "Sin Zona") + "<br>" +
                               "Estado: <span class='font-bold " + (isMoroso ? "text-red-600" : "text-green-600") + "'>" + 
                               (isMoroso ? "Moroso" : "Paz y Salvo") + "</span>";

            L.marker([lat, lon], {icon: icon}).addTo(map)
                .bindPopup(popupContent);

            // Agrupar casas de zonas activas para trazar las polilíneas de ruta continua
            if (h.zona_estado === 'en_ruta' && h.zona_id) {
                if (!activeZonas[h.zona_id]) {
                    activeZonas[h.zona_id] = [];
                }
                activeZonas[h.zona_id].push([lat, lon]);
            }
        });

        // Dibujar polilíneas continuas para representar el enrutamiento multipunto en cada zona activa
        Object.keys(activeZonas).forEach(function(zonaId) {
            var coords = activeZonas[zonaId];
            if (coords.length > 1) {
                // Trazar línea de ruta
                L.polyline(coords, {
                    color: '#2d5a46',
                    weight: 5,
                    opacity: 0.8,
                    dashArray: '8, 8'
                }).addTo(map);
            }
        });
    });
</script>

<?php
require_once __DIR__ . '/layouts/footer.php';
?>

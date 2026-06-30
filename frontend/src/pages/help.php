<?php
require_once __DIR__ . '/../components/header.php';
require_once ROOT_PATH . '/backend/src/models/ReporteIncidencia.php';

$reporteModelHelper = new ReporteIncidencia();

$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($base, -1) !== '/') {
    $base .= '/';
}
?>

<div class="max-w-[1200px] mx-auto px-6 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-primary">Reportes y Ayuda</h1>
        <p class="text-on-surface-variant text-sm mt-1">Colabore con nosotros reportando incidentes de recolección.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Panel Izquierdo: Información de Contacto -->
        <div class="lg:col-span-1 space-y-6">
            <div id="contacto" class="bg-white p-6 rounded-lg border border-surface-container-high shadow-sm">
                <h3 class="text-lg font-bold text-primary mb-4">Canales de Atención</h3>
                <div class="space-y-4 text-sm text-on-surface-variant">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary">call</span>
                        <div>
                            <span class="block text-xs font-semibold text-primary uppercase">Teléfono de Oficina:</span>
                            <span class="font-bold text-on-surface">+507 775-4321</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary">mail</span>
                        <div>
                            <span class="block text-xs font-semibold text-primary uppercase">Correo de Soporte:</span>
                            <span class="font-bold text-on-surface">soporte@smartsach.com</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary">distance</span>
                        <div>
                            <span class="block text-xs font-semibold text-primary uppercase">Oficina Principal:</span>
                            <span class="text-on-surface">David Centro, Calle 3era Oeste, Chiriquí</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Derecho: Formulario de Incidencias e Historial -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Formulario de Reporte -->
            <div class="bg-white p-6 rounded-lg border border-surface-container-high shadow-sm">
                <h3 class="text-xl font-bold text-primary mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">report</span>
                    Reportar Incidencia
                </h3>
                
                <form action="<?= $base ?>help" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-primary mb-1 uppercase">Ubicación Afectada:</label>
                            <select name="ubicacion_id" required class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-sm text-on-surface focus:ring-2 focus:ring-primary outline-none">
                                <option value="">-- Seleccionar Ubicación --</option>
                                <?php foreach ($ubicaciones as $u): ?>
                                    <option value="<?= $u['ubicacion_id'] ?>"><?= htmlspecialchars($u['nombre_referencia']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary mb-1 uppercase">Tipo de Incidencia:</label>
                            <select name="tipo_incidencia" required class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-sm text-on-surface focus:ring-2 focus:ring-primary outline-none">
                                <option value="no_paso_camion">El camión no pasó</option>
                                <option value="desperdicio_en_via">Desperdicios dejados en vía</option>
                                <option value="mala_atencion">Mala Atención</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Descripción Detallada del Problema:</label>
                        <textarea name="descripcion" rows="3" placeholder="Describa brevemente la incidencia" required class="w-full bg-surface-container/60 border-none rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-2 focus:ring-primary outline-none"></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-secondary hover:bg-[#00ab5d] text-white px-8 py-3 rounded-full font-bold shadow-md transition-all active:scale-95 flex items-center gap-2">
                            <span class="material-symbols-outlined">send</span>
                            Enviar Reporte
                        </button>
                    </div>
                </form>
            </div>

            <!-- Listado Público / Historial de Reportes -->
            <div class="bg-white p-6 rounded-lg border border-surface-container-high shadow-sm">
                <h3 class="text-xl font-bold text-primary mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">history</span>
                    Mis Reportes
                </h3>

                <?php if (empty($reportes)): ?>
                    <p class="text-on-surface-variant text-sm py-4">No tiene reportes registrados.</p>
                <?php else: ?>
                    <div class="space-y-6">
                        <?php foreach ($reportes as $rep): ?>
                            <!-- Tarjeta de Reporte -->
                            <div class="bg-surface-container/20 p-5 rounded-xl border border-surface-container flex flex-col gap-4">
                                <div class="flex justify-between items-start gap-4">
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <span class="block text-sm font-bold text-on-surface">Tipo: <?= htmlspecialchars(ucwords(str_replace('_', ' ', $rep['tipo_incidencia']))) ?></span>
                                            <span class="text-[10px] text-on-surface-variant/80">Fecha: <?= date('d/m/Y H:i', strtotime($rep['fecha_reporte'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase 
                                            <?= ($rep['estado_reporte'] === 'resuelto') ? 'bg-green-100 text-[#00c46a]' : (($rep['estado_reporte'] === 'en_proceso') ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-600') ?>">
                                            <?= htmlspecialchars($rep['estado_reporte']) ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Detalles del problema -->
                                <div>
                                    <div class="text-xs text-primary font-semibold mb-1">Ubicación Afectada: <?= htmlspecialchars($rep['nombre_referencia']) ?></div>
                                    <p class="text-sm text-on-surface-variant leading-relaxed"><?= htmlspecialchars($rep['descripcion']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../components/footer.php';
?>

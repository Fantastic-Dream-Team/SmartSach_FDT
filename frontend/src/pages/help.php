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
                
                <form action="<?= $base ?>help?action=submit_report" method="POST" class="space-y-4">
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
                <h3 class="text-xl font-bold text-primary mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">history</span>
                    Mis Reportes
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-surface-container text-on-surface-variant font-semibold">
                                <th class="pb-3 font-medium">ID</th>
                                <th class="pb-3 font-medium">Fecha</th>
                                <th class="pb-3 font-medium">Tipo Incidencia</th>
                                <th class="pb-3 font-medium">Ubicación</th>
                                <th class="pb-3 font-medium text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reportes)): ?>
                                <!-- Mock Row 1 -->
                                <tr class="border-b border-surface-container/60 hover:bg-surface-container/30 transition-colors">
                                    <td class="py-4 font-bold text-on-surface">REP-1029</td>
                                    <td class="py-4 text-on-surface-variant">15/05/2026</td>
                                    <td class="py-4 text-on-surface">El camión no pasó</td>
                                    <td class="py-4 text-primary font-semibold">Casa principal (David Este)</td>
                                    <td class="py-4 text-center">
                                        <span class="inline-block bg-green-100 text-[#00c46a] text-xs font-bold px-3 py-1 rounded-full">
                                            Resuelto
                                        </span>
                                    </td>
                                </tr>
                                <!-- Mock Row 2 -->
                                <tr class="border-b border-surface-container/60 hover:bg-surface-container/30 transition-colors">
                                    <td class="py-4 font-bold text-on-surface">REP-1042</td>
                                    <td class="py-4 text-on-surface-variant">28/05/2026</td>
                                    <td class="py-4 text-on-surface">Desperdicios dejados en vía</td>
                                    <td class="py-4 text-primary font-semibold">Casa principal (David Este)</td>
                                    <td class="py-4 text-center">
                                        <span class="inline-block bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full">
                                            En Proceso
                                        </span>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reportes as $rep): ?>
                                    <tr class="border-b border-surface-container/60 hover:bg-surface-container/30 transition-colors">
                                        <td class="py-4 font-bold text-on-surface">REP-<?= str_pad($rep['reporte_id'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td class="py-4 text-on-surface-variant"><?= date('d/m/Y', strtotime($rep['fecha_reporte'])) ?></td>
                                        <td class="py-4 text-on-surface"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $rep['tipo_incidencia']))) ?></td>
                                        <td class="py-4 text-primary font-semibold"><?= htmlspecialchars($rep['nombre_referencia']) ?></td>
                                        <td class="py-4 text-center">
                                            <?php 
                                            $estado = strtolower($rep['estado_reporte']);
                                            if ($estado === 'resuelto') {
                                                echo '<span class="inline-block bg-green-100 text-[#00c46a] text-xs font-bold px-3 py-1 rounded-full">Resuelto</span>';
                                            } elseif ($estado === 'en_proceso') {
                                                echo '<span class="inline-block bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full">En Proceso</span>';
                                            } else {
                                                echo '<span class="inline-block bg-red-100 text-red-600 text-xs font-bold px-3 py-1 rounded-full">Abierto</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../components/footer.php';
?>

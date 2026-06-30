<?php
require_once __DIR__ . '/../components/header.php';
require_once __DIR__ . '/../models/Reporte.php';

// Instanciar modelo de reportes para jalar comentarios en línea
$reporteModelHelper = new Reporte();

// Determinar ruta base para enlaces
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($base, -1) !== '/') {
    $base .= '/';
}
?>

<div class="max-w-[1200px] mx-auto px-6 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-primary">Reportes de la Comunidad y Ayuda</h1>
        <p class="text-on-surface-variant text-sm mt-1">Colabore con los vecinos reportando incidentes y participe en los hilos de soporte.</p>
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

            <!-- Redes Sociales -->
            <div class="bg-white p-6 rounded-lg border border-surface-container-high shadow-sm">
                <h3 class="text-lg font-bold text-primary mb-4">Nuestras Redes</h3>
                <div class="flex gap-4">
                    <a href="#" class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center text-primary hover:bg-primary hover:text-white transition-all">
                        <span class="font-bold">FB</span>
                    </a>
                    <a href="#" class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center text-primary hover:bg-primary hover:text-white transition-all">
                        <span class="font-bold">IG</span>
                    </a>
                    <a href="#" class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center text-primary hover:bg-primary hover:text-white transition-all">
                        <span class="font-bold">X</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Panel Derecho: Formulario de Incidencias e Historial Público -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Formulario de Reporte -->
            <div class="bg-white p-6 rounded-lg border border-surface-container-high shadow-sm">
                <h3 class="text-xl font-bold text-primary mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">report</span>
                    Reportar Incidencia de Recolección
                </h3>
                
                <form action="<?= $base ?>help" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Casa/Ruta Afectada:</label>
                        <select name="ruta_id" class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-sm text-on-surface focus:ring-2 focus:ring-primary outline-none">
                            <option value="">-- Seleccionar Casa (Opcional) --</option>
                            <?php foreach ($rutas as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Descripción Detallada del Problema:</label>
                        <textarea name="descripcion" rows="3" placeholder="Describa brevemente la incidencia (ej: el camión no pasó hoy, dejaron basura regada en la calle, etc.)" required class="w-full bg-surface-container/60 border-none rounded-2xl py-3 px-4 text-sm text-on-surface focus:ring-2 focus:ring-primary outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Evidencia Fotográfica (Opcional):</label>
                        <input name="foto_reporte" type="file" accept="image/*" class="block w-full text-xs text-on-surface-variant file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer"/>
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
                    <span class="material-symbols-outlined text-primary">forum</span>
                    Reportes Públicos de la Comunidad
                </h3>

                <?php if (empty($reportes)): ?>
                    <p class="text-on-surface-variant text-sm py-4">No hay reportes públicos registrados en este momento.</p>
                <?php else: ?>
                    <div class="space-y-6">
                        <?php foreach ($reportes as $rep): ?>
                            <?php 
                            $comentarios = $reporteModelHelper->getComentarios($rep['id']); 
                            $avatarUrl = !empty($rep['cliente_foto']) ? $base . $rep['cliente_foto'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
                            ?>
                            <!-- Tarjeta de Reporte -->
                            <div class="bg-surface-container/20 p-5 rounded-xl border border-surface-container flex flex-col gap-4">
                                <div class="flex justify-between items-start gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full overflow-hidden border border-primary/10">
                                            <img src="<?= $avatarUrl ?>" alt="Foto perfil" class="w-full h-full object-cover"/>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-bold text-on-surface"><?= htmlspecialchars($rep['cliente_nombre']) ?></span>
                                            <span class="text-[10px] text-on-surface-variant/80">Fecha: <?= date('d/m/Y H:i', strtotime($rep['created_at'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase 
                                            <?= ($rep['estado'] === 'Resuelto') ? 'bg-green-100 text-[#00c46a]' : (($rep['estado'] === 'En Proceso') ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-600') ?>">
                                            <?= htmlspecialchars($rep['estado']) ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Detalles del problema -->
                                <div>
                                    <?php if ($rep['ruta_nombre']): ?>
                                        <div class="text-xs text-primary font-semibold mb-1">Dirección: <?= htmlspecialchars($rep['ruta_nombre']) ?></div>
                                    <?php endif; ?>
                                    <p class="text-sm text-on-surface-variant leading-relaxed"><?= htmlspecialchars($rep['descripcion']) ?></p>
                                </div>

                                <!-- Evidencia Fotográfica -->
                                <?php if ($rep['foto_url']): ?>
                                    <div class="w-full max-h-60 rounded-lg overflow-hidden border border-surface-container-high bg-slate-100">
                                        <img src="<?= $base . htmlspecialchars($rep['foto_url']) ?>" alt="Evidencia" class="w-full h-full object-contain cursor-zoom-in" onclick="window.open(this.src)"/>
                                    </div>
                                <?php endif; ?>

                                <!-- Barra de Acciones del Reporte -->
                                <div class="border-t border-surface-container pt-3 flex justify-between items-center text-xs">
                                    <button onclick="toggleComments(<?= $rep['id'] ?>)" class="text-primary hover:text-secondary font-bold flex items-center gap-1 focus:outline-none">
                                        <span class="material-symbols-outlined text-[18px]">comment</span>
                                        Hilo de Respuestas (<?= count($comentarios) ?>)
                                    </button>
                                </div>

                                <!-- Cajón de Comentarios (Hilo de Discusión) -->
                                <div id="comments-container-<?= $rep['id'] ?>" class="hidden mt-2 bg-white/60 rounded-lg p-4 border border-surface-container/60 space-y-4">
                                    <!-- Listado de comentarios -->
                                    <div class="space-y-3">
                                        <?php if (empty($comentarios)): ?>
                                            <p class="text-xs text-on-surface-variant italic">No hay comentarios en este hilo. Sé el primero en responder.</p>
                                        <?php else: ?>
                                            <?php foreach ($comentarios as $com): ?>
                                                <?php 
                                                $comAvatar = !empty($com['usuario_foto']) ? $base . $com['usuario_foto'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
                                                ?>
                                                <div class="flex items-start gap-2 text-xs border-b border-surface-container/30 pb-3 last:border-0 last:pb-0">
                                                    <div class="w-8 h-8 rounded-full overflow-hidden border border-primary/10 flex-shrink-0">
                                                        <img src="<?= $comAvatar ?>" alt="Foto" class="w-full h-full object-cover"/>
                                                    </div>
                                                    <div class="flex-grow">
                                                        <div class="flex items-center gap-2">
                                                            <strong class="text-on-surface"><?= htmlspecialchars($com['usuario_nombre']) ?></strong>
                                                            <span class="text-[9px] font-bold uppercase px-2 py-0.2 rounded-full 
                                                                <?= ($com['usuario_rol'] === 'gestor') ? 'bg-amber-100 text-amber-800' : (($com['usuario_rol'] === 'conductor') ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') ?>">
                                                                <?= htmlspecialchars($com['usuario_rol']) ?>
                                                            </span>
                                                            <span class="text-[9px] text-on-surface-variant/70"><?= date('d/m/Y H:i', strtotime($com['fecha'])) ?></span>
                                                        </div>
                                                        <p class="text-on-surface-variant mt-1"><?= htmlspecialchars($com['comentario']) ?></p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Formulario de Respuesta en el Hilo -->
                                    <form action="<?= $base ?>help?action=comment" method="POST" class="flex gap-2 items-center pt-2 border-t border-surface-container/60">
                                        <input type="hidden" name="reporte_id" value="<?= $rep['id'] ?>"/>
                                        <input name="comentario" placeholder="Escriba su respuesta pública..." required class="flex-grow bg-surface-container/60 border-none rounded-full py-2 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                                        <button type="submit" class="bg-primary hover:bg-[#224f3c] text-white p-2 rounded-full flex items-center justify-center shadow-sm">
                                            <span class="material-symbols-outlined text-[18px]">send</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleComments(reporteId) {
        var container = document.getElementById('comments-container-' + reporteId);
        container.classList.toggle('hidden');
    }
</script>

<?php
require_once __DIR__ . '/../components/footer.php';
?>

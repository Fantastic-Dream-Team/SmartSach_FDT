<?php
require_once __DIR__ . '/../components/header.php';
require_once ROOT_PATH . '/backend/src/models/Reporte.php';

$reporteModelHelper = new Reporte();

// Determinar ruta base para enlaces
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($base, -1) !== '/') {
    $base .= '/';
}
?>

<div class="max-w-[1200px] mx-auto px-6 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-primary font-headline-lg">Administración de Reportes</h1>
        <p class="text-on-surface-variant text-sm mt-1">Gestione e interactúe con los tickets de incidencia y sugerencias de la comunidad.</p>
    </div>

    <!-- Contenedor Principal de Reportes -->
    <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm">
        <h3 class="text-xl font-bold text-primary mb-6 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">report</span>
            Bandeja de Entrada de Incidencias
        </h3>

        <?php if (empty($reportes)): ?>
            <p class="text-on-surface-variant text-sm py-4">No hay reportes de usuarios registrados en el sistema.</p>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($reportes as $rep): ?>
                    <?php 
                    $comentarios = $reporteModelHelper->getComentarios($rep['id']); 
                    $avatarUrl = !empty($rep['cliente_foto']) ? $base . $rep['cliente_foto'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
                    ?>
                    <!-- Tarjeta de Reporte para el Gestor -->
                    <div class="p-5 rounded-xl border flex flex-col gap-4 <?= !$rep['visto_gestor'] ? 'bg-amber-50/50 border-amber-200' : 'bg-surface-container/20 border-surface-container' ?>">
                        <div class="flex justify-between items-start gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full overflow-hidden border border-primary/10 flex-shrink-0">
                                    <img src="<?= $avatarUrl ?>" alt="Foto" class="w-full h-full object-cover"/>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <strong class="text-sm text-on-surface"><?= htmlspecialchars($rep['cliente_nombre']) ?></strong>
                                        <?php if (!$rep['visto_gestor']): ?>
                                            <span class="bg-amber-600 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">Nuevo</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-[10px] text-on-surface-variant/80">Fecha: <?= date('d/m/Y H:i', strtotime($rep['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="text-right flex items-center gap-2">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase 
                                    <?= ($rep['estado'] === 'Resuelto') ? 'bg-green-100 text-[#00c46a]' : (($rep['estado'] === 'En Proceso') ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-600') ?>">
                                    <?= htmlspecialchars($rep['estado']) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Detalle del problema -->
                        <div>
                            <?php if ($rep['ruta_nombre']): ?>
                                <div class="text-xs text-primary font-semibold mb-1">Ruta Afectada: <?= htmlspecialchars($rep['ruta_nombre']) ?></div>
                            <?php endif; ?>
                            <p class="text-sm text-on-surface-variant leading-relaxed"><?= htmlspecialchars($rep['descripcion']) ?></p>
                        </div>

                        <!-- Evidencia Fotográfica -->
                        <?php if ($rep['foto_url']): ?>
                            <div class="w-full max-h-60 rounded-lg overflow-hidden border border-surface-container-high bg-slate-100">
                                <img src="<?= $base . htmlspecialchars($rep['foto_url']) ?>" alt="Evidencia" class="w-full h-full object-contain cursor-zoom-in" onclick="window.open(this.src)"/>
                            </div>
                        <?php endif; ?>

                        <!-- Acciones del Gestor -->
                        <div class="border-t border-surface-container pt-3 flex flex-wrap justify-between items-center gap-2 text-xs">
                            <button onclick="toggleComments(<?= $rep['id'] ?>)" class="text-primary hover:text-secondary font-bold flex items-center gap-1 focus:outline-none">
                                <span class="material-symbols-outlined text-[18px]">comment</span>
                                Hilo de Discusión (<?= count($comentarios) ?>)
                            </button>

                            <div class="flex gap-2">
                                <!-- Botón Marcar Visto -->
                                <?php if (!$rep['visto_gestor']): ?>
                                    <form action="<?= $base ?>help?action=mark_seen" method="POST">
                                        <input type="hidden" name="reporte_id" value="<?= $rep['id'] ?>"/>
                                        <button type="submit" class="bg-primary hover:bg-[#224f3c] text-white px-4 py-1.5 rounded-full font-bold shadow-sm transition-all flex items-center gap-1 text-[11px]">
                                            <span class="material-symbols-outlined text-xs">visibility</span>
                                            Marcar como Visto
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Cajón de Comentarios (Hilo de Discusión) -->
                        <div id="comments-container-<?= $rep['id'] ?>" class="hidden mt-2 bg-white/60 rounded-lg p-4 border border-surface-container/60 space-y-4">
                            <!-- Listado de comentarios -->
                            <div class="space-y-3">
                                <?php if (empty($comentarios)): ?>
                                    <p class="text-xs text-on-surface-variant italic">No hay comentarios en este hilo.</p>
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

                            <!-- Responder al reporte -->
                            <form action="<?= $base ?>help?action=comment" method="POST" class="flex gap-2 items-center pt-2 border-t border-surface-container/60">
                                <input type="hidden" name="reporte_id" value="<?= $rep['id'] ?>"/>
                                <input name="comentario" placeholder="Responder al usuario en este reporte..." required class="flex-grow bg-surface-container/60 border-none rounded-full py-2 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
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

<script>
    function toggleComments(reporteId) {
        var container = document.getElementById('comments-container-' + reporteId);
        container.classList.toggle('hidden');
    }
</script>

<?php
require_once __DIR__ . '/../components/footer.php';
?>

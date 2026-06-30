<?php
require_once __DIR__ . '/../components/header.php';

// Determinar ruta base para enlaces
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($base, -1) !== '/') {
    $base .= '/';
}
?>

<div class="max-w-[1200px] mx-auto px-6 py-8">
    <div class="mb-8 flex justify-between items-center flex-wrap gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-primary">Gestión de Novedades y Noticias</h1>
            <p class="text-on-surface-variant text-sm mt-1">Publique consejos de reciclaje, cambios de horario o comunicados importantes para los clientes.</p>
        </div>
        <a href="<?= $base ?>profile" class="bg-primary/10 text-primary hover:bg-primary/20 px-4 py-2 rounded-full font-bold text-xs flex items-center gap-1">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            Volver a Consola
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Formulario para publicar noticia -->
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm sticky top-24">
                <h3 class="text-lg font-bold text-primary mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#00c46a]">rate_review</span>
                    Publicar Novedad
                </h3>
                
                <form action="<?= $base ?>news?action=create" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Título del Comunicado:</label>
                        <input name="titulo" placeholder="Ej. Cambio de horario por festivos" type="text" required class="w-full bg-surface-container/60 border-none rounded-full py-2.5 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-primary mb-1 uppercase">Contenido / Cuerpo:</label>
                        <textarea name="contenido" rows="6" placeholder="Escriba aquí los detalles del anuncio..." required class="w-full bg-surface-container/60 border-none rounded-xl py-2.5 px-4 text-xs text-on-surface focus:ring-2 focus:ring-primary outline-none resize-none"></textarea>
                    </div>
                    
                    <button type="submit" class="w-full bg-secondary hover:bg-[#00ab5d] text-white py-2.5 rounded-full font-bold shadow-md transition-all active:scale-95 text-xs flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-base">send</span>
                        Publicar Noticia
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de Noticias Publicadas -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm">
                <h3 class="text-xl font-bold text-primary mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">newspaper</span>
                    Noticias Publicadas (<?= count($noticias) ?>)
                </h3>

                <?php if (empty($noticias)): ?>
                    <div class="text-center py-12 text-on-surface-variant/70 italic text-xs">
                        <span class="material-symbols-outlined text-4xl block mb-2 opacity-50">drafts</span>
                        No hay noticias publicadas en el sistema.
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($noticias as $n): ?>
                            <div class="p-5 rounded-xl border border-surface-container/80 bg-surface-container/10 flex justify-between items-start gap-4">
                                <div class="space-y-2 flex-grow">
                                    <h4 class="font-bold text-base text-primary"><?= htmlspecialchars($n['titulo']) ?></h4>
                                    <p class="text-xs text-on-surface leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($n['contenido']) ?></p>
                                    
                                    <div class="flex items-center gap-3 text-[10px] text-on-surface-variant/80 pt-2 border-t border-surface-container/60">
                                        <span class="flex items-center gap-0.5">
                                            <span class="material-symbols-outlined text-xs">person</span>
                                            Autor: <?= htmlspecialchars($n['autor_nombre'] ?: 'Sistema') ?>
                                        </span>
                                        <span>•</span>
                                        <span class="flex items-center gap-0.5">
                                            <span class="material-symbols-outlined text-xs">calendar_month</span>
                                            <?= date('d M Y, h:i A', strtotime($n['fecha_publicacion'])) ?>
                                        </span>
                                    </div>
                                </div>

                                <form action="<?= $base ?>news?action=delete" method="POST" onsubmit="return confirm('¿Seguro que desea eliminar esta noticia?');" class="flex-shrink-0">
                                    <input type="hidden" name="id" value="<?= $n['id'] ?>"/>
                                    <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-800 p-2 rounded-full transition-all">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </form>
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

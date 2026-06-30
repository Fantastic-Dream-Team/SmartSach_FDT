<?php
require_once __DIR__ . '/../components/header.php';

// Determinar ruta base para enlaces
$base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($base, -1) !== '/') {
    $base .= '/';
}
?>

<div class="max-w-[1200px] mx-auto px-6 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-primary font-headline-lg">Historial de Pagos y Facturación</h1>
        <p class="text-on-surface-variant text-sm mt-1">Consulte sus recibos anteriores y realice el pago de sus mensualidades acumuladas.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Panel Izquierdo / Resumen de Estado Financiero -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Tarjeta de Saldo -->
            <div class="bg-white p-6 rounded-lg border border-surface-container-high shadow-sm">
                <span class="text-xs font-semibold text-on-surface-variant/70 uppercase">Total a Pagar</span>
                <h3 class="text-4xl font-black text-primary mt-2">$<?= number_format($saldoPendiente, 2) ?></h3>
                
                <div class="mt-4 pt-4 border-t border-surface-container space-y-2">
                    <div class="flex justify-between text-xs text-on-surface-variant">
                        <span>Estado de Cuenta:</span>
                        <span class="font-bold <?= ($saldoPendiente > 0) ? 'text-red-600' : 'text-[#00c46a]' ?>">
                            <?= ($saldoPendiente > 0) ? 'Moroso' : 'Paz y Salvo' ?>
                        </span>
                    </div>
                    <div class="flex justify-between text-xs text-on-surface-variant">
                        <span>Próximo vencimiento:</span>
                        <span class="font-bold">5 de <?= date('F, Y', strtotime('+1 month')) ?></span>
                    </div>
                </div>

                <!-- Botón de Pago Simulado -->
                <?php if ($saldoPendiente > 0 && isset($suscripcionMorosa)): ?>
                    <form action="<?= $base ?>payments" method="POST" class="mt-6">
                        <input type="hidden" name="suscripcion_id" value="<?= htmlspecialchars($suscripcionMorosa) ?>">
                        <button type="submit" class="w-full bg-secondary hover:bg-[#00ab5d] text-white py-3 rounded-full font-bold shadow-md transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">payments</span>
                            Simular Pago Exitoso
                        </button>
                    </form>
                <?php else: ?>
                    <button disabled class="w-full bg-[#9bb2a8]/30 text-on-surface/50 py-3 rounded-full font-bold mt-6 cursor-not-allowed flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">check_circle</span>
                        Al Día / Sin Deudas
                    </button>
                <?php endif; ?>
            </div>

            <!-- Información Adicional -->
            <div class="bg-[#163a6c] text-white p-6 rounded-lg shadow-sm">
                <h4 class="font-bold text-base mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined">info</span> Informante de Cobro
                </h4>
                <p class="text-xs opacity-90 leading-relaxed">
                    Las tarifas de smartSACH se calculan en base a la ubicación y dificultad de acceso de las viviendas registradas. El cargo mensual se genera de manera automática los primeros días de cada mes.
                </p>
            </div>
        </div>

        <!-- Tabla / Cronología de Pagos -->
        <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-lg border border-surface-container-high shadow-sm">
                <h3 class="text-xl font-bold text-primary mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined">receipt_long</span>
                    Historial de Facturas
                </h3>

                <?php if (empty($historial)): ?>
                    <div class="text-center py-12">
                        <span class="material-symbols-outlined text-4xl text-on-surface-variant/40">credit_card_off</span>
                        <p class="text-on-surface-variant text-sm mt-2">No se encontraron transacciones registradas.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-surface-container text-on-surface-variant font-semibold">
                                    <th class="pb-3 font-medium">Concepto/Fecha</th>
                                    <th class="pb-3 font-medium text-center">Referencia</th>
                                    <th class="pb-3 font-medium text-center">Estado</th>
                                    <th class="pb-3 font-medium text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historial as $pago): ?>
                                    <tr class="border-b border-surface-container/60 hover:bg-surface-container/30 transition-colors">
                                        <td class="py-4">
                                            <div class="font-semibold text-on-surface">Servicio Mensual smartSACH</div>
                                            <div class="text-xs text-on-surface-variant mt-0.5">
                                                Generado: <?= date('d/m/Y', strtotime($pago['created_at'])) ?>
                                                <?php if ($pago['fecha_pago']): ?>
                                                    | Pagado: <?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])) ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="py-4 text-center font-mono text-xs text-on-surface-variant">
                                            <?= $pago['referencia'] ?: '-' ?>
                                        </td>
                                        <td class="py-4 text-center">
                                            <?php if ($pago['estado'] === 'Pagado'): ?>
                                                <span class="inline-block bg-green-100 text-[#00c46a] text-xs font-bold px-3 py-1 rounded-full">
                                                    Pagado
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-block bg-red-100 text-red-600 text-xs font-bold px-3 py-1 rounded-full">
                                                    Pendiente
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 text-right font-bold text-on-surface">
                                            $<?= number_format($pago['monto'], 2) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../components/footer.php';
?>

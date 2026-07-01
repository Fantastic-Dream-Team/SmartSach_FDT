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

    <!-- Banner de Mora si aplica -->
    <?php if (isset($tieneDeuda) && $tieneDeuda): ?>
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-r-lg flex items-center gap-3">
            <span class="material-symbols-outlined text-red-500 text-2xl">warning</span>
            <div>
                <strong class="font-bold">Cuenta en Mora:</strong> 
                <span>Tiene un saldo pendiente de $15.00. Realice el pago para evitar suspensión del servicio.</span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Nueva Estructura: 2 Tarjetas Superiores Lado a Lado -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Tarjeta Próximo Pago -->
        <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm flex flex-col justify-between">
            <div>
                <span class="text-xs font-semibold text-on-surface-variant/70 uppercase tracking-wider block">Próximo Pago</span>
                <span class="text-3xl font-black text-primary mt-2 block">$15.00</span>
                <span class="text-xs text-on-surface-variant block mt-1">Próximo corte</span>
            </div>
            <div class="mt-4">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                    Pendiente
                </span>
            </div>
        </div>

        <!-- Tarjeta Estado de Cuenta -->
        <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm flex flex-col justify-between">
            <div>
                <span class="text-xs font-semibold text-on-surface-variant/70 uppercase tracking-wider block">Estado de Cuenta</span>
                <span class="text-2xl font-black mt-2 block <?= (isset($tieneDeuda) && $tieneDeuda) ? 'text-red-600' : 'text-[#00c46a]' ?>">
                    <?= (isset($tieneDeuda) && $tieneDeuda) ? 'Moroso' : 'Al Día' ?>
                </span>
                <span class="text-xs text-on-surface-variant block mt-1">Suscripción SmartSACH</span>
            </div>
            <div class="mt-4">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold <?= (isset($tieneDeuda) && $tieneDeuda) ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                    <?= (isset($tieneDeuda) && $tieneDeuda) ? 'Mora' : 'Activo' ?>
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Columna Izquierda: Selección de Método de Pago -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm">
                <h3 class="text-base font-bold text-primary mb-4">Selecciona tu método de pago</h3>
                
                <!-- Métodos de Pago Interactivos -->
                <div class="space-y-3" id="payment-methods">
                    <button type="button" onclick="selectMethod('tarjeta')" id="method-tarjeta" class="w-full flex items-center justify-between p-3.5 rounded-xl border-2 border-surface-container hover:border-primary transition-all text-sm font-semibold text-on-surface text-left">
                        <span class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                            Tarjeta de Crédito / Débito
                        </span>
                        <span class="w-4 h-4 rounded-full border border-on-surface-variant/40 flex items-center justify-center" id="radio-tarjeta"></span>
                    </button>
                    
                    <button type="button" onclick="selectMethod('paypal')" id="method-paypal" class="w-full flex items-center justify-between p-3.5 rounded-xl border-2 border-surface-container hover:border-primary transition-all text-sm font-semibold text-on-surface text-left">
                        <span class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#003087]"><path d="M7.144 19.532l1.049-5.751c.11-.606.691-1.002 1.304-.948.337.027 1.343.149 1.942.149 2.145 0 3.73-.559 4.38-2.671.305-1.026.155-2.062-.355-2.736-.615-.815-1.776-1.12-3.136-1.12H8.761c-.604 0-1.126.43-1.226 1.029L5.05 19.532h2.094z"></path><path d="M10.158 2.22c.11-.606.691-1.002 1.304-.948 1.401.11 3.559.278 4.757.278 2.502 0 4.35-.65 5.109-3.109.355-1.196.181-2.404-.414-3.19-.716-.95-2.072-1.306-3.659-1.306h-4.148c-.705 0-1.314.502-1.43 1.2L9.208 9.48c-.029.155.088.303.245.303h1.831l.874-4.8c.11-.606.691-1.002 1.304-.948.337.027 1.343.149 1.942.149 1.625 0 2.825-.422 3.318-2.023.23-.746.12-1.498-.266-1.99-.441-.584-1.272-.803-2.247-.803H12.75c-.352 0-.657.251-.715.6L10.158 2.22z"></path></svg>
                            PayPal
                        </span>
                        <span class="w-4 h-4 rounded-full border border-on-surface-variant/40 flex items-center justify-center" id="radio-paypal"></span>
                    </button>

                    <button type="button" onclick="selectMethod('ach')" id="method-ach" class="w-full flex items-center justify-between p-3.5 rounded-xl border-2 border-surface-container hover:border-primary transition-all text-sm font-semibold text-on-surface text-left">
                        <span class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M3 21h18"></path><path d="M3 10h18"></path><path d="M5 6l7-3 7 3"></path><path d="M4 10v11"></path><path d="M20 10v11"></path><path d="M8 14v3"></path><path d="M12 14v3"></path><path d="M16 14v3"></path></svg>
                            Banco Local (ACH)
                        </span>
                        <span class="w-4 h-4 rounded-full border border-on-surface-variant/40 flex items-center justify-center" id="radio-ach"></span>
                    </button>
                </div>

                <!-- Botón de Pago -->
                <?php if (isset($tieneDeuda) && $tieneDeuda && isset($suscripcionMorosa)): ?>
                    <form action="<?= $base ?>payments" method="POST" id="simulated-payment-form" onsubmit="return confirmPayment(event)" class="mt-6">
                        <input type="hidden" name="suscripcion_id" value="<?= htmlspecialchars($suscripcionMorosa) ?>">
                        <input type="hidden" name="metodo_pago" id="selected-method-val" value="">
                        <button type="submit" class="w-full bg-secondary hover:bg-[#00ab5d] text-white py-3 rounded-full font-bold shadow-md transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">payments</span>
                            Pagar Ahora
                        </button>
                    </form>
                <?php else: ?>
                    <button disabled class="w-full bg-[#9bb2a8]/30 text-on-surface/50 py-3 rounded-full font-bold mt-6 cursor-not-allowed flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">check_circle</span>
                        Al Día / Sin Deudas
                    </button>
                <?php endif; ?>
            </div>

            <!-- Informante -->
            <div class="bg-[#163a6c] text-white p-5 rounded-xl shadow-sm">
                <h4 class="font-bold text-sm mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined">info</span> Informante de Cobro
                </h4>
                <p class="text-[11px] opacity-90 leading-relaxed">
                    Las tarifas se calculan automáticamente según la zona y nivel de acceso de sus propiedades registradas. El cobro se realiza los primeros días de cada mes.
                </p>
            </div>
        </div>

        <!-- Historial de Facturas -->
        <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-xl border border-surface-container-high shadow-sm">
                <h3 class="text-xl font-bold text-primary mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">receipt_long</span>
                    Historial de Facturas
                </h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-surface-container text-on-surface-variant font-semibold">
                                <th class="pb-3 font-medium">Factura / Concepto</th>
                                <th class="pb-3 font-medium text-center">Referencia</th>
                                <th class="pb-3 font-medium text-center">Estado</th>
                                <th class="pb-3 font-medium text-right">Monto</th>
                                <th class="pb-3 font-medium text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Mock Row 1 -->
                            <tr class="border-b border-surface-container/60 hover:bg-surface-container/30 transition-colors">
                                <td class="py-4">
                                    <div class="font-semibold text-on-surface">FCT-2026-001 (Servicio smartSACH)</div>
                                    <div class="text-xs text-on-surface-variant mt-0.5">Generado: 01/03/2026</div>
                                </td>
                                <td class="py-4 text-center font-mono text-xs text-on-surface-variant">REF-92837</td>
                                <td class="py-4 text-center">
                                    <span class="inline-block bg-green-100 text-[#00c46a] text-xs font-bold px-3 py-1 rounded-full">
                                        Pagado
                                    </span>
                                </td>
                                <td class="py-4 text-right font-bold text-on-surface">$10.00</td>
                                <td class="py-4 text-right">
                                    <a href="#" onclick="alert('Descargando factura FCT-2026-001 en formato PDF...')" class="text-secondary hover:underline font-bold text-xs inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">download</span> PDF
                                    </a>
                                </td>
                            </tr>
                            
                            <!-- Mock Row 2 -->
                            <tr class="border-b border-surface-container/60 hover:bg-surface-container/30 transition-colors">
                                <td class="py-4">
                                    <div class="font-semibold text-on-surface">FCT-2026-002 (Servicio smartSACH)</div>
                                    <div class="text-xs text-on-surface-variant mt-0.5">Generado: 01/04/2026</div>
                                </td>
                                <td class="py-4 text-center font-mono text-xs text-on-surface-variant">REF-92838</td>
                                <td class="py-4 text-center">
                                    <span class="inline-block bg-green-100 text-[#00c46a] text-xs font-bold px-3 py-1 rounded-full">
                                        Pagado
                                    </span>
                                </td>
                                <td class="py-4 text-right font-bold text-on-surface">$10.00</td>
                                <td class="py-4 text-right">
                                    <a href="#" onclick="alert('Descargando factura FCT-2026-002 en formato PDF...')" class="text-secondary hover:underline font-bold text-xs inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">download</span> PDF
                                    </a>
                                </td>
                            </tr>

                            <!-- Row Variable según estado del backend -->
                            <tr class="border-b border-surface-container/60 hover:bg-surface-container/30 transition-colors">
                                <td class="py-4">
                                    <div class="font-semibold text-on-surface">FCT-2026-003 (Servicio smartSACH)</div>
                                    <div class="text-xs text-on-surface-variant mt-0.5">Generado: 01/05/2026</div>
                                </td>
                                <td class="py-4 text-center font-mono text-xs text-on-surface-variant">REF-92839</td>
                                <td class="py-4 text-center">
                                    <span class="inline-block <?= (isset($tieneDeuda) && $tieneDeuda) ? 'bg-red-100 text-red-600' : 'bg-green-100 text-[#00c46a]' ?> text-xs font-bold px-3 py-1 rounded-full">
                                        <?= (isset($tieneDeuda) && $tieneDeuda) ? 'Pendiente' : 'Pagado' ?>
                                    </span>
                                </td>
                                <td class="py-4 text-right font-bold text-on-surface">$15.00</td>
                                <td class="py-4 text-right">
                                    <?php if (isset($tieneDeuda) && $tieneDeuda): ?>
                                        <span class="text-on-surface-variant/40 text-xs">No disponible</span>
                                    <?php else: ?>
                                        <a href="#" onclick="alert('Descargando factura FCT-2026-003 en formato PDF...')" class="text-secondary hover:underline font-bold text-xs inline-flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">download</span> PDF
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let selectedMethod = '';

    function selectMethod(method) {
        selectedMethod = method;
        document.getElementById('selected-method-val').value = method;

        // Reset borders
        document.querySelectorAll('#payment-methods button').forEach(btn => {
            btn.classList.remove('border-primary', 'bg-primary/5');
            btn.classList.add('border-surface-container');
        });
        
        // Reset radio dots
        document.querySelectorAll('#payment-methods button span[id^="radio-"]').forEach(dot => {
            dot.innerHTML = '';
        });

        // Set selected
        const activeBtn = document.getElementById('method-' + method);
        activeBtn.classList.remove('border-surface-container');
        activeBtn.classList.add('border-primary', 'bg-primary/5');

        const activeDot = document.getElementById('radio-' + method);
        activeDot.innerHTML = '<span class="w-2.5 h-2.5 bg-primary rounded-full inline-block"></span>';
    }

    function confirmPayment(e) {
        if (!selectedMethod) {
            alert('Por favor, selecciona un método de pago antes de continuar.');
            return false;
        }
        
        let methodStr = '';
        if (selectedMethod === 'tarjeta') methodStr = 'Tarjeta de Crédito/Débito';
        if (selectedMethod === 'paypal') methodStr = 'PayPal';
        if (selectedMethod === 'ach') methodStr = 'ACH / Banco Local';

        return confirm(`¿Confirmar pago de $15.00 usando ${methodStr}?`);
    }
</script>

<?php
require_once __DIR__ . '/../components/footer.php';
?>

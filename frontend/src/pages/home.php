<?php
require_once __DIR__ . '/../components/header.php';
?>

<!-- Hero Section -->
<section class="relative bg-primary text-white py-20 px-6 overflow-hidden">
    <div class="absolute inset-0 opacity-20 bg-[url('https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&q=80&w=1200')] bg-cover bg-center"></div>
    <div class="max-w-[1200px] mx-auto relative z-10 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div>
            <span class="bg-[#00c46a] text-white text-xs font-bold uppercase tracking-wider px-3 py-1.5 rounded-full">Servicios Ambientales de Chiriquí</span>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mt-4 leading-tight">
                Hacia una provincia limpia, verde y sostenible
            </h1>
            <p class="text-lg opacity-90 mt-6 max-w-lg leading-relaxed">
                smartSACH es el sistema inteligente de recolección de residuos urbanos y rurales en la provincia de Chiriquí. Facilitamos la administración del servicio para su hogar o empresa.
            </p>
            <div class="flex flex-wrap gap-4 mt-8">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard" class="bg-[#00c46a] hover:bg-[#00ab5d] text-white px-8 py-3 rounded-full font-bold shadow-lg transition-all active:scale-95">Ir a mi Panel de Control</a>
                <?php else: ?>
                    <a href="auth" class="bg-[#00c46a] hover:bg-[#00ab5d] text-white px-8 py-3 rounded-full font-bold shadow-lg transition-all active:scale-95">Comenzar Ahora</a>
                    <a href="#nosotros" class="border-2 border-white/60 hover:border-white text-white px-8 py-3 rounded-full font-bold transition-all hover:bg-white/10">Saber Más</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hidden md:flex justify-center">
            <!-- Representación gráfica o logo gigante -->
            <div class="w-80 h-80 bg-white/10 backdrop-blur-md border border-white/20 rounded-full flex items-center justify-center p-8 animate-pulse">
                <img alt="Eco logo" class="w-full h-full object-contain brightness-0 invert" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDSXzdK1Y0U25femz4FceW4dcn4EEus8_VmUopdkAn62o0QjClHqHNLAXMRnWJPCk06SHFC_Buo0ZPiuy2hPGB-CHcm5epb-t3CqyOdFCw_uMPeqiMF8lwVBqUG9C1ERsCMhDKHfhFgzorFB_PauVxI63nxrWZaXk9kpQ7EdeeE7NLShdopxlL2Nuqs_y2e4heZPRTKyFCV8gTz1ivQXXisMwmmSw5JQ1r8ET9tnOda80kiaPvhantjm4T1N21EM1kzRzLzK_vpvm8"/>
            </div>
        </div>
    </div>
</section>

<!-- Misión, Visión, Historia -->
<section id="nosotros" class="py-16 max-w-[1200px] mx-auto px-6">
    <div class="text-center mb-12">
        <h2 class="text-3xl font-bold text-primary">Nuestra Organización</h2>
        <div class="w-20 h-1 bg-[#00c46a] mx-auto mt-2 rounded-full"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Misión -->
        <div class="bg-white p-8 rounded-lg border border-surface-container shadow-sm hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                <span class="material-symbols-outlined text-2xl font-bold">assignment_ind</span>
            </div>
            <h3 class="text-xl font-bold text-primary mb-3">Nuestra Misión</h3>
            <p class="text-on-surface-variant text-sm leading-relaxed">
                Brindar soluciones integrales en la gestión y recolección de residuos sólidos en Chiriquí, utilizando tecnología de punta para optimizar rutas, mitigar el impacto ecológico y elevar la calidad de vida de nuestra comunidad.
            </p>
        </div>

        <!-- Visión -->
        <div class="bg-white p-8 rounded-lg border border-surface-container shadow-sm hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                <span class="material-symbols-outlined text-2xl font-bold">visibility</span>
            </div>
            <h3 class="text-xl font-bold text-primary mb-3">Nuestra Visión</h3>
            <p class="text-on-surface-variant text-sm leading-relaxed">
                Convertirnos para el año 2030 en la empresa líder de servicios ambientales de Panamá Occidental, reconocidos por nuestra innovación digital, eficiencia operativa y el fomento de una cultura cívica de reciclaje.
            </p>
        </div>

        <!-- Historia -->
        <div class="bg-white p-8 rounded-lg border border-surface-container shadow-sm hover:shadow-md transition-shadow">
            <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center text-primary mb-6">
                <span class="material-symbols-outlined text-2xl font-bold">history</span>
            </div>
            <h3 class="text-xl font-bold text-primary mb-3">Nuestra Historia</h3>
            <p class="text-on-surface-variant text-sm leading-relaxed">
                Fundados en David, Chiriquí, comenzamos como un servicio de recolección vecinal. Hoy, adaptándonos al crecimiento tecnológico y de población, lanzamos smartSACH para conectar directamente a los ciudadanos con el camión en tiempo real.
            </p>
        </div>
    </div>
</section>

<!-- Beneficios del Sistema -->
<section class="bg-surface-container py-16 px-6">
    <div class="max-w-[1200px] mx-auto grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div>
            <h2 class="text-3xl font-bold text-primary">¿Por qué usar la plataforma smartSACH?</h2>
            <p class="text-on-surface-variant mt-4 leading-relaxed">
                Nuestra plataforma no solo moderniza el servicio de recolección de residuos en Chiriquí, sino que pone el control en manos del usuario:
            </p>
            <ul class="space-y-4 mt-6">
                <li class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-secondary mt-0.5">check_circle</span>
                    <div>
                        <strong>Seguimiento de rutas en tiempo real:</strong> Sepa exactamente si el camión pasará hoy frente a su casa o si la ruta está inactiva.
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-secondary mt-0.5">check_circle</span>
                    <div>
                        <strong>Pagos Simples & Historial Transparente:</strong> Realice y simule pagos, verifique su estado de cuenta (Paz y Salvo/Moroso) y evite cortes del servicio.
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-secondary mt-0.5">check_circle</span>
                    <div>
                        <strong>Atención y Reportes de Servicio:</strong> Reporte fallas, demoras o problemas adjuntando fotos para que nuestro equipo le dé pronta solución.
                    </div>
                </li>
            </ul>
        </div>
        <div class="relative h-64 md:h-96 rounded-xl overflow-hidden shadow-lg border border-surface-container-high">
            <img alt="Camión de basura" class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1611284446314-60a58ac0deb9?auto=format&fit=crop&q=80&w=800"/>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/../components/footer.php';
?>

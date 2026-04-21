<?php $pageTitle = 'Configuración'; ?>
<?php require_once __DIR__ . '/layout/header.view.php'; ?>
<?php require_once __DIR__ . '/layout/sidebar.view.php'; ?>

<?php
    // Metadatos visuales de cada grupo
    $categoryLabels = [
        'Premium'  => ['icon' => 'fas fa-star',       'color' => 'yellow'],
        'Limites'  => ['icon' => 'fas fa-sliders-h',  'color' => 'blue'],
        'Reportes' => ['icon' => 'fas fa-flag',       'color' => 'red'],
        'Sistema'  => ['icon' => 'fas fa-cog',        'color' => 'purple'],
        'Contacto' => ['icon' => 'fas fa-envelope',   'color' => 'emerald'],
    ];

    // Etiquetas amigables + ayuda + unidad por cada clave tecnica
    $configMeta = [
        'premium_precio_cents' => [
            'label' => 'Precio Premium',
            'hint'  => 'Se guarda en céntimos (499 = 4,99 EUR)',
            'unit'  => 'céntimos',
        ],
        'premium_dias_defecto' => [
            'label' => 'Duración de Premium por defecto',
            'hint'  => 'Cuántos días dura el Premium al concederlo manualmente',
            'unit'  => 'días',
        ],
        'max_anuncios_gratis' => [
            'label' => 'Anuncios gratis por usuario',
            'hint'  => 'Límite de anuncios que puede publicar un usuario no Premium',
            'unit'  => 'anuncios',
        ],
        'max_evidencia_mb' => [
            'label' => 'Tamaño máximo de evidencia',
            'hint'  => 'Peso máximo permitido al adjuntar evidencia en un reporte',
            'unit'  => 'MB',
        ],
        'suspension_dias_defecto' => [
            'label' => 'Duración de suspensión por defecto',
            'hint'  => 'Días que dura una suspensión estándar si no se especifica otra',
            'unit'  => 'días',
        ],
        'motivos_reporte' => [
            'label' => 'Motivos de reporte disponibles',
            'hint'  => 'Lista JSON con los motivos que puede seleccionar el usuario al reportar',
            'unit'  => '',
        ],
        'contacto_email' => [
            'label' => 'Email de soporte',
            'hint'  => 'Dirección visible en el footer y en los correos del sistema',
            'unit'  => '',
        ],
        'mantenimiento' => [
            'label' => 'Modo mantenimiento',
            'hint'  => 'Bloquea el acceso a la web a usuarios no admin',
            'unit'  => '',
        ],
    ];
?>

<main class="ml-[72px] flex-1 min-h-screen flex flex-col">
    <?php require_once __DIR__ . '/layout/topbar.view.php'; ?>
    <div class="flex-1 p-10 max-w-4xl">

        <!-- Mensajes flash -->
        <?php if ($successMsg): ?>
            <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-base flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                <?= htmlspecialchars($successMsg) ?>
            </div>
        <?php endif; ?>
        <?php if ($errorMsg): ?>
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-base flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
                <?= htmlspecialchars($errorMsg) ?>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form action="<?= url('/admin/config/update') ?>" method="POST" class="space-y-8">

            <?php foreach ($grouped as $group => $configs): ?>
                <?php
                    $meta = $categoryLabels[$group] ?? ['icon' => 'fas fa-wrench', 'color' => 'gray'];
                    $color = $meta['color'];
                ?>

                <!-- Grupo: <?= $group ?> -->
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-<?= $color ?>-500/10 flex items-center justify-center">
                            <i class="<?= $meta['icon'] ?> text-<?= $color ?>-400 text-sm"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-white"><?= htmlspecialchars($group) ?></h2>
                    </div>

                    <div class="space-y-3">
                        <?php foreach ($configs as $cfg):
                            $meta  = $configMeta[$cfg['clave']] ?? null;
                            $label = $meta['label'] ?? ($cfg['descripcion'] ?: ucfirst(str_replace('_', ' ', $cfg['clave'])));
                            $hint  = $meta['hint']  ?? '';
                            $unit  = $meta['unit']  ?? '';
                        ?>
                            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-5 flex items-center justify-between gap-6">
                                <div class="flex-1 min-w-0">
                                    <label for="config_<?= htmlspecialchars($cfg['clave']) ?>" class="text-base font-medium text-white block">
                                        <?= htmlspecialchars($label) ?>
                                    </label>
                                    <?php if ($hint !== ''): ?>
                                        <p class="text-sm text-gray-500 mt-0.5"><?= htmlspecialchars($hint) ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="shrink-0 flex items-center gap-2">
                                    <?php if ($cfg['tipo'] === 'bool'): ?>
                                        <!-- Toggle switch para booleanos -->
                                        <input type="hidden" name="config[<?= htmlspecialchars($cfg['clave']) ?>]" value="0">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox"
                                                   name="config[<?= htmlspecialchars($cfg['clave']) ?>]"
                                                   id="config_<?= htmlspecialchars($cfg['clave']) ?>"
                                                   value="1"
                                                   <?= $cfg['valor'] ? 'checked' : '' ?>
                                                   class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer
                                                        peer-checked:after:translate-x-full peer-checked:after:border-white
                                                        after:content-[''] after:absolute after:top-[2px] after:start-[2px]
                                                        after:bg-white after:border-gray-300 after:border after:rounded-full
                                                        after:h-5 after:w-5 after:transition-all
                                                        peer-checked:bg-primary"></div>
                                        </label>

                                    <?php elseif ($cfg['tipo'] === 'int'): ?>
                                        <!-- Input numerico -->
                                        <input type="number"
                                               name="config[<?= htmlspecialchars($cfg['clave']) ?>]"
                                               id="config_<?= htmlspecialchars($cfg['clave']) ?>"
                                               value="<?= htmlspecialchars($cfg['valor']) ?>"
                                               class="w-32 bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-2.5 text-base text-white
                                                      focus:border-primary focus:ring-1 focus:ring-primary outline-none transition">

                                    <?php elseif ($cfg['tipo'] === 'json'): ?>
                                        <!-- Textarea para JSON -->
                                        <textarea name="config[<?= htmlspecialchars($cfg['clave']) ?>]"
                                                  id="config_<?= htmlspecialchars($cfg['clave']) ?>"
                                                  rows="3"
                                                  class="w-72 bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-white font-mono
                                                         focus:border-primary focus:ring-1 focus:ring-primary outline-none transition resize-y"
                                        ><?= htmlspecialchars($cfg['valor']) ?></textarea>

                                    <?php else: ?>
                                        <!-- Input de texto -->
                                        <input type="text"
                                               name="config[<?= htmlspecialchars($cfg['clave']) ?>]"
                                               id="config_<?= htmlspecialchars($cfg['clave']) ?>"
                                               value="<?= htmlspecialchars($cfg['valor']) ?>"
                                               class="w-64 bg-gray-800/60 border border-gray-700 rounded-lg px-4 py-2.5 text-base text-white
                                                      focus:border-primary focus:ring-1 focus:ring-primary outline-none transition">
                                    <?php endif; ?>

                                    <?php if ($unit !== '' && $cfg['tipo'] !== 'bool'): ?>
                                        <span class="text-sm text-gray-500 font-medium"><?= htmlspecialchars($unit) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Botón guardar -->
            <div class="pt-4 border-t border-gray-700">
                <button type="submit"
                        class="px-8 py-3 bg-primary hover:bg-primary/90 text-white font-semibold text-base rounded-lg transition-colors">
                    Guardar cambios
                </button>
            </div>

        </form>

    </div>
</main>

<?php require_once __DIR__ . '/layout/footer.view.php'; ?>

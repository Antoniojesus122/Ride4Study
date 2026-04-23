<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    } 

    $isLoggedIn = isset($_SESSION['user_id']);
    $userName = $_SESSION['user_name'] ?? '';
    $userInitial = !empty($userName) ? strtoupper(substr($userName, 0, 1)) : 'U';

    // Notificaciones y estado premium (solo si está logueado)
    $unreadNotifCount = 0;
    $userIsPremium = false;
        if ($isLoggedIn) {
            require_once __DIR__ . '/../../config/database.php';
            require_once __DIR__ . '/../../app/models/Notification.php';

            $headerDb = (new Database())->connect();
            $notifModel = new Notification($headerDb);
            $unreadNotifCount = $notifModel->countUnread((int)$_SESSION['user_id']);
            $premStmt = $headerDb->prepare("SELECT premium, premium_hasta FROM usuarios WHERE idUsuario = :id LIMIT 1");
            $premStmt->execute([':id' => $_SESSION['user_id']]);
            $premRow = $premStmt->fetch(PDO::FETCH_ASSOC);
            $userIsPremium = $premRow && $premRow['premium'] && (!$premRow['premium_hasta'] || $premRow['premium_hasta'] > date('Y-m-d H:i:s'));
        }
?>

<!DOCTYPE html>
  <html lang="<?= currentLang() ?>" class="h-full bg-gray-900">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ride4Study</title>
        <base href="<?= url('/') ?>">

        <link rel="icon" href="public/favicon.ico" sizes="any">
        <link rel="icon" type="image/png" sizes="32x32" href="public/favicon-32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="public/favicon-16.png">
        <link rel="apple-touch-icon" sizes="180x180" href="public/apple-touch-icon.png">
        <link rel="manifest" href="public/site.webmanifest">
        <meta name="theme-color" content="#111827">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

        <script src="https://cdn.tailwindcss.com"></script>
        <script src="public/js/tailwind-config.js"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <link rel="stylesheet" href="public/css/accessibility.css">

        <style>
            body { font-family: 'Inter', sans-serif; }

            /* Enlace de salto al contenido (accesibilidad) */
            .skip-link {
                position: absolute;
                top: -40px;
                left: 8px;
                background: #fde047;
                color: #111827;
                padding: 8px 16px;
                z-index: 100;
                font-weight: 600;
                border-radius: 8px;
                transition: top 0.2s ease;
            }
            .skip-link:focus {
                top: 8px;
                outline: 3px solid #111827;
            }

            /* Focus visible global (accesibilidad - WCAG 2.4.7) */
            *:focus-visible {
                outline: 2px solid #fde047;
                outline-offset: 2px;
                border-radius: 4px;
            }

            #mobile-menu {
                transition: opacity 0.2s ease, transform 0.2s ease;
            }

            #mobile-menu.hidden {
                opacity: 0;
                transform: translateY(-8px);
                pointer-events: none;
            }

            #mobile-menu.open {
                opacity: 1;
                transform: translateY(0);
                pointer-events: auto;
            }

            /* Respetar preferencia de reduccion de movimiento (WCAG 2.3.3) */
            @media (prefers-reduced-motion: reduce) {
                *, *::before, *::after {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                    scroll-behavior: auto !important;
                }
            }
        </style>
    </head>
    <body class="min-h-screen text-gray-100 flex flex-col pt-28 bg-cover bg-center">
        <?php require_once __DIR__ . '/modals.php'; ?>
        <a href="#main-content" class="skip-link"><?= t('a11y.skip_to_content') ?? 'Saltar al contenido principal' ?></a>
        <div class="fixed inset-0 bg-gray-900/90 z-[-1]" aria-hidden="true"></div>
            <nav class="fixed top-6 inset-x-0 mx-auto z-50 w-full max-w-6xl px-4 sm:px-6" aria-label="<?= t('a11y.main_nav') ?? 'Navegacion principal' ?>">

                <!-- Principal-->
                <div class="bg-gray-900/80 backdrop-blur-xl border border-white/10 rounded-full shadow-2xl px-5 sm:px-8 py-3.5 mx-auto">
                    <div class="flex items-center justify-between gap-3">

                        <!-- Logo -->
                        <a href="<?= url('/') ?>" class="flex-shrink-0 group" aria-label="<?= t('a11y.home') ?? 'Ride4Study - ir al inicio' ?>">
                            <div class="flex items-center gap-2.5 sm:gap-3">
                                <img src="public/img/logo.png" alt="" aria-hidden="true" class="w-10 h-10 sm:w-11 sm:h-11 object-contain transition-transform group-hover:scale-110">
                                <span class="text-xl sm:text-2xl font-bold text-white transition-colors group-hover:text-primary">Ride4Study</span>
                            </div>
                        </a>

                        <!-- Escritorio: navegacion y acciones -->
                        <?php if ($isLoggedIn): ?>

                            <!-- Enlaces de navegacion (solo escritorio) -->
                            <div class="hidden md:flex items-center gap-1 bg-white/5 rounded-full p-1.5 border border-white/5">
                                <a href="<?= url('/dashboard') ?>" class="px-5 py-2 rounded-full text-sm font-medium transition-all <?= isActive('/dashboard') ? 'bg-primary text-secondary shadow-lg shadow-primary/25' : 'text-gray-300 hover:text-white hover:bg-white/5' ?>"<?= isActive('/dashboard') ? ' aria-current="page"' : '' ?>>
                                    <i class="fas fa-search mr-1.5" aria-hidden="true"></i><?= t('nav.search') ?>
                                </a>

                                <a href="<?= url('/my-rides') ?>" class="px-5 py-2 rounded-full text-sm font-medium transition-all <?= isActive('/my-rides') ? 'bg-primary text-secondary shadow-lg shadow-primary/25' : 'text-gray-300 hover:text-white hover:bg-white/5' ?>"<?= isActive('/my-rides') ? ' aria-current="page"' : '' ?>>
                                    <i class="fas fa-car mr-1.5" aria-hidden="true"></i><?= t('nav.my_rides') ?>
                                </a>

                                <a href="<?= url('/messages') ?>" class="px-5 py-2 rounded-full text-sm font-medium transition-all relative <?= isActive('/messages') ? 'bg-primary text-secondary shadow-lg shadow-primary/25' : 'text-gray-300 hover:text-white hover:bg-white/5' ?>"<?= isActive('/messages') ? ' aria-current="page"' : '' ?>>
                                    <i class="fas fa-comment-alt mr-1.5" aria-hidden="true"></i><?= t('nav.messages') ?>
                                </a>
                            </div>

                            <!-- Acciones derechas -->
                            <div class="flex items-center gap-2 sm:gap-3">

                                <!-- Selector de idioma (oculto en móvil, está en el menú hamburguesa) -->
                                <div class="hidden sm:flex items-center gap-0.5 bg-white/5 rounded-full p-0.5 border border-white/5" role="group" aria-label="<?= t('a11y.lang_selector') ?? 'Seleccionar idioma' ?>">
                                    <a href="<?= url('/set-lang') ?>?lang=es" hreflang="es" lang="es" class="px-2.5 py-1.5 rounded-full text-xs font-semibold transition-all <?= currentLang() === 'es' ? 'bg-primary text-secondary' : 'text-gray-400 hover:text-white' ?>"<?= currentLang() === 'es' ? ' aria-current="true"' : '' ?>>ES</a>
                                    <a href="<?= url('/set-lang') ?>?lang=en" hreflang="en" lang="en" class="px-2.5 py-1.5 rounded-full text-xs font-semibold transition-all <?= currentLang() === 'en' ? 'bg-primary text-secondary' : 'text-gray-400 hover:text-white' ?>"<?= currentLang() === 'en' ? ' aria-current="true"' : '' ?>>EN</a>
                                </div>

                                <div class="h-7 w-px bg-white/10 hidden md:block"></div>

                                <!-- Campana de notificaciones -->
                                <div class="relative" id="notif-wrapper">
                                    <button type="button" onclick="toggleNotifPanel()" class="relative p-2.5 rounded-full hover:bg-white/10 transition-all text-gray-300 hover:text-white" aria-label="<?= t('nav.notifications') ?><?= $unreadNotifCount > 0 ? ' (' . $unreadNotifCount . ' ' . (t('a11y.unread') ?? 'sin leer') . ')' : '' ?>" aria-expanded="false" aria-haspopup="true" aria-controls="notif-panel" id="notif-btn">
                                        <i class="fas fa-bell text-lg" aria-hidden="true"></i>

                                        <?php if ($unreadNotifCount > 0): ?>
                                            <span class="absolute top-0.5 right-0.5 min-w-[16px] h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-0.5" id="notif-badge" aria-hidden="true"><?= $unreadNotifCount ?></span>
                                        <?php else: ?>
                                            <span class="absolute top-0.5 right-0.5 min-w-[16px] h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-0.5 hidden" id="notif-badge" aria-hidden="true">0</span>
                                        <?php endif; ?>
                                    </button>

                                    <!-- Panel de notificaciones -->
                                    <div id="notif-panel" class="hidden fixed sm:absolute left-4 right-4 sm:left-auto sm:right-0 top-[4.5rem] sm:top-full sm:mt-3 sm:w-80 sm:max-w-none origin-top-right rounded-2xl bg-[#1a1b26]/95 backdrop-blur-xl border border-white/10 shadow-2xl z-50" role="region" aria-label="<?= t('nav.notifications') ?>">
                                        <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
                                            <span class="text-sm font-semibold text-white"><?= t('nav.notifications') ?></span>
                                            <button onclick="markAllRead()" class="text-xs text-primary hover:underline"><?= t('nav.mark_all_read') ?></button>
                                        </div>

                                        <div id="notif-list" class="max-h-72 overflow-y-auto divide-y divide-white/5">
                                            <p class="text-sm text-gray-400 p-4 text-center"><?= t('nav.loading') ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="h-7 w-px bg-white/10 hidden md:block"></div>

                                <!-- Avatar + desplegable (escritorio) -->
                                <div class="relative hidden md:block">
                                    <button type="button" onclick="toggleUserMenu()" class="flex items-center gap-2 rounded-full pr-1.5 pl-1.5 py-1 hover:bg-white/10 transition-all border border-transparent hover:border-white/10" id="user-menu-button" aria-expanded="false" aria-haspopup="true" aria-controls="user-menu" aria-label="<?= t('a11y.user_menu') ?? 'Menu de usuario' ?>">
                                        <div class="h-10 w-10 rounded-full overflow-hidden ring-2 ring-gray-900 bg-gray-800 flex items-center justify-center">
                                            <?php if (!empty($_SESSION['user_photo']) && file_exists(__DIR__ . '/../../public/uploads/profiles/' . $_SESSION['user_photo'])):
                                                $photoPath = 'public/uploads/profiles/' . $_SESSION['user_photo'];
                                                $fsPath = __DIR__ . '/../../public/uploads/profiles/' . $_SESSION['user_photo'];
                                                $ver = filemtime($fsPath);
                                            ?>

                                            <img src="<?= $photoPath ?>?v=<?= $ver ?>" alt="" aria-hidden="true" class="w-full h-full object-cover">

                                            <?php else: ?>
                                                <div class="w-full h-full bg-gradient-to-tr from-gray-700 to-gray-600 flex items-center justify-center text-white font-bold text-sm" aria-hidden="true">
                                                    <?= $userInitial ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <i class="fas fa-chevron-down text-gray-400 text-xs mr-2 transition-transform duration-200" id="menu-arrow" aria-hidden="true"></i>
                                    </button>

                                    <!-- Desplegable escritorio -->
                                    <div id="user-menu" class="hidden absolute right-0 top-full mt-3 w-60 origin-top-right rounded-2xl bg-[#1a1b26]/95 backdrop-blur-xl border border-white/10 py-2 shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none z-50" role="menu" aria-labelledby="user-menu-button">
                                        <div class="px-5 py-4 border-b border-white/5 bg-white/5 mx-2 rounded-xl mb-2">
                                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold"><?= t('nav.connected_as') ?></p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <p class="text-base font-bold text-white truncate"><?= htmlspecialchars($userName) ?></p>
                                                <?php if ($userIsPremium): ?>
                                                    <span class="px-1.5 py-0.5 bg-yellow-500/20 text-yellow-400 text-[10px] font-bold rounded-full border border-yellow-500/30 flex-shrink-0">
                                                        <i class="fas fa-crown mr-0.5" aria-hidden="true"></i> PRO
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="px-2 space-y-1">
                                            <a href="<?= url('/profile') ?>" role="menuitem" class="group flex items-center px-3 py-2.5 text-sm font-medium text-gray-300 rounded-lg hover:bg-primary hover:text-secondary transition-all">
                                                <i class="fas fa-user-circle w-6 text-center text-gray-400 group-hover:text-secondary/70" aria-hidden="true"></i><?= t('nav.my_profile') ?>
                                            </a>

                                            <a href="<?= url('/my-rides') ?>" role="menuitem" class="group flex items-center px-3 py-2.5 text-sm font-medium text-gray-300 rounded-lg hover:bg-primary hover:text-secondary transition-all">
                                                <i class="fas fa-car w-6 text-center text-gray-400 group-hover:text-secondary/70" aria-hidden="true"></i><?= t('nav.my_rides') ?>
                                            </a>

                                            <a href="<?= url('/my-rides') ?>?tab=bookings" role="menuitem" class="group flex items-center px-3 py-2.5 text-sm font-medium text-gray-300 rounded-lg hover:bg-primary hover:text-secondary transition-all">
                                                <i class="fas fa-ticket-alt w-6 text-center text-gray-400 group-hover:text-secondary/70" aria-hidden="true"></i><?= t('nav.my_bookings') ?>
                                            </a>

                                            <?php if ($userIsPremium): ?>
                                                <a href="<?= url('/premium') ?>" role="menuitem" class="group flex items-center px-3 py-2.5 text-sm font-medium text-yellow-400 rounded-lg hover:bg-yellow-500/10 transition-all">
                                                    <i class="fas fa-crown w-6 text-center opacity-70" aria-hidden="true"></i><?= t('nav.my_subscription') ?>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= url('/premium') ?>" role="menuitem" class="group flex items-center px-3 py-2.5 text-sm font-medium text-gray-300 rounded-lg hover:bg-primary hover:text-secondary transition-all">
                                                    <i class="fas fa-star w-6 text-center text-gray-400 group-hover:text-secondary/70" aria-hidden="true"></i><?= t('nav.go_premium') ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mt-2 pt-2 border-t border-white/5 px-2">
                                            <form method="POST" action="<?= url('/logout') ?>" class="block">
                                                <?= csrfField() ?>
                                                <button type="submit" role="menuitem" class="w-full group flex items-center px-3 py-2.5 text-sm font-medium text-red-400 rounded-lg hover:bg-red-500/10 hover:text-red-300 transition-all text-left">
                                                    <i class="fas fa-sign-out-alt w-6 text-center opacity-70" aria-hidden="true"></i><?= t('nav.logout') ?>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hamburguesa (solo movil) -->
                                <button
                                    type="button"
                                    id="mobile-menu-btn"
                                    onclick="toggleMobileMenu()"
                                    class="md:hidden flex items-center justify-center w-10 h-10 rounded-full bg-white/10 hover:bg-white/15 active:bg-white/20 transition-all text-white border border-white/15"
                                    aria-label="<?= t('a11y.open_menu') ?? 'Abrir menu' ?>"
                                    aria-expanded="false"
                                    aria-controls="mobile-menu"
                                >
                                    <i class="fas fa-bars text-lg" id="mobile-menu-icon" aria-hidden="true"></i>
                                </button>

                            </div>

                        <?php else: ?>
                            <!-- Usuario NO logueado: enlaces + CTA + idioma -->
                            <div class="flex items-center gap-2">

                                <!-- Enlaces navegación pública (solo escritorio) -->
                                <div class="hidden md:flex items-center gap-1">
                                    <a href="<?= url('/instituciones') ?>" class="px-4 py-2 rounded-full text-sm font-medium text-gray-300 hover:text-white hover:bg-white/10 transition-all <?= isActive('/instituciones') ? 'bg-white/10 text-white' : '' ?>">
                                        <i class="fas fa-university mr-1.5 opacity-70" aria-hidden="true"></i><?= t('landing.institutions') ?>
                                    </a>
                                    <a href="<?= SURVEY_URL ?>" target="_blank" rel="noopener" class="px-4 py-2 rounded-full text-sm font-medium text-gray-300 hover:text-white hover:bg-white/10 transition-all">
                                        <i class="fas fa-comment-dots mr-1.5 opacity-70" aria-hidden="true"></i><?= t('landing.survey') ?>
                                    </a>
                                </div>

                                <div class="hidden md:block h-7 w-px bg-white/10"></div>

                                <!-- Idioma (oculto en móvil, está en el menú hamburguesa) -->
                                <div class="hidden sm:flex items-center gap-0.5 bg-white/5 rounded-full p-0.5 border border-white/5" role="group" aria-label="<?= t('a11y.lang_selector') ?? 'Seleccionar idioma' ?>">
                                    <a href="<?= url('/set-lang') ?>?lang=es" hreflang="es" lang="es" class="px-2 py-1 rounded-full text-xs font-semibold transition-all <?= currentLang() === 'es' ? 'bg-primary text-secondary' : 'text-gray-400 hover:text-white' ?>"<?= currentLang() === 'es' ? ' aria-current="true"' : '' ?>>ES</a>
                                    <a href="<?= url('/set-lang') ?>?lang=en" hreflang="en" lang="en" class="px-2 py-1 rounded-full text-xs font-semibold transition-all <?= currentLang() === 'en' ? 'bg-primary text-secondary' : 'text-gray-400 hover:text-white' ?>"<?= currentLang() === 'en' ? ' aria-current="true"' : '' ?>>EN</a>
                                </div>

                                <!-- Login/Register: en escritorio ambos visibles; en móvil solo Registrarse -->
                                <a href="<?= url('/login') ?>" class="hidden sm:inline-flex px-3 sm:px-4 py-2 rounded-full text-sm font-medium text-gray-300 hover:text-white hover:bg-white/10 transition-all">
                                    <?= t('nav.login') ?>
                                </a>

                                <a href="<?= url('/register') ?>" class="px-3 sm:px-4 py-2 rounded-full text-sm font-semibold bg-primary text-secondary hover:opacity-90 transition-all shadow-lg shadow-primary/25 whitespace-nowrap">
                                    <?= t('nav.register') ?>
                                </a>

                                <!-- Hamburguesa móvil para guest -->
                                <button
                                    type="button"
                                    id="guest-menu-btn"
                                    onclick="toggleGuestMenu()"
                                    class="md:hidden flex items-center justify-center w-10 h-10 rounded-full bg-white/10 hover:bg-white/15 active:bg-white/20 transition-all text-white border border-white/15 shrink-0"
                                    aria-label="<?= t('a11y.open_menu') ?? 'Abrir menu' ?>"
                                    aria-expanded="false"
                                    aria-controls="guest-menu"
                                >
                                    <i class="fas fa-bars text-lg" id="guest-menu-icon" aria-hidden="true"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Menú móvil (solo para usuario logueado) -->
                <?php if ($isLoggedIn): ?>
                    <div id="mobile-menu" class="hidden md:hidden mt-2 mx-0 rounded-2xl bg-gray-900/95 backdrop-blur-xl border border-white/10 shadow-2xl overflow-hidden" aria-label="<?= t('a11y.mobile_menu') ?? 'Menu movil' ?>">

                        <!-- Cabecera del menú móvil -->
                        <?php
                            $currentLangCode = currentLang();
                            $langFlags = ['es' => '🇪🇸', 'en' => '🇬🇧'];
                            $otherLang = $currentLangCode === 'es' ? 'en' : 'es';
                        ?>
                        <div class="px-4 py-4 border-b border-white/10 bg-white/5 flex items-start gap-3">
                            <!-- Columna: avatar + insignia PRO -->
                            <div class="flex flex-col items-center gap-1.5 flex-shrink-0">
                                <div class="h-12 w-12 rounded-full overflow-hidden ring-2 ring-gray-700 bg-gray-800 flex items-center justify-center">
                                    <?php if (!empty($_SESSION['user_photo']) && file_exists(__DIR__ . '/../../public/uploads/profiles/' . $_SESSION['user_photo'])): ?>
                                        <img src="<?= 'public/uploads/profiles/' . $_SESSION['user_photo'] ?>?v=<?= filemtime(__DIR__ . '/../../public/uploads/profiles/' . $_SESSION['user_photo']) ?>" alt="" aria-hidden="true" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gradient-to-tr from-gray-700 to-gray-600 flex items-center justify-center text-white font-bold text-sm" aria-hidden="true">
                                            <?= $userInitial ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($userIsPremium): ?>
                                    <span class="px-1.5 py-0.5 bg-yellow-500/20 text-yellow-400 text-[10px] font-bold rounded-full border border-yellow-500/30 flex items-center gap-0.5 leading-none">
                                        <i class="fas fa-crown text-[9px]" aria-hidden="true"></i> PRO
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Info del usuario -->
                            <div class="flex-1 min-w-0 pt-0.5">
                                <p class="text-[11px] text-gray-500 leading-none mb-1"><?= t('nav.connected_as') ?></p>
                                <p class="text-[15px] font-bold text-white leading-tight break-words"><?= htmlspecialchars($userName) ?></p>
                            </div>

                            <!-- Selector de idioma desplegable (bandera + dropdown) -->
                            <div class="relative flex-shrink-0" id="lang-dropdown-wrap">
                                <button type="button" onclick="toggleLangDropdown(event)" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/5 border border-white/10 hover:bg-white/10 transition-all" aria-label="<?= t('a11y.lang_selector') ?? 'Seleccionar idioma' ?>" aria-expanded="false" aria-haspopup="true">
                                    <span class="text-base leading-none"><?= $langFlags[$currentLangCode] ?></span>
                                    <span class="text-[11px] font-bold text-white uppercase"><?= $currentLangCode ?></span>
                                    <i class="fas fa-chevron-down text-[9px] text-gray-400" aria-hidden="true"></i>
                                </button>
                                <div id="lang-dropdown" class="hidden absolute right-0 top-full mt-1.5 bg-gray-800 border border-white/10 rounded-xl shadow-2xl overflow-hidden z-[60] min-w-[96px]">
                                    <a href="<?= url('/set-lang') ?>?lang=<?= $otherLang ?>" hreflang="<?= $otherLang ?>" lang="<?= $otherLang ?>" class="flex items-center gap-2 px-3 py-2 text-[11px] font-bold text-white uppercase hover:bg-white/5 transition">
                                        <span class="text-base leading-none"><?= $langFlags[$otherLang] ?></span>
                                        <span><?= $otherLang ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Enlaces de navegación principal -->
                        <nav class="px-3 py-3 space-y-1 border-b border-white/10" aria-label="<?= t('nav.navigation') ?? 'Navegacion' ?>">
                            <p class="text-[10px] uppercase tracking-widest text-gray-500 font-semibold px-3 pb-1" id="mobile-nav-label"><?= t('nav.navigation') ?? 'Navegación' ?></p>
                            <a href="<?= url('/dashboard') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= isActive('/dashboard') ? 'bg-primary/20 text-primary' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?>"<?= isActive('/dashboard') ? ' aria-current="page"' : '' ?>>
                                <i class="fas fa-search w-5 text-center text-sm opacity-70" aria-hidden="true"></i><?= t('nav.search') ?>
                            </a>

                            <a href="<?= url('/my-rides') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= isActive('/my-rides') ? 'bg-primary/20 text-primary' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?>"<?= isActive('/my-rides') ? ' aria-current="page"' : '' ?>>
                                <i class="fas fa-car w-5 text-center text-sm opacity-70" aria-hidden="true"></i><?= t('nav.my_rides') ?>
                            </a>

                            <a href="<?= url('/messages') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all <?= isActive('/messages') ? 'bg-primary/20 text-primary' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?>"<?= isActive('/messages') ? ' aria-current="page"' : '' ?>>
                                <i class="fas fa-comment-alt w-5 text-center text-sm opacity-70" aria-hidden="true"></i><?= t('nav.messages') ?>
                            </a>
                        </nav>

                        <!-- Enlaces de cuenta -->
                        <nav class="px-3 py-3 space-y-1 border-b border-white/10" aria-label="<?= t('nav.account') ?? 'Cuenta' ?>">
                            <p class="text-[10px] uppercase tracking-widest text-gray-500 font-semibold px-3 pb-1"><?= t('nav.account') ?? 'Cuenta' ?></p>
                            <a href="<?= url('/profile') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                                <i class="fas fa-user-circle w-5 text-center text-sm opacity-70" aria-hidden="true"></i><?= t('nav.my_profile') ?>
                            </a>

                            <a href="<?= url('/my-rides') ?>?tab=bookings" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                                <i class="fas fa-ticket-alt w-5 text-center text-sm opacity-70" aria-hidden="true"></i><?= t('nav.my_bookings') ?>
                            </a>

                            <?php if ($userIsPremium): ?>
                                <a href="<?= url('/premium') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-yellow-400 hover:bg-yellow-500/10 transition-all">
                                    <i class="fas fa-crown w-5 text-center text-sm opacity-70" aria-hidden="true"></i><?= t('nav.my_subscription') ?>
                                </a>

                            <?php else: ?>
                                <a href="<?= url('/premium') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                                    <i class="fas fa-star w-5 text-center text-sm opacity-70" aria-hidden="true"></i><?= t('nav.go_premium') ?>
                                </a>
                            <?php endif; ?>
                        </nav>

                        <!-- Logout -->
                        <div class="px-3 py-3 border-t border-white/10">
                            <form method="POST" action="<?= url('/logout') ?>" class="block">
                                <?= csrfField() ?>
                                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-all text-left">
                                    <i class="fas fa-sign-out-alt w-5 text-center text-sm opacity-70" aria-hidden="true"></i><?= t('nav.logout') ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Menú móvil guest (no logueado) -->
                <?php if (!$isLoggedIn): ?>
                    <div id="guest-menu" class="hidden md:hidden mt-2 mx-0 rounded-2xl bg-gray-900/95 backdrop-blur-xl border border-white/10 shadow-2xl overflow-hidden" aria-label="<?= t('a11y.mobile_menu') ?? 'Menu movil' ?>">
                        <nav class="p-2 space-y-1">
                            <a href="<?= url('/instituciones') ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium text-gray-200 hover:bg-white/10 transition-all">
                                <i class="fas fa-university w-5 text-center text-primary/80" aria-hidden="true"></i><?= t('landing.institutions') ?>
                            </a>
                            <a href="<?= SURVEY_URL ?>" target="_blank" rel="noopener" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium text-gray-200 hover:bg-white/10 transition-all">
                                <i class="fas fa-comment-dots w-5 text-center text-primary/80" aria-hidden="true"></i><?= t('landing.survey') ?>
                                <i class="fas fa-external-link-alt text-[10px] text-gray-500 ml-auto" aria-hidden="true"></i>
                            </a>
                            <a href="<?= url('/support') ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium text-gray-200 hover:bg-white/10 transition-all">
                                <i class="fas fa-life-ring w-5 text-center text-primary/80" aria-hidden="true"></i><?= t('footer.support') ?? 'Soporte' ?>
                            </a>
                        </nav>
                        <div class="border-t border-white/10 p-2 space-y-1">
                            <a href="<?= url('/login') ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium text-gray-200 hover:bg-white/10 transition-all">
                                <i class="fas fa-sign-in-alt w-5 text-center text-gray-400" aria-hidden="true"></i><?= t('nav.login') ?>
                            </a>
                            <a href="<?= url('/institution-login') ?>" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium text-gray-200 hover:bg-white/10 transition-all">
                                <i class="fas fa-building-columns w-5 text-center text-gray-400" aria-hidden="true"></i><?= t('login.institution_access') ?>
                            </a>
                        </div>
                        <!-- Idioma -->
                        <div class="border-t border-white/10 p-3">
                            <p class="px-1 pb-2 text-[11px] font-semibold text-gray-500 uppercase tracking-wider"><?= t('a11y.lang_selector') ?? 'Idioma' ?></p>
                            <div class="flex gap-2">
                                <a href="<?= url('/set-lang') ?>?lang=es" hreflang="es" lang="es" class="flex-1 px-3 py-2 rounded-xl text-sm font-semibold text-center transition-all <?= currentLang() === 'es' ? 'bg-primary text-secondary' : 'bg-white/5 text-gray-300 hover:bg-white/10' ?>"<?= currentLang() === 'es' ? ' aria-current="true"' : '' ?>>Español</a>
                                <a href="<?= url('/set-lang') ?>?lang=en" hreflang="en" lang="en" class="flex-1 px-3 py-2 rounded-xl text-sm font-semibold text-center transition-all <?= currentLang() === 'en' ? 'bg-primary text-secondary' : 'bg-white/5 text-gray-300 hover:bg-white/10' ?>"<?= currentLang() === 'en' ? ' aria-current="true"' : '' ?>>English</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </nav>

        <!-- Scripts -->
        <?php if ($isLoggedIn): ?>
            <script>
                /* Desplegable de idioma (dentro del menú móvil) */
                function toggleLangDropdown(e) {
                    if (e) e.stopPropagation();
                    const dd = document.getElementById('lang-dropdown');
                    const btn = document.querySelector('#lang-dropdown-wrap button');
                    if (!dd) return;
                    const open = dd.classList.toggle('hidden');
                    if (btn) btn.setAttribute('aria-expanded', String(!open));
                }
                document.addEventListener('click', function(e) {
                    const wrap = document.getElementById('lang-dropdown-wrap');
                    const dd = document.getElementById('lang-dropdown');
                    if (wrap && dd && !dd.classList.contains('hidden') && !wrap.contains(e.target)) {
                        dd.classList.add('hidden');
                        const btn = wrap.querySelector('button');
                        if (btn) btn.setAttribute('aria-expanded', 'false');
                    }
                });

                /* Escritorio: desplegable de usuario */
                function toggleUserMenu() {
                    const menu  = document.getElementById('user-menu');
                    const btn   = document.getElementById('user-menu-button');
                    const arrow = document.getElementById('menu-arrow');
                    const isHidden = menu.classList.contains('hidden');

                    menu.classList.toggle('hidden', !isHidden);
                    if (btn) btn.setAttribute('aria-expanded', String(isHidden));
                    arrow.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
                }

                /* Móvil: hamburguesa */
                function toggleMobileMenu() {
                    const mobileMenu = document.getElementById('mobile-menu');
                    const btn        = document.getElementById('mobile-menu-btn');
                    const icon = document.getElementById('mobile-menu-icon');
                    const isHidden = mobileMenu.classList.contains('hidden');

                    if (isHidden) {
                        mobileMenu.classList.remove('hidden');
                        mobileMenu.getBoundingClientRect();
                        mobileMenu.classList.add('open');
                        icon.classList.replace('fa-bars', 'fa-times');
                        if (btn) btn.setAttribute('aria-expanded', 'true');

                        } else {
                            mobileMenu.classList.remove('open');
                            mobileMenu.classList.add('hidden');
                            icon.classList.replace('fa-times', 'fa-bars');
                            if (btn) btn.setAttribute('aria-expanded', 'false');
                    }
                }

                /* Cerrar menús al hacer clic fuera */
                document.addEventListener('click', function(event) {
                    // Menú de escritorio para usuario
                    const btn    = document.getElementById('user-menu-button');
                    const menu   = document.getElementById('user-menu');
                    const arrow  = document.getElementById('menu-arrow');

                    if (btn && menu && !btn.contains(event.target) && !menu.contains(event.target)) {
                        menu.classList.add('hidden');
                        btn.setAttribute('aria-expanded', 'false');
                        if (arrow) arrow.style.transform = 'rotate(0deg)';
                    }

                    // Notificaciones
                    const notifWrapper = document.getElementById('notif-wrapper');
                    const notifPanel   = document.getElementById('notif-panel');
                    const notifBtn     = document.getElementById('notif-btn');

                    if (notifWrapper && !notifWrapper.contains(event.target)) {
                        notifPanel.classList.add('hidden');
                        if (notifBtn) notifBtn.setAttribute('aria-expanded', 'false');
                    }

                    // Menú para móvil
                    const mobileBtn  = document.getElementById('mobile-menu-btn');
                    const mobileMenu = document.getElementById('mobile-menu');
                    const mIcon      = document.getElementById('mobile-menu-icon');

                    if (mobileBtn && mobileMenu && !mobileBtn.contains(event.target) && !mobileMenu.contains(event.target)) {
                        mobileMenu.classList.remove('open');
                        mobileMenu.classList.add('hidden');
                        mobileBtn.setAttribute('aria-expanded', 'false');
                        if (mIcon) { mIcon.classList.remove('fa-times'); mIcon.classList.add('fa-bars'); }
                    }
                });

                /* Cerrar menus con tecla Escape (accesibilidad) */
                document.addEventListener('keydown', function(event) {
                    if (event.key !== 'Escape') return;
                    const userMenu   = document.getElementById('user-menu');
                    const userBtn    = document.getElementById('user-menu-button');
                    const notifPanel = document.getElementById('notif-panel');
                    const notifBtn   = document.getElementById('notif-btn');
                    const mobileMenu = document.getElementById('mobile-menu');
                    const mobileBtn  = document.getElementById('mobile-menu-btn');
                    const mIcon      = document.getElementById('mobile-menu-icon');
                    const reportM    = document.getElementById('reportModal');

                    if (userMenu && !userMenu.classList.contains('hidden')) {
                        userMenu.classList.add('hidden');
                        if (userBtn) { userBtn.setAttribute('aria-expanded', 'false'); userBtn.focus(); }
                    }
                    if (notifPanel && !notifPanel.classList.contains('hidden')) {
                        notifPanel.classList.add('hidden');
                        if (notifBtn) { notifBtn.setAttribute('aria-expanded', 'false'); notifBtn.focus(); }
                    }
                    if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.remove('open');
                        mobileMenu.classList.add('hidden');
                        if (mIcon) { mIcon.classList.remove('fa-times'); mIcon.classList.add('fa-bars'); }
                        if (mobileBtn) { mobileBtn.setAttribute('aria-expanded', 'false'); mobileBtn.focus(); }
                    }
                    if (reportM && !reportM.classList.contains('hidden')) {
                        closeReportModal();
                    }
                });

                /* Notificaciones */
                function formatNotifDate(raw) {
                    if (!raw) return '';
                    const d = new Date(raw.replace(' ', 'T'));
                    if (isNaN(d)) return raw;

                    const now = new Date();
                    const diffMin = Math.floor((now - d) / 60000);
                    const diffH   = Math.floor(diffMin / 60);
                    const diffD   = Math.floor(diffH   / 24);

                    if (diffMin < 1)  return '<?= t('nav.notif_just_now') ?>';
                    if (diffMin < 60) return diffMin + ' <?= t('nav.notif_min_ago') ?>';
                    if (diffH   < 24) return diffH   + ' <?= t('nav.notif_h_ago') ?>';
                    if (diffD   <  7) return diffD   + ' <?= t('nav.notif_d_ago') ?>';

                    return d.toLocaleDateString('<?= currentLang() === 'es' ? 'es-ES' : 'en-GB' ?>', { day: 'numeric', month: 'short' });
                }

                function toggleNotifPanel() {
                    const panel = document.getElementById('notif-panel');
                    const btn   = document.getElementById('notif-btn');
                    const wasHidden = panel.classList.contains('hidden');

                    panel.classList.toggle('hidden');
                    if (btn) btn.setAttribute('aria-expanded', String(wasHidden));
                    if (wasHidden) loadNotifications();
                }

                function loadNotifications() {
                    fetch('<?= url("/notifications") ?>?action=list')
                    .then(r => r.json())
                    .then(data => {
                        const list = document.getElementById('notif-list');

                        if (!data.success || !data.notifications.length) {
                            list.innerHTML = '<p class="text-sm text-gray-400 p-4 text-center"><?= t('nav.no_notifications') ?></p>';
                            return;
                        }

                        list.innerHTML = data.notifications.map(n => `
                            <div class="flex items-start gap-3 px-4 py-3 hover:bg-white/5 cursor-pointer transition-colors"
                                onclick="markRead(${n.idNotificacion}, ${n.url ? JSON.stringify(n.url) : 'null'})">
                                <i class="${n.icono || 'fas fa-bell'} text-primary mt-0.5 flex-shrink-0 text-sm"></i>
                                <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-200 leading-snug">${n.mensaje}</p>
                                <p class="text-xs text-gray-500 mt-0.5">${formatNotifDate(n.fecha_creacion)}</p>
                                </div>
                            </div>
                            `).join('');
                    })
                    .catch(() => {
                        document.getElementById('notif-list').innerHTML =
                        '<p class="text-sm text-red-400 p-4 text-center"><?= t('nav.error_loading') ?></p>';
                    });
                }

                function markRead(id, url) {
                    const body = new FormData();
                    body.append('action', 'mark_read');
                    body.append('id', id);

                    fetch('<?= url("/notifications") ?>', { method: 'POST', body })
                    .finally(() => {
                        updateBadge(-1);
                        if (url) window.location.href = url;
                        else loadNotifications();
                    });
                }

                function markAllRead() {
                    const body = new FormData();
                    body.append('action', 'mark_all_read');

                    fetch('<?= url("/notifications") ?>', { method: 'POST', body })
                    .then(() => { updateBadge(0, true); loadNotifications(); });
                }

                function updateBadge(delta, reset = false) {
                    const badge = document.getElementById('notif-badge');
                    if (!badge) return;
                    let count = reset ? 0 : Math.max(0, parseInt(badge.textContent || '0') + delta);
                    badge.textContent = count;
                    badge.classList.toggle('hidden', count === 0);
                }

                function pollNotifBadge() {
                    fetch('<?= url("/notifications") ?>?action=count')
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && typeof data.count === 'number') {
                            const badge = document.getElementById('notif-badge');
                            if (!badge) return;
                            badge.textContent = data.count;
                            badge.classList.toggle('hidden', data.count === 0);
                            const panel = document.getElementById('notif-panel');
                            if (panel && !panel.classList.contains('hidden')) loadNotifications();
                        }
                    })
                    .catch(() => {});
                }
                setInterval(pollNotifBadge, 30000);

                /* Reporte global */
                let reportData = {};

                function openReportModal(tipo, opts = {}) {
                    // Para 'chat_conv' usamos 'chat' internamente pero mostramos 'conversación' al usuario
                    const realTipo = tipo === 'chat_conv' ? 'chat' : tipo;
                    reportData = { tipo: realTipo, isConversation: tipo === 'chat_conv', ...opts };
                    document.getElementById('report-tipo').value      = realTipo;
                    document.getElementById('report-idUsuario').value = opts.idUsuario ?? '';
                    document.getElementById('report-idAnuncio').value = opts.idAnuncio ?? '';
                    document.getElementById('report-idChat').value    = opts.idChat    ?? '';
                    document.getElementById('report-motivo').value    = '';
                    document.getElementById('report-mensaje').value   = '';
                    clearEvidence();

                    const labels = { usuario: 'usuario', anuncio: 'anuncio', chat: 'mensaje de chat' };
                    const label = tipo === 'chat_conv' ? 'conversación' : (labels[tipo] || tipo);
                    document.getElementById('report-label').textContent = label;
                    document.getElementById('reportModal').classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }

                function previewEvidence(input) {
                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            document.getElementById('report-evidencia-img').src = e.target.result;
                            document.getElementById('report-evidencia-preview').classList.remove('hidden');
                            document.getElementById('report-evidencia-text').textContent = input.files[0].name;
                        };
                        reader.readAsDataURL(input.files[0]);
                    }
                }

                function clearEvidence() {
                    const input = document.getElementById('report-evidencia');
                    input.value = '';
                    document.getElementById('report-evidencia-preview').classList.add('hidden');
                    document.getElementById('report-evidencia-text').textContent = '<?= t('nav.report_evidence_upload') ?? 'Subir captura de pantalla' ?>';
                }

                function closeReportModal() {
                    document.getElementById('reportModal').classList.add('hidden');
                    document.body.style.overflow = '';
                }

                function submitReport() {
                    const motivo  = document.getElementById('report-motivo').value;
                    const mensaje = document.getElementById('report-mensaje').value.trim();

                    if (!motivo) { showToast('<?= t('nav.report_select_empty') ?>', false); return; }

                    const body = new FormData();
                    body.append('tipo',    document.getElementById('report-tipo').value);
                    body.append('motivo',  motivo);
                    body.append('mensaje', mensaje);

                    if (reportData.idUsuario) body.append('idUsuarioReportado', reportData.idUsuario);
                    if (reportData.idAnuncio) body.append('idAnuncio', reportData.idAnuncio);
                    if (!reportData.isConversation && reportData.idChat) body.append('idChat', reportData.idChat);

                    // Adjuntar evidencia si existe
                    const evidenciaInput = document.getElementById('report-evidencia');
                    if (evidenciaInput.files && evidenciaInput.files[0]) {
                        body.append('evidencia', evidenciaInput.files[0]);
                    }

                    fetch('<?= url("/report") ?>', { method: 'POST', body })
                    .then(r => r.json())
                    .then(data => { closeReportModal(); showToast(data.message, data.success); })
                    .catch(() => showToast('<?= t('nav.report_error') ?>', false));
                }

                /* Toast global */
                function showToast(msg, success = true) {
                    const toast    = document.getElementById('global-toast');
                    const toastMsg = document.getElementById('global-toast-msg');
                    const toastIcon = document.getElementById('global-toast-icon');

                    toastMsg.textContent = msg;
                    toast.className = toast.className.replace(/bg-\S+/, '');

                    if (success) {
                        toast.classList.add('bg-green-600');
                        toastIcon.className = 'fas fa-check-circle';
                    } else {
                        toast.classList.add('bg-red-600');
                        toastIcon.className = 'fas fa-times-circle';
                    }

                    toast.classList.remove('hidden', 'opacity-0');
                    toast.classList.add('opacity-100');
                    setTimeout(() => {
                    toast.classList.remove('opacity-100');
                    toast.classList.add('opacity-0');
                    setTimeout(() => toast.classList.add('hidden'), 300);
                    }, 3500);
                }
            </script>

            <!-- Modal global de reporte -->
            <div id="reportModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="report-modal-title" aria-describedby="report-modal-desc">
                <div class="bg-[#1a1b26] border border-gray-700 rounded-2xl shadow-2xl max-w-md w-full mx-4">
                    <div class="p-6 border-b border-gray-700 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-500/10 flex items-center justify-center flex-shrink-0" aria-hidden="true">
                            <i class="fas fa-flag text-red-400" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h3 id="report-modal-title" class="text-lg font-bold text-white"><?= t('nav.report') ?> <span id="report-label"><?= t('nav.report_content') ?></span></h3>
                            <p id="report-modal-desc" class="text-xs text-gray-400"><?= t('nav.report_reviewed') ?></p>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">
                        <input type="hidden" id="report-tipo">
                        <input type="hidden" id="report-idUsuario">
                        <input type="hidden" id="report-idAnuncio">
                        <input type="hidden" id="report-idChat">

                        <div>
                            <label for="report-motivo" class="block text-sm font-medium text-gray-300 mb-2"><?= t('nav.report_reason') ?> <span aria-label="<?= t('a11y.required') ?? 'obligatorio' ?>" class="text-red-400">*</span></label>
                            <select id="report-motivo" required aria-required="true" class="w-full bg-gray-800 border border-gray-600 text-gray-100 text-sm rounded-xl px-4 py-3 focus:outline-none focus:border-primary">
                                <option value=""><?= t('nav.report_select_reason') ?></option>
                                <option value="spam"><?= t('nav.report_reason_spam') ?></option>
                                <option value="ofensivo"><?= t('nav.report_reason_offensive') ?></option>
                                <option value="suplantacion"><?= t('nav.report_reason_impersonation') ?></option>
                                <option value="inapropiado"><?= t('nav.report_reason_inappropriate') ?></option>
                                <option value="fraude"><?= t('nav.report_reason_fraud') ?></option>
                                <option value="otro"><?= t('nav.report_reason_other') ?></option>
                            </select>
                        </div>

                        <div>
                            <label for="report-mensaje" class="block text-sm font-medium text-gray-300 mb-2"><?= t('nav.report_details') ?></label>
                            <textarea id="report-mensaje" rows="3" maxlength="500" placeholder="<?= t('nav.report_placeholder') ?>" class="w-full bg-gray-800 border border-gray-600 text-gray-100 text-sm rounded-xl px-4 py-3 focus:outline-none focus:border-primary resize-none placeholder-gray-500"></textarea>
                        </div>

                        <!-- Evidencia (captura de pantalla) -->
                        <div>
                            <label for="report-evidencia" class="block text-sm font-medium text-gray-300 mb-2"><?= t('nav.report_evidence') ?? 'Adjuntar evidencia (opcional)' ?></label>
                            <label id="report-evidencia-label" for="report-evidencia" class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-gray-800 border-2 border-dashed border-gray-600 rounded-xl cursor-pointer hover:border-primary/50 hover:bg-gray-800/80 transition group">
                                <i class="fas fa-camera text-gray-500 group-hover:text-primary transition" aria-hidden="true"></i>
                                <span id="report-evidencia-text" class="text-sm text-gray-500 group-hover:text-gray-300 transition"><?= t('nav.report_evidence_upload') ?? 'Subir captura de pantalla' ?></span>
                                <input type="file" id="report-evidencia" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="previewEvidence(this)">
                            </label>
                            <div id="report-evidencia-preview" class="hidden mt-2 relative">
                                <img id="report-evidencia-img" class="w-full max-h-32 object-cover rounded-lg border border-gray-600" alt="<?= t('nav.report_evidence_preview') ?? 'Vista previa de la evidencia adjuntada' ?>">
                                <button type="button" onclick="clearEvidence()" class="absolute top-1 right-1 w-6 h-6 bg-red-600 rounded-full flex items-center justify-center hover:bg-red-500 transition" aria-label="<?= t('a11y.remove_evidence') ?? 'Eliminar evidencia' ?>">
                                    <i class="fas fa-times text-white text-xs" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 pb-6 flex gap-3">
                        <button type="button" onclick="closeReportModal()" class="flex-1 px-4 py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition-colors"><?= t('nav.report_cancel') ?></button>
                        <button type="button" onclick="submitReport()"     class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-500 text-white rounded-xl text-sm font-bold transition-colors"><?= t('nav.report_send') ?></button>
                    </div>
                </div>
            </div>

            <!-- Toast global -->
            <div id="global-toast" role="status" aria-live="polite" aria-atomic="true" class="hidden fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-5 py-3 rounded-2xl shadow-2xl text-white text-sm font-medium transition-opacity duration-300 bg-green-600 max-w-[calc(100vw-3rem)]">
                <i id="global-toast-icon" class="fas fa-check-circle flex-shrink-0" aria-hidden="true"></i>
                <span id="global-toast-msg"></span>
            </div>
            <?php require_once __DIR__ . '/cookie-banner.php'; ?>
        <?php endif; ?>

        <?php if (!$isLoggedIn): ?>
            <script>
                /* Móvil guest: hamburguesa */
                function toggleGuestMenu() {
                    const menu = document.getElementById('guest-menu');
                    const btn  = document.getElementById('guest-menu-btn');
                    const icon = document.getElementById('guest-menu-icon');
                    if (!menu) return;
                    const willOpen = menu.classList.contains('hidden');
                    menu.classList.toggle('hidden', !willOpen);
                    if (btn) btn.setAttribute('aria-expanded', String(willOpen));
                    if (icon) {
                        icon.classList.toggle('fa-bars', !willOpen);
                        icon.classList.toggle('fa-times', willOpen);
                    }
                }
                /* Cerrar al hacer click fuera */
                document.addEventListener('click', function(event) {
                    const btn  = document.getElementById('guest-menu-btn');
                    const menu = document.getElementById('guest-menu');
                    const icon = document.getElementById('guest-menu-icon');
                    if (btn && menu && !menu.classList.contains('hidden') && !btn.contains(event.target) && !menu.contains(event.target)) {
                        menu.classList.add('hidden');
                        btn.setAttribute('aria-expanded', 'false');
                        if (icon) { icon.classList.remove('fa-times'); icon.classList.add('fa-bars'); }
                    }
                });
                /* Escape para cerrar */
                document.addEventListener('keydown', function(event) {
                    if (event.key !== 'Escape') return;
                    const menu = document.getElementById('guest-menu');
                    const btn  = document.getElementById('guest-menu-btn');
                    const icon = document.getElementById('guest-menu-icon');
                    if (menu && !menu.classList.contains('hidden')) {
                        menu.classList.add('hidden');
                        if (btn) { btn.setAttribute('aria-expanded', 'false'); btn.focus(); }
                        if (icon) { icon.classList.remove('fa-times'); icon.classList.add('fa-bars'); }
                    }
                });
            </script>
            <?php require_once __DIR__ . '/cookie-banner.php'; ?>
        <?php endif; ?>
        <main id="main-content" tabindex="-1" class="flex-grow flex flex-col h-full">
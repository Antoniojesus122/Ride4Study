<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verificar que sea una sesion de institucion
    if (!isset($_SESSION['institution_id'])) {
        header('Location: ' . url('/institution-login'));
        exit;
    }

    $pageTitle = $pageTitle ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title><?= htmlspecialchars($pageTitle) ?> - <?= htmlspecialchars($_SESSION['institution_name'] ?? 'Institucion') ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: '#6EE7B7',
                            'primary-dark': '#059669',
                            secondary: '#111827',
                            surface: '#1F2937',
                            'surface-light': '#374151',
                            accent: '#3E8E89',
                            'inst-accent': '#60a5fa',
                        },
                        fontFamily: {
                            sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        }
                    }
                }
            }
        </script>
        <style>
            html { font-size: 16px; }
            @media (min-width: 1024px) { html { font-size: 18px; } }

            .inst-sidebar {
                width: 280px;
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .inst-sidebar.is-open { transform: translateX(0); }
            @media (min-width: 768px) {
                .inst-sidebar { width: 72px; transform: translateX(0); }
                .inst-sidebar:hover { width: 280px; }
                .inst-sidebar .nav-label {
                    opacity: 0; white-space: nowrap;
                    transition: opacity 0.15s ease; pointer-events: none;
                }
                .inst-sidebar:hover .nav-label { opacity: 1; pointer-events: auto; }
            }
            @media (max-width: 767px) {
                .inst-sidebar .nav-label { opacity: 1; pointer-events: auto; }
            }

            .inst-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 25; }
            .inst-backdrop.is-open { display: block; }
            @media (min-width: 768px) { .inst-backdrop { display: none !important; } }

            ::-webkit-scrollbar { width: 6px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: #374151; border-radius: 3px; }
            ::-webkit-scrollbar-thumb:hover { background: #4b5563; }
            .inst-sidebar nav::-webkit-scrollbar { display: none; }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    </head>

    <body class="bg-gray-900 text-gray-100 font-sans" style="font-family: 'Inter', sans-serif;">
        <div class="flex min-h-screen">

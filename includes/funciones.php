<?php
function limpiarEntrada($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirigir($url) {
    header("Location: $url");
    exit;
}

function mostrarError($mensaje) {
    echo "<div class='alert alert-danger mt-2'>$mensaje</div>";
}

function mostrarExito($mensaje) {
    echo "<div class='alert alert-success mt-2'>$mensaje</div>";
}
?>
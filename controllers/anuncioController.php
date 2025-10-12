<?php
require_once __DIR__ . '/../models/Anuncio.php';
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../config/db.php';
session_start();

$anuncioModel = new Anuncio($pdo);

// Crear anuncio
if (isset($_POST['crear_anuncio'])) {
    $tipo = limpiarEntrada($_POST['tipo']);
    $origen = limpiarEntrada($_POST['origen']);
    $destino = limpiarEntrada($_POST['destino']);
    $horaSalida = limpiarEntrada($_POST['horaSalida']);
    $horaRegreso = limpiarEntrada($_POST['horaRegreso']);
    $precio = limpiarEntrada($_POST['precio']);
    $idUsuario = $_SESSION['usuario']['id'];

    $anuncioModel->crear($tipo, $origen, $destino, $horaSalida, $horaRegreso, $precio, $idUsuario);
    redirigir("../public/home.php");
}

// Editar anuncio
if (isset($_POST['editar_anuncio'])) {
    $idAnuncio = limpiarEntrada($_POST['idAnuncio']);
    $tipo = limpiarEntrada($_POST['tipo']);
    $origen = limpiarEntrada($_POST['origen']);
    $destino = limpiarEntrada($_POST['destino']);
    $horaSalida = limpiarEntrada($_POST['horaSalida']);
    $horaRegreso = limpiarEntrada($_POST['horaRegreso']);
    $precio = limpiarEntrada($_POST['precio']);

    $anuncioModel->actualizar($idAnuncio, $tipo, $origen, $destino, $horaSalida, $horaRegreso, $precio);
    redirigir("../public/home.php");
}

// Eliminar anuncio
if (isset($_GET['eliminar'])) {
    $idAnuncio = $_GET['eliminar'];
    $anuncioModel->eliminar($idAnuncio);
    redirigir("../public/home.php");
}
?>
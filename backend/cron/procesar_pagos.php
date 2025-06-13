<?php
/**
 * Script para procesar pagos y cancelar reservas vencidas
 * Este script debe ejecutarse diariamente mediante un cron job
 */

// Incluir archivos necesarios
require_once(__DIR__ . '/../controllers/ReservaControllers.php');

// Inicializar el controlador
$controlador = new ReservaControllers();

// Cancelar reservas pendientes vencidas
$reservasCanceladas = $controlador->cancelarReservasPendientes();

// Registrar la ejecución
$logFile = __DIR__ . '/../logs/procesar_pagos.log';
$mensaje = date('Y-m-d H:i:s') . " - Se cancelaron $reservasCanceladas reservas pendientes vencidas.\n";

// Escribir en el archivo de log
file_put_contents($logFile, $mensaje, FILE_APPEND);

echo $mensaje;
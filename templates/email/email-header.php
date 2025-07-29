<?php
// templates/emails/email-header.php
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Humanitarios - Correo Electrónico</title>
    <style>
        /* Estilos para compatibilidad con clientes de correo */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
        }

        .email-header {
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
        }

        .email-body {
            padding: 30px;
        }

        .email-footer {
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #0d6efd;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 15px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table td {
            padding: 10px;
            border: 1px solid #eee;
        }

        table tr td:first-child {
            font-weight: bold;
            width: 30%;
            background-color: #f9f9f9;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <img src="https://humanitarios.do/wp-content/uploads/2025/01/Logotipo-horizontal-e1736620178908.png" alt="Humanitarios" width="200">
        </div>
        <div class="email-body">
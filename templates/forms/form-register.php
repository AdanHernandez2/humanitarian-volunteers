<?php
defined('ABSPATH') || exit();

// Cargar helpers
require_once __DIR__ . '/helpers/fields.php';
require_once __DIR__  . '/helpers/validation.php';

// Cargar header (incluye lógica de usuario)
include __DIR__ . '/parts/header.php';

// Cargar secciones
include __DIR__ . '/parts/personal-info.php';
include __DIR__ . '/parts/skills.php';
include __DIR__ . '/parts/availability.php';
include __DIR__ . '/parts/experience.php';
include __DIR__ . '/parts/additional-info.php';
include __DIR__ . '/parts/references.php';

// Cargar footer (incluye documento, términos y JS)
include __DIR__ . '/parts/footer.php';

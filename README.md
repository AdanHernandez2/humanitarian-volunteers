# Humanitarian Volunteers

**Plugin de WordPress para la gestión integral de voluntarios y fundaciones.**

[![Versión](https://img.shields.io/badge/versión-1.0.0-blue.svg)](https://github.com/AdanHernandez2/humanitarian-volunteers)
[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-21759b.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb3.svg)](https://www.php.net/)
[![Licencia](https://img.shields.io/badge/licencia-GPL--2.0-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

---

## 📋 Descripción

Humanitarian Volunteers es un plugin para WordPress diseñado para complementar un tema existente que carecia de funcionalidades específicas para la gestión de voluntarios y fundaciones. Nació como una solución práctica cuando un cliente necesitaba ampliar las capacidades de su sitio web sin modificar directamente el tema que estaba utilizando, ya que este no contaba con la documentación necesaria para personalizarlo fácilmente.

El plugin añade una capa completa de gestión de usuarios con roles diferenciados, flujos de aprobación, generación de certificados verificables y notificaciones automatizadas, integrándose de forma transparente con el sistema de perfiles nativo de WordPress y el tema activo.

---

## 🚀 Funcionalidades principales

### Gestión de usuarios y roles
- Registro de usuarios con roles personalizados: **Voluntario** y **Fundación**
- Panel de administración para la gestión de solicitudes pendientes
- Flujo de aprobación/rechazo de nuevos registros
- Perfiles extendidos con campos personalizados según el rol

### Sistema de verificación
- Envío automático de correos electrónicos de verificación al registrarse
- Proceso de confirmación de cuenta mediante enlace único
- Notificaciones por correo electrónico al administrador del sitio cuando hay nuevas solicitudes

### Certificados digitales
- Generación automática de certificados para voluntarios
- Código QR único en cada certificado para verificación de autenticidad
- Página pública de validación de certificados
- Descarga e impresión de certificados desde el perfil del usuario

### Integración con tema existente
- Añade funcionalidades sin entrar en conflicto con las características existentes del tema
- Solución ideal cuando no se dispone de la documentación del tema para personalizaciones directas

---

## 🛠️ Requisitos del sistema

| Requisito | Mínimo |
|-----------|--------|
| WordPress | 5.0 o superior |
| PHP | 7.4 o superior |
| MySQL | 5.6 o superior |

---

## 📦 Instalación

1. Descarga el plugin desde este repositorio
2. Sube la carpeta `humanitarian-volunteers` al directorio `/wp-content/plugins/` de tu instalación de WordPress
3. Activa el plugin desde el menú **Plugins** en el panel de administración
4. Configura los ajustes del plugin desde el nuevo menú "Humanitarian Volunteers" en el panel de administración
5. Configura las páginas necesarias (registro, perfil, validación de certificados) usando los shortcodes proporcionados

---

## 📝 Uso

### Shortcodes disponibles

| Shortcode | Descripción |
|-----------|-------------|
| `[hv_registro]` | Muestra el formulario de registro personalizado |
| `[hv_perfil]` | Muestra el perfil extendido del usuario |
| `[hv_verificar_certificado]` | Página pública para verificar certificados mediante QR |

### Flujo de trabajo

1. **Registro**: El usuario se registra a través del formulario personalizado seleccionando su rol (Voluntario/Fundación)
2. **Verificación por correo**: Recibe un email para confirmar su dirección de correo electrónico
3. **Aprobación administrativa**: El administrador recibe una notificación y revisa la solicitud
4. **Activación**: Una vez aprobado, el usuario puede acceder a todas las funcionalidades según su rol
5. **Certificados**: Los voluntarios pueden generar sus certificados con código QR de verificación

---

## 🔧 Personalización

El plugin está diseñado para heredar los estilos del tema activo, pero puedes personalizar su apariencia mediante:

- **CSS personalizado**: Añade tus estilos en el archivo `assets/css/custom.css`
- **Plantillas**: Copia los archivos de la carpeta `templates/` a tu tema hijo para modificarlos
- **Hooks y filtros**: Consulta la documentación para desarrolladores incluida en la carpeta `/docs`

---

## 🧩 Estructura del plugin

humanitarian-volunteers/
├── admin/ # Panel de administración
│ ├── class-admin.php # Gestión del panel admin
│ └── class-users.php # Gestión de usuarios
├── includes/ # Funcionalidades principales
│ ├── class-register.php # Sistema de registro
│ ├── class-email.php # Sistema de notificaciones
│ ├── class-certificate.php # Generación de certificados
│ └── class-qr.php # Sistema de códigos QR
├── assets/ # Recursos estáticos
│ ├── css/
│ └── js/
├── templates/ # Plantillas de frontend
├── languages/ # Traducciones
└── humanitarian-volunteers.php # Archivo principal

## 🤝 Contribuir

¿Te gustaría contribuir al desarrollo de este plugin? ¡Las contribuciones son bienvenidas!

1. Haz un fork del repositorio
2. Crea una rama para tu funcionalidad (`git checkout -b feature/nueva-funcionalidad`)
3. Realiza tus cambios y haz commit (`git commit -m 'Añade nueva funcionalidad'`)
4. Haz push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

---

## 📄 Licencia

Este proyecto está licenciado bajo la Licencia Pública General de GNU v2.0 (GPL-2.0). Consulta el archivo `LICENSE` para más detalles.

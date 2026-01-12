# INSAT Staging - Guía de Uso para el Equipo

## 🎯 Inicio Rápido

### 1. Acceder al Admin
```
URL: https://stag.insat.com.ar/wp-admin/
Usuario: admin
Contraseña: admin
```

---

## 📝 Tareas Comunes

### ✏️ Editar una Página

1. Ir a Dashboard → **Páginas**
2. Seleccionar la página a editar (ej: "Hogares")
3. Editar contenido con **Gutenberg editor**
4. Usar **INSAT Patterns** desde el panel lateral
5. Click en **Actualizar**

### ➕ Crear un Nuevo Post

1. Ir a Dashboard → **Novedades** (o Tecnología / Historias)
2. Click en **Agregar Nuevo**
3. Completar:
   - **Título**: El título del post
   - **Contenido**: Usar Gutenberg editor
   - **Imagen destacada**: Subir thumbnail
   - **Categoría**: Seleccionar de la derecha
   - **Tags**: Agregar tags relacionados
4. Click en **Publicar**

### 📄 Crear una Nueva Página

1. Ir a Dashboard → **Páginas**
2. Click en **Agregar Nueva**
3. Completar:
   - **Título**: Nombre de la página
   - **Contenido**: Usar Gutenberg editor
   - **Slug**: URL-friendly (se auto-genera)
4. Seleccionar **Página Padre** (opcional)
5. Click en **Publicar**

---

## 🎨 Usando Gutenberg Patterns

### Insertar un Pattern

1. En el editor, hacer click en **+**
2. Buscar: `INSAT Patterns`
3. Elegir Pattern:

#### Pattern: Hero Section
- Título grande + Subtítulo + 2 Botones
- Uso: Portadas de páginas

#### Pattern: Plans Cards
- Grid 3 columnas con cards de planes
- Uso: Mostrar diferentes servicios

#### Pattern: Coverage CTA
- Formulario de verificación de cobertura
- Uso: En sidebar o sección dedicada

#### Pattern: Features List
- 3 columnas con checkmarks
- Uso: Listar características

#### Pattern: Testimonial
- Card con cita de cliente
- Uso: Testimonios de clientes

#### Pattern: FAQ Section
- Preguntas y respuestas
- Uso: Preguntas frecuentes

---

## 🖼️ Agregar Imágenes

### Subir Imagen Destacada
1. En el editor, panel derecho → **Imagen Destacada**
2. Click en **Seleccionar imagen**
3. **Subir** o seleccionar de librería
4. Ajustar crop si es necesario

### Insertar Imagen en Contenido
1. En el editor, click en **+**
2. Buscar **Image** block
3. **Subir imagen** o seleccionar
4. Escribir **alt text** (importante para SEO)
5. Ajustar alineación y tamaño

---

## 🏷️ Usar Categorías y Tags

### Asignar Categoría a un Post
1. En el editor de post, panel derecho
2. Sección **Categorías**
3. Seleccionar una o más categorías

### Agregar Tags
1. Panel derecho → **Tags**
2. Escribir tag nuevo o seleccionar existente
3. Presionar Enter

---

## ⚠️ Cosas Importantes (NO hacer)

### ❌ NO Editar Estos Archivos
- `/wp-config.php` - Configuración de BD
- `/.htaccess` - Reglas de servidor
- `/wp-content/themes/blocksy-child/functions.php` - CPTs y SEO

### ❌ NO Cambiar Estos Slugs
Los slugs están optimizados:
- `/hogares/`
- `/internet-ilimitado/`
- `/tv-satelital/`

### ❌ NO Instalar Plugins Sin Permiso
Cualquier plugin nuevo puede romper el diseño.

---

## 🔍 Preguntas Frecuentes

### P: ¿Cómo cambio el color de un botón?
**R**: Los botones siguen el color primario. Contactar al equipo técnico.

### P: ¿Dónde puedo agregar un formulario de contacto?
**R**: Usar el pattern "Coverage CTA" o contactar al equipo.

### P: ¿Por qué veo "Basic Auth" cuando accedo?
**R**: Es seguridad del staging. No verán esto en producción.

### P: ¿Puedo ver cómo se ve en mobile?
**R**: Sí, presiona F12 → Click en el icono de móvil.

---

## ✅ Checklist - Primer Uso

- [ ] Acceder a https://stag.insat.com.ar/wp-admin/
- [ ] Editar una página
- [ ] Agregar un pattern
- [ ] Crear un nuevo post
- [ ] Subir una imagen
- [ ] Asignar categoría y tags
- [ ] Revistar vista previa en mobile

¡Listo! 🚀

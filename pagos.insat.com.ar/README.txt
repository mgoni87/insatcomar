# Portal de pagos (INSAT)

**Requisitos**: PHP 8.3, MySQL, MySQLi, cURL habilitado.  
**Conexión**: Debe existir `/includes/conn.php` que exponga `$conn` (mysqli).

## Instalación rápida
1. Crear tabla:
   ```sql
   SOURCE sql/schema.sql;
   ```
2. Subir la carpeta al docroot de `pagos.insat.com.ar`.
3. Asegurarse de tener `/includes/conn.php` (no incluido). Ejemplo:
   ```php
   <?php
   $conn = new mysqli('localhost','USER','PASS','DB');
   if ($conn->connect_errno) { die('DB error'); }
   $conn->set_charset('utf8mb4');
   ```

## Variables de entorno (recomendado via `.htaccess` o panel)
- **Mercado Pago** (solo saldo en cuenta):
  - `MP_ACCESS_TOKEN`: token productivo o sandbox.

- **SiPago** (tarjetas crédito/débito/prepaga):
  - `SIPAGO_CLIENT_ID`
  - `SIPAGO_CLIENT_SECRET`
  - `SIPAGO_AUTH_BASE` = `https://auth.geopagos.com` (o preprod)
  - `SIPAGO_API_BASE`  = `https://api.sipago.coop` (o preprod)

## Flujo de uso
- Home (`/index.php`): campo único para ingresar DNI/CUIT/Nº de cliente. Con el CSV actual, **Nº de cliente** (`Codigo Cliente`) es el identificador disponible.
- Resultados: muestra **Pagos recurrentes** (si `Origen` contiene *Facturación Mensual*) o **Pagos puntuales** (resto). Oculta secciones vacías y, si no hay deudas, informa:
  > "No se encontraron pagos pendientes. Para verificar tu saldo ingresá en Autogestión".

- Botones por deuda:
  - **MP saldo** → `payments/mercadopago.php` crea preferencia con todos los *payment_types* excluidos salvo `account_money` (saldo) y redirige al `init_point` de Checkout Pro.
  - **Tarjeta (SiPago)** → `payments/sipago.php` crea una `order` y redirige a `links.checkout`.

## Importar CSV
- Ir a `/admin/` y subir el CSV exportado (separador `;`, codificación Latin1/Windows).  
- Los montos como `138.524,00` se normalizan automáticamente. Fechas `dd-mm-yyyy` se convierten a `yyyy-mm-dd`.

## Seguridad / buenas prácticas
- Agregar validación de origen y un CSRF para los endpoints si los publica externamente.
- Limitar `init_point`/checkout a tiempo razonable y conciliar el estado de la orden (ver webhooks o consultas POST pago).

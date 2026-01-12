#!/bin/bash
# SCRIPT PARA GENERAR .htpasswd EN SERVIDOR STAGING

# Ejecutar en servidor:
# htpasswd -c /home/insatcomar/.htpasswd admin
# (luego ingresar password: admin)

# Hacer que sea legible solo por www-data:
# chmod 644 /home/insatcomar/.htpasswd
# chown root:www-data /home/insatcomar/.htpasswd

# Para PRODUCCIÓN, cambiar credenciales:
# htpasswd -c /home/insatcomar/.htpasswd admin_user_strong
# (ingresar password muy fuerte)

echo "Generando archivo .htpasswd para staging..."
htpasswd -c /home/insatcomar/.htpasswd admin
echo "✅ .htpasswd creado"
echo ""
echo "Verificando permisos..."
chmod 644 /home/insatcomar/.htpasswd
chown root:www-data /home/insatcomar/.htpasswd
echo "✅ Permisos configurados"

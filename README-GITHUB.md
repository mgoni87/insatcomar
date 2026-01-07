# Proyecto InsatComar

Servidor web para **insat.com.ar** - Proyecto web de la cuenta cPanel `insatcomar`.

## Estructura del Repositorio

```
/home/insatcomar/public_html/
├── insat.com.ar/              # Dominio principal
├── autogestion.insat.com.ar/  # Subdominio de autogestion
├── comprar.insat.com.ar/      # Subdominio de compra
├── corporativo.insat.com.ar/  # Subdominio corporativo
├── cobertura/                 # Información de cobertura
└── ...otros archivos
```

## Deploy Automático

El repositorio está configurado con webhooks de GitHub. Cuando hagas `git push` a la rama `main`, 
se ejecutará automáticamente `/webhook.php` en el servidor que hará un `git pull` automático.

### Logs de Deploy

Los logs de deployment se guardan en:
```bash
/tmp/insatcomar_webhook.log
```

Para monitorear en tiempo real:
```bash
ssh -p 5156 root@149.50.143.84
tail -f /tmp/insatcomar_webhook.log
```

## Configuración Git

El repositorio usa SSH con credenciales almacenadas. Para cambios locales:

```bash
git clone https://github.com/mgoni87/insatcomar.git
cd insatcomar
git config user.name "Tu Nombre"
git config user.email "tu@email.com"
git push origin main
```

## Acceso SSH al Servidor

```bash
ssh -p 5156 root@149.50.143.84
cd /home/insatcomar/public_html
git status
```

## Información de Interés

- **Dominio**: insat.com.ar
- **Usuario cPanel**: insatcomar
- **Rama Principal**: main
- **Servidor**: 149.50.143.84 (puerto 5156)

---
Última actualización: 2026-01-06

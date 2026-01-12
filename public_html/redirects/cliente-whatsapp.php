<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Atención al Cliente INSAT</title>
    <meta name="description" content="Contacta con nuestro equipo de atención al cliente de INSAT mediante WhatsApp">
    <meta name="robots" content="noindex, follow">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        
        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: #25D366;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }
        
        h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .message {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .phone {
            display: inline-block;
            background: #f0f0f0;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 600;
            color: #25D366;
            margin-bottom: 30px;
            font-size: 18px;
        }
        
        .button {
            display: inline-block;
            background: #25D366;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s ease;
            margin-bottom: 15px;
        }
        
        .button:hover {
            background: #20BA5A;
        }
        
        .button:active {
            transform: scale(0.98);
        }
        
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-left: 10px;
            vertical-align: middle;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .loader-text {
            color: #999;
            font-size: 14px;
            margin-top: 20px;
        }
        
        .manual-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .manual-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">💬</div>
        <h1>Te estamos derivando</h1>
        <p class="message">Nos conectaremos con <strong>Atención al Cliente</strong> para brindarte la mejor asistencia.</p>
        
        <div class="phone">+54 9 11 2846-5645</div>
        
        <a href="#" id="whatsappLink" class="button">
            Abrir WhatsApp
            <span class="spinner"></span>
        </a>
        
        <p class="loader-text">Redirigiendo en <span id="countdown">5</span> segundos...</p>
        
        <p style="margin-top: 20px; color: #999; font-size: 13px;">
            Si no se abre automáticamente, <a href="#" id="manualLink" class="manual-link">haz clic aquí</a>
        </p>
    </div>

    <script>
        // Datos de la redirección
        const whatsappNumber = '5491128465645';
        const whatsappMessage = 'Hola, Ya soy cliente de INSAT!, para iniciar mi consulta les envio el DNI/CUIT del titular de servicio:';
        const whatsappURL = 'https://wa.me/' + whatsappNumber + '?text=' + encodeURIComponent(whatsappMessage);
        
        // Establecer el href del enlace
        document.getElementById('whatsappLink').href = whatsappURL;
        document.getElementById('manualLink').href = whatsappURL;
        
        // Redireccionamiento automático después de 5 segundos
        let seconds = 5;
        const countdownElement = document.getElementById('countdown');
        
        const countdownInterval = setInterval(() => {
            seconds--;
            countdownElement.textContent = seconds;
            
            if (seconds === 0) {
                clearInterval(countdownInterval);
                window.location.href = whatsappURL;
            }
        }, 1000);
        
        // Clic inmediato en el botón
        document.getElementById('whatsappLink').addEventListener('click', (e) => {
            e.preventDefault();
            clearInterval(countdownInterval);
            window.location.href = whatsappURL;
        });
    </script>
</body>
</html>

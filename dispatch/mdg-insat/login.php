<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Insatcom</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --bg-body: #f1f5f9;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --card-bg: #ffffff;
            --border: #e2e8f0;
        }

        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .brand-logo {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 2rem;
        }

        .brand-logo i {
            color: var(--primary);
        }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border);
        }

        .card-header {
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .card-header h2 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .card-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-main);
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 12px;
            color: var(--text-muted);
        }

        .input-control {
            width: 100%;
            padding: 0.75rem 0.75rem 0.75rem 2.5rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .input-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn-login {
            width: 100%;
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 1rem;
        }

        .btn-login:hover {
            background-color: var(--primary-hover);
        }

        .footer-text {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        .icon-padding-right {
            padding-right: 15px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="brand-logo">
            <i data-lucide="network"></i> Insatcomar
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Bienvenido</h2>
                <p>Ingresa tus credenciales para continuar</p>
            </div>

            <form action="auth.php" method="POST">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <div class="input-wrapper">
                        <i data-lucide="user" size="18" class="icon-padding-right"></i>
                        <input type="text" name="username" id="username" class="input-control" placeholder="Nombre de usuario" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <i data-lucide="lock" size="18" class="icon-padding-right"></i>
                        <input type="password" name="password" id="password" class="input-control" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    Entrar al Sistema
                    <i data-lucide="arrow-right" size="18"></i>
                </button>
            </form>
        </div>

        <div class="footer-text">
            &copy; <?php echo date("Y"); ?> Insatcomar - Gestión de Cartera
        </div>
    </div>

    <script>
        // Inicializar iconos
        lucide.createIcons();
    </script>
</body>
</html>
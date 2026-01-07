<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

/** 1. CONSULTA: CLIENTES POR ESTADO **/
$sql_estados = "SELECT Estado, COUNT(*) as total FROM mdg_clientes 
                WHERE Estado IN ('A Conectar', 'Conectado', 'Suspendido') 
                GROUP BY Estado";
$res_estados = $conexion->query($sql_estados);
$estados_data = ['Conectado' => 0, 'Suspendido' => 0, 'A Conectar' => 0];
while($row = $res_estados->fetch_assoc()) { $estados_data[$row['Estado']] = $row['total']; }

/** 2. CONSULTA: COBRANZA DETALLADA **/
// Cobranza Clientes Activos (Conectado / Suspendido)
$sql_cob_activos = "SELECT 
    SUM(CASE WHEN Saldo <= 0 THEN 1 ELSE 0 END) as cant_al_dia,
    SUM(CASE WHEN Saldo > 0 THEN 1 ELSE 0 END) as cant_con_saldo,
    SUM(CASE WHEN Saldo > 0 THEN Saldo ELSE 0 END) as monto_deuda_activa
    FROM mdg_clientes WHERE Estado IN ('Conectado', 'Suspendido')";
$cob_activos = $conexion->query($sql_cob_activos)->fetch_assoc();

// Cobranza Clientes Desconectados
$sql_cob_desc = "SELECT 
    COUNT(*) as cant_con_saldo,
    SUM(Saldo) as monto_deuda_desc
    FROM mdg_clientes WHERE Estado = 'Desconectado' AND Saldo > 0";
$cob_desc = $conexion->query($sql_cob_desc)->fetch_assoc();

/** 3. CONSULTA: HISTÓRICO REAL **/
$sql_historico = "
    SELECT 
        meses.mes,
        COALESCE(altas.cantidad, 0) as total_altas,
        COALESCE(bajas.cantidad, 0) as total_bajas,
        (SELECT COUNT(*) FROM mdg_clientes 
         WHERE FechaCX < STR_TO_DATE(CONCAT(meses.mes, '-01'), '%Y-%m-%d') 
         AND (FechaDX IS NULL OR FechaDX >= STR_TO_DATE(CONCAT(meses.mes, '-01'), '%Y-%m-%d'))
        ) as activos_inicio
    FROM (
        SELECT DISTINCT DATE_FORMAT(FechaCX, '%Y-%m') as mes FROM mdg_clientes WHERE FechaCX IS NOT NULL
        UNION 
        SELECT DISTINCT DATE_FORMAT(FechaDX, '%Y-%m') as mes FROM mdg_clientes WHERE FechaDX IS NOT NULL
    ) meses
    LEFT JOIN (SELECT DATE_FORMAT(FechaCX, '%Y-%m') as mes, COUNT(*) as cantidad FROM mdg_clientes GROUP BY mes) altas ON meses.mes = altas.mes
    LEFT JOIN (SELECT DATE_FORMAT(FechaDX, '%Y-%m') as mes, COUNT(*) as cantidad FROM mdg_clientes GROUP BY mes) bajas ON meses.mes = bajas.mes
    WHERE meses.mes IS NOT NULL
    ORDER BY meses.mes ASC";

$res_hist = $conexion->query($sql_historico);
$labels = []; $data_altas = []; $data_bajas = []; $data_churn = [];

while($row = $res_hist->fetch_assoc()){
    $labels[] = date("M Y", strtotime($row['mes'] . "-01"));
    $data_altas[] = $row['total_altas'];
    $data_bajas[] = $row['total_bajas'];
    $activos = $row['activos_inicio'] > 0 ? $row['activos_inicio'] : 1;
    $data_churn[] = round(($row['total_bajas'] / $activos) * 100, 2);
}

$sql_ciclo = "SELECT AVG(DATEDIFF(FechaDX, FechaCX)) as promedio FROM mdg_clientes WHERE FechaCX IS NOT NULL AND FechaDX IS NOT NULL";
$ciclo_vida_promedio = $conexion->query($sql_ciclo)->fetch_assoc()['promedio'] ?? 0;

/** 4. CONSULTA: CONEXIONES (CORREGIDA) **/

// 1. Conexiones Únicas y Tipos de Cliente (Corregido)
// Primero obtenemos una lista de IdConexion únicos con su respectivo tipo
$sql_resumen_unicos = "SELECT ClienteTipo, COUNT(DISTINCT IdConexion) as total_tipo 
                       FROM mdg_conexiones 
                       GROUP BY ClienteTipo";
$res_conexiones = $conexion->query($sql_resumen_unicos);

$total_conexiones_unicas = 0;
$tipos_cliente = [];
while($row = $res_conexiones->fetch_assoc()) {
    $tipos_cliente[$row['ClienteTipo']] = (int)$row['total_tipo'];
    $total_conexiones_unicas += (int)$row['total_tipo'];
}

// 2. Productos y sus cantidades totales (Para el gráfico)
$sql_productos = "SELECT Producto, SUM(Cantidad) as total_cantidad 
                  FROM mdg_conexiones 
                  GROUP BY Producto 
                  ORDER BY total_cantidad DESC 
                  LIMIT 10"; // Limitamos a los 10 principales para que el gráfico sea legible
$res_productos = $conexion->query($sql_productos);
$labels_prod = []; $data_prod = [];
while($p = $res_productos->fetch_assoc()) {
    $labels_prod[] = $p['Producto'];
    $data_prod[] = $p['total_cantidad'];
}

// 3. Promociones (Corregido: Validar que el campo no esté vacío o sea 'NULL')
$sql_promos = "SELECT Promo, COUNT(*) as total 
               FROM mdg_conexiones 
               WHERE Promo IS NOT NULL AND Promo != '' AND Promo != 'NULL'
               GROUP BY Promo 
               ORDER BY total DESC";
$res_promos = $conexion->query($sql_promos);

/** 4.2 CONSULTA: PRODUCTOS (PARA GRÁFICO) **/
$sql_productos = "SELECT Producto, SUM(Cantidad) as total_cantidad 
                  FROM mdg_conexiones 
                  GROUP BY Producto 
                  ORDER BY total_cantidad DESC"; 
$res_productos = $conexion->query($sql_productos);

$labels_prod = []; 
$data_prod = [];

if ($res_productos && $res_productos->num_rows > 0) {
    while($p = $res_productos->fetch_assoc()) {
        // Limpiamos el nombre del producto de comillas para evitar errores en JS
        $labels_prod[] = str_replace('"', '', $p['Producto']);
        $data_prod[] = (int)$p['total_cantidad'];
    }
}

/** 4.3 CONSULTA: PUNTOS PARA MAPA (ÚNICOS POR CONEXIÓN) **/
$sql_mapa = "SELECT IdConexion, Latitud, Longitud, ClienteNombre, Plan, EstadoConexion 
             FROM mdg_conexiones 
             WHERE Latitud IS NOT NULL AND Longitud IS NOT NULL 
             AND Latitud != 0 AND Longitud != 0
             GROUP BY IdConexion"; // Agrupamos para no repetir puntos
$res_mapa = $conexion->query($sql_mapa);
$puntos_mapa = [];
while($p = $res_mapa->fetch_assoc()) {
    $puntos_mapa[] = [
        'lat' => (float)$p['Latitud'],
        'lng' => (float)$p['Longitud'],
        'info' => "<strong>{$p['ClienteNombre']}</strong><br>ID: {$p['IdConexion']}<br>Plan: {$p['Plan']}<br>Estado: {$p['EstadoConexion']}"
    ];
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Insatcom</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    <style>
        :root {
            --bg-body: #f1f5f9;
            --bg-nav: #1e293b;
            --primary: #2563eb;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --card-bg: #ffffff;
        }

        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
        }

        .navbar {
            background-color: var(--bg-nav);
            color: white;
            padding: 0.75rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar .brand { font-size: 1.25rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .navbar .nav-links { display: flex; gap: 1.5rem; align-items: center; }
        .navbar a { color: #cbd5e1; text-decoration: none; font-size: 0.9rem; transition: color 0.2s; }
        .navbar a:hover { color: white; }
        .user-profile { display: flex; align-items: center; gap: 10px; background: #334155; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; }

        .main-content { padding: 2rem; max-width: 1400px; margin: 0 auto; }

        .welcome-header { margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem; }
        .welcome-header .avatar { width: 60px; height: 60px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: bold; }
        .welcome-header h2 { margin: 0; font-size: 1.5rem; }
        .welcome-header p { margin: 4px 0 0 0; color: var(--text-muted); font-size: 0.9rem; }

        .quick-actions { display: flex; gap: 12px; margin-bottom: 2rem; flex-wrap: wrap; }
        .action-btn { background: white; border: 1px solid #e2e8f0; padding: 0.6rem 1.2rem; border-radius: 8px; display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; color: var(--text-main); transition: all 0.2s; text-decoration: none; }
        .action-btn:hover { background: #f8fafc; border-color: #cbd5e1; transform: translateY(-1px); }
        .action-btn.active { background: var(--primary); color: white; border-color: var(--primary); }

        .dashboard-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 1.5rem; }
        .card { background: var(--card-bg); border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .card-header { display: flex; align-items: center; gap: 8px; margin-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem; }
        .card-header h3 { margin: 0; font-size: 0.95rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .card-header i { color: var(--primary); }

        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 1rem; }
        .stat-item { text-align: left; }
        .stat-label { font-size: 0.75rem; color: var(--text-muted); display: block; margin-bottom: 5px; }
        .stat-number { font-size: 1.5rem; font-weight: 800; display: block; }
        .stat-currency { font-size: 0.85rem; color: var(--text-muted); font-weight: 600; margin-top: 4px; display: block; }

        .col-4 { grid-column: span 4; }
        .col-8 { grid-column: span 8; }

        @media (max-width: 1024px) {
            .col-4, .col-8 { grid-column: span 12; }
        }

        .chart-box { height: 260px; position: relative; }

        #mapa-conexiones {
            height: 600px;
            width: 100%;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            z-index: 1; /* Evita que se superponga a menús */
        }
        .col-full { grid-column: span 12; }

        /* Estilo para los grupos de clústeres */
        .mycluster {
            background-color: rgba(0, 123, 186, 0.7);
            border: 4px solid #007cba;
            border-radius: 50%;
            text-align: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
            width: 30px !important;
            height: 30px !important;
            line-height: 25px !important;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="brand">
            <i data-lucide="network"></i> Insatcomar
        </div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="list_clientes.php">Clientes</a>
            <a href="import_manual.php">Herramientas</a>
            <a href="#">Configuración</a>
            <div class="user-profile">
                <i data-lucide="user" size="16"></i>
                <span><?php echo $_SESSION['username'] ?? 'Usuario'; ?></span>
            </div>
            <a href="logout.php" title="Cerrar Sesión"><i data-lucide="log-out" size="18"></i></a>
        </div>
    </nav>

    <main class="main-content">
        <div class="welcome-header">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?></div>
            <div>
                <h2>Panel Histórico de Gestión</h2>
                <p><i data-lucide="calendar" size="14" style="vertical-align: middle;"></i> <?php echo date("d-m-Y"); ?> • Cobranza y Gestión de Cartera</p>
            </div>
        </div>

        <div class="quick-actions">
            <a href="#" id="btn-clientes" class="action-btn active"><i data-lucide="users" size="16"></i> Clientes</a>
            <a href="#" id="btn-conexiones" class="action-btn"><i data-lucide="network" size="16"></i> Conexiones</a>
            <a href="import_manual.php" class="action-btn"><i data-lucide="file-up" size="16"></i> Importar csv</a>
        </div>

        <div id="vista-clientes" class="dashboard-grid">
            <div class="col-4" style="display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- Estados -->
                <div class="card">
                    <div class="card-header">
                        <i data-lucide="activity" size="18"></i>
                        <h3>Clientes por estado</h3>
                    </div>
                    <div class="stats-row">
                        <div class="stat-item">
                            <span class="stat-label">Conectados</span>
                            <span class="stat-number" style="color: var(--success);"><?php echo $estados_data['Conectado']; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Suspendos</span>
                            <span class="stat-number" style="color: var(--warning);"><?php echo $estados_data['Suspendido']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Cobranza Activos -->
                <div class="card">
                    <div class="card-header">
                        <i data-lucide="wallet" size="18"></i>
                        <h3>Cobranza Cartera Activa</h3>
                    </div>
                    <div class="stats-row">
                        <div class="stat-item">
                            <span class="stat-label">Al día</span>
                            <span class="stat-number"><?php echo $cob_activos['cant_al_dia'] ?? 0; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Con deuda</span>
                            <span class="stat-number" style="color: var(--danger);"><?php echo $cob_activos['cant_con_saldo'] ?? 0; ?></span>
                            <span class="stat-currency">$<?php echo number_format($cob_activos['monto_deuda_activa'] ?? 0, 2, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Cobranza Desconectados -->
                <div class="card">
                    <div class="card-header">
                        <i data-lucide="user-x" size="18"></i>
                        <h3>Cobranza Desconectados</h3>
                    </div>
                    <div class="stats-row">
                        <div class="stat-item">
                            <span class="stat-label">Clientes</span>
                            <span class="stat-number"><?php echo $cob_desc['cant_con_saldo'] ?? 0; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Saldo pendiente</span>
                            <span class="stat-number" style="color: var(--danger);">$<?php echo number_format($cob_desc['monto_deuda_desc'] ?? 0, 2, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i data-lucide="clock" size="18"></i>
                        <h3>Ciclo de Vida</h3>
                    </div>
                    <span class="stat-number"><?php echo round($ciclo_vida_promedio); ?> <small style="font-size: 0.8rem; font-weight: 400;">días promedio</small></span>
                </div>
            </div>

            <div class="col-8">
                <div class="card">
                    <div class="card-header">
                        <i data-lucide="trending-up" size="18"></i>
                        <h3>Evolución Histórica (Altas vs Bajas)</h3>
                    </div>
                    <div class="chart-box">
                        <canvas id="movChart"></canvas>
                    </div>
                </div>

                <div class="card" style="margin-top: 1.5rem;">
                    <div class="card-header">
                        <i data-lucide="pie-chart" size="18"></i>
                        <h3>Tasa de Churn Mensual (%)</h3>
                    </div>
                    <div class="chart-box" style="height: 180px;">
                        <canvas id="churnChart"></canvas>
                    </div>
                </div>
            </div>

        </div>
        <div id="vista-conexiones" class="dashboard-grid" style="display: none;">
            <div class="card col-4">
                <div class="card-header"><i data-lucide="wifi" size="18"></i><h3>Resumen de Conexiones</h3></div>
                <div class="stat-item">
                    <span class="stat-label">Total de conexiones</span>
                    <span class="stat-number" style="color: var(--primary);"><?php echo $total_conexiones_unicas; ?></span>
                </div>
                <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 15px 0;">
                <span class="stat-label">Por tipo de cliente</span>
                <?php foreach($tipos_cliente as $tipo => $cant): ?>
                    <div style="display: flex; justify-content: space-between; margin-top: 8px;">
                        <small><strong><?php echo $tipo; ?>:</strong></small>
                        <small><?php echo $cant; ?></small>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card col-4">
                <div class="card-header"><i data-lucide="package" size="18"></i><h3>Top Productos</h3></div>
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="prodChart"></canvas>
                </div>
            </div>

            <div class="card col-4">
                <div class="card-header"><i data-lucide="tag" size="18"></i><h3>Promociones Activas</h3></div>
                <div style="max-height: 300px; overflow-y: auto;">
                    <?php if($res_promos->num_rows > 0): ?>
                        <?php while($pr = $res_promos->fetch_assoc()): ?>
                            <div style="background: #f8fafc; padding: 10px; border-radius: 6px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.8rem; font-weight: 600;"><?php echo $pr['Promo']; ?></span>
                                <span class="stat-number" style="font-size: 1rem; color: var(--success);"><?php echo $pr['total']; ?></span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="color: var(--text-muted); font-size: 0.85rem; text-align: center;">No se detectaron promociones activas.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card col-full" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <i data-lucide="map" size="18"></i>
                    <h3>Geolocalización de Conexiones</h3>
                </div>
                <div id="mapa-conexiones"></div>
            </div>
        </div>
    </main>

<script>
    // 1. Inicializar Iconos
    lucide.createIcons();

    // 1. Declarar variables globales al inicio del script
    let lineChart = null; 
    let churnChart = null;
    let myProdChart = null;

    function initClientesCharts() {
        // CAMBIO: Usar 'movChart' que es el ID que tienes en el HTML
        const ctxLine = document.getElementById('movChart');
        const ctxChurn = document.getElementById('churnChart');
        
        // Verificamos que el elemento exista y que el gráfico no se haya creado ya
        if (ctxLine && !lineChart) {
            lineChart = new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [
                        { 
                            label: 'Altas', 
                            data: <?php echo json_encode($data_altas); ?>, 
                            borderColor: '#10b981', // Volvemos al verde de la versión vieja
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4 
                        },
                        { 
                            label: 'Bajas', 
                            data: <?php echo json_encode($data_bajas); ?>, 
                            borderColor: '#ef4444', 
                            borderDash: [5, 5], 
                            tension: 0.4 
                        }
                    ]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        if (ctxChurn && !churnChart) {
            churnChart = new Chart(ctxChurn, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($labels); ?>,
                    datasets: [{ 
                        label: '% Churn', 
                        data: <?php echo json_encode($data_churn); ?>, 
                        backgroundColor: '#2563eb',
                        borderRadius: 4
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
                }
            });
        }
    }

    // Asegúrate de llamar a la función al cargar la página
    window.onload = () => {
        lucide.createIcons();
        initClientesCharts();
    };

    // 4. Función para inicializar el gráfico de la pestaña CONEXIONES
    function renderProdChart() {
        const ctx = document.getElementById('prodChart');
        if (!ctx) return;

        if (myProdChart) myProdChart.destroy();

        myProdChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels_prod); ?>,
                datasets: [{
                    label: 'Cantidad Total',
                    data: <?php echo json_encode($data_prod); ?>,
                    backgroundColor: '#2563eb',
                    borderRadius: 5
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }

    // 5. Función de intercambio de Vistas (Corregida)
    function toggleView(view) {
        const vClientes = document.getElementById('vista-clientes');
        const vConexiones = document.getElementById('vista-conexiones');
        const btnC = document.getElementById('btn-clientes');
        const btnX = document.getElementById('btn-conexiones');

        if (view === 'conexiones') {
            vClientes.style.display = 'none';
            vConexiones.style.display = 'grid';
            btnX.classList.add('active');
            btnC.classList.remove('active');
            
            // Renderizar con pequeño delay para que el DOM se actualice
            setTimeout(() => {
                renderProdChart();
                renderMapaConexiones(); // <--- Llamada nueva
            }, 150);
        } else {
            vClientes.style.display = 'grid';
            vConexiones.style.display = 'none';
            btnC.classList.add('active');
            btnX.classList.remove('active');
            
            // Re-inicializar gráficos de clientes si es necesario
            setTimeout(initClientesCharts, 150);
        }
    }

    // 6. Configuración de Eventos de botones
    document.getElementById('btn-clientes').addEventListener('click', (e) => {
        e.preventDefault();
        toggleView('clientes');
    });

    document.getElementById('btn-conexiones').addEventListener('click', (e) => {
        e.preventDefault();
        toggleView('conexiones');
    });

    // 7. Ejecución inicial
    window.onload = () => {
        initClientesCharts();
    };

    let mapConexiones = null;
    let clusterGroup = null;

    function renderMapaConexiones() {
        const puntos = <?php echo json_encode($puntos_mapa); ?>;
        const mapaDiv = document.getElementById('mapa-conexiones');
        
        if (!mapaDiv) return;

        if (!mapConexiones) {
            // Inicializar mapa
            mapConexiones = L.map('mapa-conexiones').setView([-38.416097, -63.616672], 6);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(mapConexiones);

            // Configurar el grupo de clústeres con el estilo del archivo de referencia
            clusterGroup = L.markerClusterGroup({
                maxClusterRadius: 25,
                iconCreateFunction: function(cluster) {
                    return L.divIcon({
                        html: '<b>' + cluster.getChildCount() + '</b>',
                        className: 'mycluster',
                        iconSize: L.point(30, 30)
                    });
                }
            });

            const bounds = [];

            puntos.forEach(p => {
                // Determinar color según estado (similar a la lógica del archivo de referencia)
                const color = p.estado === 'Conectado' ? 'green' : 'orange';

                // Crear el marcador circular (circleMarker) solicitado
                const marker = L.circleMarker([p.lat, p.lng], {
                    radius: 6,
                    fillColor: color,
                    color: '#000',
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.8
                });

                marker.bindPopup(p.info);
                clusterGroup.addLayer(marker);
                bounds.push([p.lat, p.lng]);
            });

            mapConexiones.addLayer(clusterGroup);

            if (bounds.length > 0) {
                mapConexiones.fitBounds(bounds, { padding: [50, 50] });
            }
        } else {
            // Ajustar tamaño si se cambió de pestaña
            setTimeout(() => {
                mapConexiones.invalidateSize();
            }, 100);
        }
    }
</script>
</body>
</html>
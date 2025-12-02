<?php 
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

requireAdmin();
global $pdo;

// Filtros
$filterMonth = $_GET['month'] ?? date('m');
$filterYear = $_GET['year'] ?? date('Y');
$searchProduct = $_GET['product'] ?? '';
$startDate = "$filterYear-$filterMonth-01";
$endDate = date("Y-m-t", strtotime($startDate));

// Estadísticas rápidas
$totalProductos = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalVentasStmt = $pdo->prepare("SELECT SUM(oi.quantity*oi.price) FROM order_items oi JOIN orders o ON oi.order_id=o.id WHERE o.created_at BETWEEN :start AND :end");
$totalVentasStmt->execute(['start'=>$startDate, 'end'=>$endDate]);
$totalVentas = $totalVentasStmt->fetchColumn() ?: 0;

$totalOrdenesStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE created_at BETWEEN :start AND :end");
$totalOrdenesStmt->execute(['start'=>$startDate, 'end'=>$endDate]);
$totalOrdenes = $totalOrdenesStmt->fetchColumn();

$totalClientesStmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) FROM orders WHERE created_at BETWEEN :start AND :end");
$totalClientesStmt->execute(['start'=>$startDate, 'end'=>$endDate]);
$totalClientes = $totalClientesStmt->fetchColumn();

// Productos más vendidos con filtro de producto
$sqlProducts = "
    SELECT p.id, p.name, SUM(oi.quantity) AS total_vendido
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.created_at BETWEEN :start AND :end
";
$params = ['start'=>$startDate,'end'=>$endDate];

if($searchProduct){
    $sqlProducts .= " AND p.name LIKE :prod";
    $params['prod'] = "%$searchProduct%";
}

$sqlProducts .= " GROUP BY p.id ORDER BY total_vendido DESC LIMIT 10";
$stmt = $pdo->prepare($sqlProducts);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para Chart.js
$labels = [];
$data = [];
$colors = [];
$palette = ['#ff6384','#36a2eb','#ff9f40','#4bc0c0','#9966ff','#c9cbcf','#ffcd56','#8aff33','#ff33a8','#3385ff'];
$totalVendido = array_sum(array_column($productos, 'total_vendido'));
foreach($productos as $i => $prod){
    $labels[] = $prod['name'];
    $data[] = $prod['total_vendido'];
    $colors[] = $palette[$i % count($palette)];
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">

    <!-- Filtros modernos -->
    <form method="GET" class="d-flex justify-content-center align-items-center gap-3 mb-4 flex-wrap">
        <input type="text" name="product" placeholder="🔍 Buscar producto..." value="<?= htmlspecialchars($searchProduct) ?>" style="padding:8px 12px; border-radius:12px; border:1px solid #ccc; min-width:180px; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
        
        <select name="month" style="padding:8px 12px; border-radius:12px; border:1px solid #ccc; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
            <?php for($m=1;$m<=12;$m++): ?>
                <option value="<?= $m ?>" <?= $m==$filterMonth?'selected':'' ?>><?= str_pad($m,2,'0',STR_PAD_LEFT) ?></option>
            <?php endfor; ?>
        </select>
        <select name="year" style="padding:8px 12px; border-radius:12px; border:1px solid #ccc; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
            <?php for($y=date('Y'); $y>=2020; $y--): ?>
                <option value="<?= $y ?>" <?= $y==$filterYear?'selected':'' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
        <button type="submit" style="padding:8px 18px; background:#007bff; color:white; border:none; border-radius:12px; box-shadow:0 4px 8px rgba(0,0,0,0.15); transition:0.2s;">Filtrar</button>
    </form>

    <!-- Estadísticas rápidas -->
    <div class="row mb-5">
        <?php 
        $stats = [
            ['title'=>'Productos', 'value'=>$totalProductos, 'color'=>'#17a2b8'],
            ['title'=>'Ventas', 'value'=>'L. '.number_format($totalVentas,2), 'color'=>'#fd7e14'],
            ['title'=>'Órdenes', 'value'=>$totalOrdenes, 'color'=>'#6f42c1'],
            ['title'=>'Clientes', 'value'=>$totalClientes, 'color'=>'#28a745'],
        ];
        foreach($stats as $s): ?>
            <div class="col-md-3 mb-3">
                <div style="background:<?= $s['color'] ?>; color:white; padding:25px; border-radius:15px; box-shadow:0 6px 15px rgba(0,0,0,0.2); text-align:center; transition:transform .3s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                    <h5 style="font-weight:500; letter-spacing:0.5px;"><?= $s['title'] ?></h5>
                    <h2 style="font-weight:700; font-size:1.8rem; margin-top:8px;"><?= $s['value'] ?></h2>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Gráfico Top 10 Productos animado con degradado -->
    <div class="card shadow-sm p-4 rounded mb-5">
        <h2 class="mb-4 text-center">Top 10 Productos Más Vendidos</h2>
        <canvas id="topProductsChart"></canvas>
    </div>

    <!-- Tabla ranking con mini gráficos animados -->
    <div class="card shadow-sm p-4 rounded mb-5">
        <h4 class="mb-3 text-center">Ranking de Productos</h4>
        <table style="width:100%; border-collapse: collapse; text-align:left;">
            <thead>
                <tr style="background:#1a1a1a; color:white;">
                    <th style="padding:10px;">#</th>
                    <th style="padding:10px;">Producto</th>
                    <th style="padding:10px;">Cantidad Vendida</th>
                    <th style="padding:10px;">Porcentaje del Total</th>
                    <th style="padding:10px;">Evolución Mensual</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($productos as $i => $p): 
                    $stmtLine = $pdo->prepare("SELECT DAY(o.created_at) as dia, SUM(oi.quantity) as vendidos FROM order_items oi JOIN orders o ON oi.order_id=o.id WHERE oi.product_id=:pid AND o.created_at BETWEEN :start AND :end GROUP BY dia ORDER BY dia");
                    $stmtLine->execute(['pid'=>$p['id'],'start'=>$startDate,'end'=>$endDate]);
                    $ventasDia = $stmtLine->fetchAll(PDO::FETCH_ASSOC);
                    $dias = array_column($ventasDia,'dia');
                    $cantidades = array_column($ventasDia,'vendidos');
                ?>
                    <tr style="border-bottom:1px solid #ddd;">
                        <td style="padding:8px;"><?= $i+1 ?></td>
                        <td style="padding:8px; font-weight:500;"><?= htmlspecialchars($p['name']) ?></td>
                        <td style="padding:8px;"><?= $p['total_vendido'] ?></td>
                        <td style="padding:8px;"><?= $totalVendido>0 ? round($p['total_vendido']/$totalVendido*100,2).'%' : '0%' ?></td>
                        <td style="padding:8px;">
                            <canvas id="lineChart<?= $i ?>" style="height:50px;"></canvas>
                            <script>
                            new Chart(document.getElementById('lineChart<?= $i ?>'), {
                                type:'line',
                                data:{
                                    labels: <?= json_encode($dias) ?>,
                                    datasets:[{
                                        data: <?= json_encode($cantidades) ?>,
                                        borderColor: '<?= $colors[$i] ?>',
                                        backgroundColor: '<?= $colors[$i] ?>55',
                                        fill:true,
                                        tension:0.3,
                                        pointRadius:2,
                                        pointHoverRadius:4
                                    }]
                                },
                                options:{
                                    responsive:true,
                                    plugins:{legend:{display:false}},
                                    animation:{duration:1000},
                                    scales:{x:{display:false}, y:{display:false}}
                                }
                            });
                            </script>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('topProductsChart').getContext('2d');
const gradient = ctx.createLinearGradient(0,0,0,400);
gradient.addColorStop(0,'#36a2eb');
gradient.addColorStop(1,'#4bc0c0');

const topProductsChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels); ?>,
        datasets: [{
            label: 'Cantidad Vendida',
            data: <?= json_encode($data); ?>,
            backgroundColor: gradient,
            borderColor: '#00000033',
            borderWidth: 1,
            borderRadius: 12,
        }]
    },
    options: {
        responsive: true,
        animation: { duration: 1200 },
        plugins: {
            legend: { display: false },
            title: { 
                display: true, 
                text: 'Top 10 Productos Más Vendidos', 
                font: { size: 20, weight: '600' }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `Vendidos: ${context.raw}`;
                    }
                }
            }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { ticks: { autoSkip: false } }
        }
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

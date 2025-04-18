<?php
// 1. Configuración inicial
$usuario_buscado = $_GET['usuario'] ?? null;
$mostrar_todos = isset($_GET['todos']);

// 2. Cargar archivos CSV
$inventario = array_map('str_getcsv', file('inventario_base.csv'));
$movimientos = file_exists('movimientos.csv') ? array_map('str_getcsv', file('movimientos.csv')) : [];

// 3. Procesar encabezados
array_shift($inventario); // Eliminar encabezados del inventario
if (!empty($movimientos)) array_shift($movimientos); // Eliminar encabezados de movimientos

// 4. Filtrar movimientos por usuario (si aplica)
$movimientos_filtrados = [];
foreach ($movimientos as $m) {
    if ($mostrar_todos || !$usuario_buscado || $m[1] == $usuario_buscado) {
        $movimientos_filtrados[] = $m;
    }
}

// 5. Calcular resumen por artículo
$resultados = [];
foreach ($inventario as $item) {
    $entregas = $consumos = 0;
    foreach ($movimientos_filtrados as $m) {
        if ($m[0] == $item[0]) { // Coincide ID de artículo
            if ($m[2] == 'entrega') $entregas += $m[3];
            else $consumos += $m[3];
        }
    }
    
    $resultados[] = [
        'nombre' => $item[1],
        'inicial' => $item[2],
        'entregas' => $entregas,
        'consumos' => $consumos,
        'disponible' => $item[2] + $entregas - $consumos
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-responsive { 
            max-height: 70vh;
            overflow-y: auto;
        }
        .titulo-usuario {
            font-size: 1.1rem;
            color: #6c757d;
        }
        th {
            position: sticky;
            top: 0;
            background: white;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <!-- Encabezado con buscador -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <h1 class="h3 text-center mb-3">📊 Inventario</h1>
                
                <?php if ($usuario_buscado): ?>
                    <p class="text-center titulo-usuario mb-3">
                        Usuario: <strong><?= htmlspecialchars($usuario_buscado) ?></strong>
                    </p>
                <?php endif; ?>
                
                <form method="get" class="row g-2">
                    <div class="col-md-8">
                        <input type="number" name="usuario" class="form-control" 
                               value="<?= htmlspecialchars($usuario_buscado) ?>" 
                               placeholder="Ingrese ID de usuario">
                    </div>
                    <div class="col-md-4 d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            🔍 Buscar
                        </button>
                        <?php if ($usuario_buscado): ?>
                            <a href="?todos=1" class="btn btn-outline-secondary">
                                Ver todos
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resultados -->
        <div class="card shadow">
            <div class="card-body p-0">
                <?php if (empty($resultados)): ?>
                    <div class="alert alert-warning m-4">
                        No se encontraron resultados.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Artículo</th>
                                    <th class="text-end">Inicial</th>
                                    <th class="text-end">Entregas</th>
                                    <th class="text-end">Consumos</th>
                                    <th class="text-end">Disponible</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resultados as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['nombre']) ?></td>
                                    <td class="text-end"><?= number_format($item['inicial'], 2) ?></td>
                                    <td class="text-end"><?= number_format($item['entregas'], 2) ?></td>
                                    <td class="text-end"><?= number_format($item['consumos'], 2) ?></td>
                                    <td class="text-end fw-bold">
                                        <?= number_format($item['disponible'], 2) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
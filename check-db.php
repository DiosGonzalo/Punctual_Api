#!/usr/bin/env php
<?php

/**
 * Script para verificar la conexión a la base de datos
 * Ejecutar: php check-db.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "==========================================\n";
echo "Verificación de Base de Datos\n";
echo "==========================================\n\n";

try {
    $pdo = DB::connection()->getPdo();
    echo "✅ Conexión a la base de datos: EXITOSA\n";
    echo "   Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
    echo "   Base de datos: " . env('DB_DATABASE') . "\n";
    echo "   Host: " . env('DB_HOST') . "\n";
    echo "   Puerto: " . env('DB_PORT') . "\n\n";

    // Verificar tablas
    echo "Verificando tablas...\n";
    $tables = DB::select('SHOW TABLES');
    
    if (empty($tables)) {
        echo "⚠️  No hay tablas en la base de datos.\n";
        echo "   Ejecuta: php artisan migrate\n\n";
    } else {
        echo "✅ Tablas encontradas: " . count($tables) . "\n";
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            $count = DB::table($tableName)->count();
            echo "   - $tableName: $count registros\n";
        }
    }

    echo "\n==========================================\n";
    echo "Verificación completada\n";
    echo "==========================================\n";

} catch (\Exception $e) {
    echo "❌ Error de conexión:\n";
    echo "   " . $e->getMessage() . "\n\n";
    echo "Verifica tu archivo .env:\n";
    echo "   DB_CONNECTION=" . env('DB_CONNECTION') . "\n";
    echo "   DB_HOST=" . env('DB_HOST') . "\n";
    echo "   DB_PORT=" . env('DB_PORT') . "\n";
    echo "   DB_DATABASE=" . env('DB_DATABASE') . "\n";
    echo "   DB_USERNAME=" . env('DB_USERNAME') . "\n\n";
    exit(1);
}

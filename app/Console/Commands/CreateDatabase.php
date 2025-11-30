<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateDatabase extends Command
{
    protected $signature = 'db:create {name}';
    protected $description = 'Crear base de datos en MySQL';

    public function handle()
    {
        $name = $this->argument('name');

        try {
            // Conectar al servidor MySQL (sin seleccionar base)
            $connection = mysqli_connect(
                env('DB_HOST', '127.0.0.1'),
                env('DB_USERNAME', 'root'),
                env('DB_PASSWORD', ''),
                null, // ← muy importante: no seleccionar base
                env('DB_PORT', 3306)
            );

            if (!$connection) {
                throw new \Exception('Error de conexión: ' . mysqli_connect_error());
            }

            // Crear base
            $sql = "CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
            if (mysqli_query($connection, $sql)) {
                $this->info("Base de datos '$name' creada correctamente.");
            } else {
                throw new \Exception("Error al crear la base: " . mysqli_error($connection));
            }

            mysqli_close($connection);

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}

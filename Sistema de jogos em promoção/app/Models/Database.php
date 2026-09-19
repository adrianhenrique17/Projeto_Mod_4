<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;

final class Database
{
    private const HOST = 'localhost';
    private const DATABASE = 'promocoes_gamer';
    private const USER = 'root';
    private const PASSWORD = '';

    private ?PDO $connection = null;

    public function connect(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        try {
            $this->connection = new PDO(
                'mysql:host=' . self::HOST . ';dbname=' . self::DATABASE . ';charset=utf8mb4',
                self::USER,
                self::PASSWORD,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $exception) {
            throw new PDOException(
                'Não foi possível conectar ao MySQL. Importe database/promocoes.sql e confira o XAMPP. ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception
            );
        }

        return $this->connection;
    }
}

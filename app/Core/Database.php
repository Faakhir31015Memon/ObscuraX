<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;
use Throwable;

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $config = config('db');
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['name'],
            $config['charset']
        );

        try {
            self::$pdo = new PDO($dsn, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::ATTR_PERSISTENT => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Database connection failed. Check your .env settings and make sure MySQL is running. '
                . '(' . $e->getMessage() . ')',
                (int) $e->getCode(),
                $e
            );
        }

        return self::$pdo;
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($params);
        return $statement;
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function value(string $sql, array $params = [], mixed $default = null): mixed
    {
        $value = self::query($sql, $params)->fetchColumn();
        return $value === false ? $default : $value;
    }

    public static function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(static fn (string $c): string => ':' . $c, $columns);
        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $table,
            implode('`, `', $columns),
            implode(', ', $placeholders)
        );

        $stmt = self::pdo()->prepare($sql);
        foreach ($data as $column => $value) {
            $stmt->bindValue(':' . $column, $value, self::paramType($value));
        }
        $stmt->execute();

        return (int) self::pdo()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(static fn (string $c): string => "`$c` = :$c", array_keys($data)));
        $sql = sprintf('UPDATE `%s` SET %s WHERE %s', $table, $set, $where);
        $stmt = self::pdo()->prepare($sql);
        foreach ($data as $column => $value) {
            $stmt->bindValue(':' . $column, $value, self::paramType($value));
        }
        foreach ($whereParams as $key => $value) {
            $stmt->bindValue(':' . ltrim((string) $key, ':'), $value, self::paramType($value));
        }
        $stmt->execute();
        return $stmt->rowCount();
    }

    public static function transaction(callable $callback): mixed
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();
        try {
            $result = $callback();
            $pdo->commit();
            return $result;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    private static function paramType(mixed $value): int
    {
        return match (true) {
            is_int($value) => PDO::PARAM_INT,
            is_bool($value) => PDO::PARAM_BOOL,
            is_null($value) => PDO::PARAM_NULL,
            default => PDO::PARAM_STR,
        };
    }
}

<?php declare(strict_types=1);

namespace Autoescape;

use PDO;

class Db
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @param array<string, mixed> $options
     * @return array<int, array<string, mixed>>|false
     */
    public function fetchAll(string $statement, array $options = [], int $mode = PDO::FETCH_DEFAULT): array|false
    {
        [$statement, $parameters] = $this->extractParameters($statement);

        $stmt = $this->pdo->prepare($statement, $options);
        if ($stmt === false) {
            return false;
        }
        $stmt->execute($parameters);
        return $stmt->fetchAll($mode);
    }

    /**
     * @return array{0: string, 1: array<string, string>}
     */
    private function extractParameters(string $statement): array
    {
        $parameters = [];
        $pattern = '/\{escaped:([A-Za-z0-9+\/=]+)\}/';

        $newStatement = preg_replace_callback($pattern, function ($matches) use (&$parameters) {
            $decodedValue = base64_decode($matches[1]);
            $paramName = ':param' . count($parameters);
            $parameters[$paramName] = $decodedValue;
            return $paramName;
        }, $statement);

        return [$newStatement, $parameters];
    }
}

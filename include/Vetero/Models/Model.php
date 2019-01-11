<?php
declare(strict_types=1);

namespace Vetero\Models;

use Slim\Container;

/**
 * Class Model
 * @package Vetero\Models
 */
class Model
{
    protected $container;

    /** @var \PDO $db */
    protected $db;

    /** @var \Monolog\Logger */
    protected $logger;

    /**
     * Constructor
     *
     * @param Container $c
     */
    public function __construct(Container $c)
    {
        $this->container = $c;
        $this->db = $c->get('db');
        $this->logger = $c->get('Logger');
    }

    /**
     * Prepare and execute a prepared statement.
     *
     * @param string $query
     * @param array|null $params
     * @return \PDOStatement
     * @throws \PDOException
     */
    private function stmt(string $query, ?array $params = null): \PDOStatement
    {
        $this->logger->debug('Executing query', ['query' => $query, 'params' => $params]);

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Execute a query.
     *
     * @param string $query
     * @param array|null $params
     * @throws \PDOException
     */
    protected function query(string $query, ?array $params = null)
    {
        $this->stmt($query, $params);
    }

    /**
     * Execute a query and return the result rows.
     *
     * @param string $query
     * @param array|null $params
     * @return array
     * @throws \PDOException
     */
    protected function queryRows(string $query, ?array $params = null): array
    {
        $stmt = $this->stmt($query, $params);
        $rows = $stmt->fetchAll($this->db::FETCH_ASSOC);
        $stmt->closeCursor();
        return $rows;
    }

    /**
     * Execute a query and return a single row.
     *
     * @param string $query
     * @param array|null $params
     * @return array
     * @throws \PDOException
     */
    protected function queryRow(string $query, ?array $params = null): array
    {
        $rows = $this->queryRows($query, $params);
        $row = reset($rows);
        if ($row !== false) {
            return $row;
        }
        return [];
    }

    /**
     * Execute a query and return the value of the first column of the first row.
     *
     * @param string $query
     * @param array|null $params
     * @return mixed
     * @throws \PDOException
     */
    protected function queryColumn(string $query, ?array $params = null) /*: mixed */
    {
        $row = $this->queryRow($query, $params);
        $value = reset($row);
        if ($value !== false) {
            return $value;
        }
        return null;
    }
}

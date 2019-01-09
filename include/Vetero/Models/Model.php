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
    protected $db;

    /**
     * Constructor
     *
     * @param Container $c
     */
    public function __construct(Container $c)
    {
        $this->container = $c;
        $this->db = $c->get('db');
    }

    /**
     * Execute a query.
     *
     * @param string $query
     * @param array|null $params
     */
    protected function query(string $query, ?array $params = null)
    {
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
    }

    /**
     * Execute a query and return the result rows.
     *
     * @param string $query
     * @param array|null $params
     * @return array
     */
    protected function queryRows(string $query, ?array $params = null): array
    {
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll($this->db::FETCH_ASSOC);
        $stmt->closeCursor();
        return $rows ?? [];
    }

    /**
     * Execute a query and return a single row.
     *
     * @param string $query
     * @param array|null $params
     * @return array
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
     */
    protected function queryColumn(string $query, ?array $params = null) /*: mixed */
    {
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $value = $stmt->fetchColumn();
        $stmt->closeCursor();
        return $value;
    }
}

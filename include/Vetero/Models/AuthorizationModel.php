<?php
declare(strict_types=1);

namespace Vetero\Models;

use Slim\Container;

/**
 * Class AuthorizationModel
 * @package Vetero\Models
 */
class AuthorizationModel extends Model
{
    /**
     * Return whether the given token grants access to a resource.
     *
     * @param string $token
     * @param string $resource
     * @param bool
     */
    public function canAccess(string $token, string $resource): bool
    {
        $result = $this->queryColumn(
            'SELECT COUNT(token) FROM authorization
                 WHERE token = ? AND enabled = 1
                 AND (FIND_IN_SET(?, resources) > 0 OR FIND_IN_SET("*", resources) > 0)',
            [$token, $resource]
        );

        return (bool)$result;
    }
}

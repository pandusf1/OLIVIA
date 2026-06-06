<?php

namespace App\Database\Connectors;

use Illuminate\Database\PostgresConnection;

class CustomPostgresConnection extends PostgresConnection
{
    /**
     * Prepare the query bindings for execution.
     *
     * @param  array  $bindings
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $bindings = parent::prepareBindings($bindings);

        foreach ($bindings as $key => $value) {
            if (is_bool($value)) {
                $bindings[$key] = $value ? 'true' : 'false';
            }
        }

        return $bindings;
    }
}

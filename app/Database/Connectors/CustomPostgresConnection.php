<?php

namespace App\Database\Connectors;

use Illuminate\Database\PostgresConnection;

class CustomPostgresConnection extends PostgresConnection
{
    /**
     * Siapkan query binding untuk dieksekusi.
     *
     * @param  array  $bindings
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        foreach ($bindings as $key => $value) {
            if (is_bool($value)) {
                $bindings[$key] = $value ? 'true' : 'false';
            }
        }

        return parent::prepareBindings($bindings);
    }
}

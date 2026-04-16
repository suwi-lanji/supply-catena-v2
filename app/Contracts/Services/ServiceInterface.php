<?php

namespace App\Contracts\Services;

interface ServiceInterface
{
    /**
     * Begin a new database transaction.
     */
    public function beginTransaction(): void;

    /**
     * Commit the current database transaction.
     */
    public function commit(): void;

    /**
     * Rollback the current database transaction.
     */
    public function rollback(): void;

    /**
     * Execute a callback within a database transaction.
     *
     * @param callable $callback
     * @return mixed
     */
    public function transaction(callable $callback): mixed;
}

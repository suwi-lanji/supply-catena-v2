<?php

namespace App\Services;

use App\Contracts\Services\ServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class BaseService implements ServiceInterface
{
    /**
     * Begin a new database transaction.
     */
    public function beginTransaction(): void
    {
        DB::beginTransaction();
    }

    /**
     * Commit the current database transaction.
     */
    public function commit(): void
    {
        DB::commit();
    }

    /**
     * Rollback the current database transaction.
     */
    public function rollback(): void
    {
        DB::rollBack();
    }

    /**
     * Execute a callback within a database transaction.
     *
     * @param callable $callback
     * @return mixed
     * @throws Exception
     */
    public function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    /**
     * Log an action for audit purposes.
     *
     * @param string $action
     * @param array $context
     * @return void
     */
    protected function logAction(string $action, array $context = []): void
    {
        Log::info("[{static::class}] {$action}", $context);
    }

    /**
     * Log an error for debugging purposes.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error("[{static::class}] {$message}", $context);
    }
}

<?php
namespace App\Contracts;
/**
 * Interface JobDispatcherInterface
 *
 * This interface defines the contract for job dispatchers.
 * Implementations should provide a method to dispatch jobs.
 */

interface JobDispatcherInterface
{
    public function dispatch(object $job): void;
}

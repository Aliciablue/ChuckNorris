<?php 
namespace App\Services;

use App\Contracts\JobDispatcherInterface;
use Illuminate\Contracts\Queue\Queue;

class QueueDispatcher implements JobDispatcherInterface
{
    public function __construct(protected Queue $queue) {}

    public function dispatch(object $job): void
    {
        $this->queue->push($job);
    }
}
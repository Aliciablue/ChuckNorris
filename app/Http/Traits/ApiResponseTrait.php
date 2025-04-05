<?php

namespace App\Http\Traits;

use Psr\Log\LoggerInterface;
use Illuminate\Support\Facades\Log;

trait ApiResponseTrait
{
    public function __construct(protected LoggerInterface $logger) {}
    public function errorResponseJson(string $message, string $error, int $statusCode)
    {
        $this->LogError($message, $error);
        return response()->json(['error' => $message], $statusCode);
    }
    public function errorResponseFront(string $message, string $error, int $statusCode)
    {
        $this->LogError($message, $error);
        return back()->withErrors(['api' => $message])->withInput();
    }
    private function LogError(string $message, string $error)
    {
        $this->logger->error($message . ": " . $error);
    }

}

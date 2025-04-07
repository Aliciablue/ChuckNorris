<?php

namespace App\Services;

use App\Contracts\JobDispatcherInterface;
use App\Contracts\SearchRepositoryInterface;
use App\Jobs\SaveSearchJob;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;

class SearchRecordService
{
    public function __construct(
        protected SearchRepositoryInterface $searchRepository,
        protected JobDispatcherInterface $jobDispatcher,
        protected LoggerInterface $logger,
        protected Request $request
    ) {}

    public function handleSearchRecord(string $type, ?string $query, array $allResults, ?string $email): void
    {
      
        if (!session()->has('has_made_search')) {

            try {
                $this->logger->debug('All Results:', $allResults); // Add this line
                // Create a new instance of the SaveSearchJob
                $job = new SaveSearchJob($type, $query, $allResults, $email);

                // Dispatch the job using the injected JobDispatcher
                $this->jobDispatcher->dispatch($job);

                // Mark that the job has been initiated for this request
                $this->request->attributes->set('current_search_initiated', true);
                $this->logger->info('Search job dispatched successfully.', [
                    'type' => $type,
                    'query' => $query,
                    'email' => $email,
                ]);
                // Mark it so next time it's not "first"
                session(['has_made_search' => true]);
            } catch (\Throwable $e) {
                // Log the error
                $this->logger->error('Error dispatching SaveSearchJob:', [
                    'message' => $e->getMessage(),
                    'type' => $type,
                    'query' => $query,
                    'email' => $email,
                ]);

                // Implement retry mechanisms or other error handling strategies here
                // Notify an administrator if job dispatch consistently fails.
            } catch (\Exception $e) {
                // Log the error
                $this->logger->error('Error dispatching SaveSearchJob:', [
                    'message' => $e->getMessage(),
                    'type' => $type,
                    'query' => $query,
                    'email' => $email,
                ]);

                // Implement retry mechanisms or other error handling strategies here
                // Notify an administrator if job dispatch consistently fails.
            }
        }
    }
}

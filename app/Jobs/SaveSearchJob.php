<?php

namespace App\Jobs;

use App\Models\Search;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Contracts\SearchRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SaveSearchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
//protected SearchRepositoryInterface $searchRepository,
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( protected string $type, protected ?string $query, protected array $results, protected ?string $email) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SearchRepositoryInterface $searchRepository)
    {
        $searchRepository->save($this->type, $this->query, $this->results, $this->email);
    }
}

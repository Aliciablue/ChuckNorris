<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Mail\SearchResultsMail; // Assuming you'll create this Mailable

class SendSearchResultsEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param array $results
     * @param string $searchType
     * @param string $searchTerm
     * @param string $email
     * @param string $allResultsUrl
     * @param string $locale
     */
    public function __construct(protected array $results, protected string $searchType, protected string $searchTerm, protected string $email, protected string $allResultsUrl, protected string $locale)
    {
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        App::setLocale($this->locale);
        Mail::to($this->email)->send(new SearchResultsMail(
            $this->results,
            $this->searchType,
            $this->searchTerm,
            $this->allResultsUrl
        ));
    }
}

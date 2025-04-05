<?php

namespace Tests\Unit\Jobs;

use Mockery;
use Tests\TestCase;
use App\Mail\SearchResultsMail;
use App\Services\LocaleService;
use App\Jobs\SendSearchResultsEmailJob;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\WithFaker;

class SendSearchResultsEmailTest extends TestCase
{
    use WithFaker;

    #[Test]
    public function test_it_stores_correct_properties()
    {
        $results = [['title' => 'Test Result']];
        $searchType = 'keyword';
        $searchTerm = 'test';
        $email = 'test@example.com';
        $url = 'https://example.com/results';
        $locale = 'en';
        $total = 1;

        $job = new SendSearchResultsEmailJob($results, $searchType, $searchTerm, $email, $url, $locale, $total);

        $this->assertEquals($results, $job->getResults());
        $this->assertEquals($searchType, $job->getSearchType());
        $this->assertEquals($searchTerm, $job->getSearchTerm());
        $this->assertEquals($email, $job->getEmail());
        $this->assertEquals($url, $job->getAllResultsUrl());
        $this->assertEquals($locale, $job->getLocale());
        $this->assertEquals($total, $job->getTotal());
    }

    #[Test]
    public function test_it_sets_locale_and_sends_email(): void
    {
        Mail::fake();
    
        $mockLocaleService = Mockery::mock(LocaleService::class);
        $mockLocaleService->shouldReceive('setLocale')->once()->with('en');
    
        $this->app->instance(LocaleService::class, $mockLocaleService);
    
        $job = new SendSearchResultsEmailJob(
            results: [['title' => 'Sample']],
            searchType: 'keyword',
            searchTerm: 'Laravel',
            email: 'test@example.com',
            allResultsUrl: 'https://example.com/all',
            locale: 'en',
            total: 1
        );
    
        $job->handle(app('mailer'), $mockLocaleService);
    
        Mail::assertSent(SearchResultsMail::class, fn($mail) => $mail->hasTo('test@example.com'));
    }

    #[Test]
    public function it_dispatches_correctly()
    {
        Queue::fake(); // Prevent actual queue processing

        SendSearchResultsEmailJob::dispatch(
            results: [['title' => 'Test Result']],
            searchType: 'keyword',
            searchTerm: 'Laravel',
            email: 'test@example.com',
            allResultsUrl: 'https://example.com/results',
            locale: 'en',
            total: 10
        );

        Queue::assertPushed(SendSearchResultsEmailJob::class);
    }
}

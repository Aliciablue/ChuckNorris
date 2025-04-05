<?php

namespace Tests\Feature\Mail;

use Tests\TestCase;
use App\Mail\SearchResultsMail;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;

class SearchResultsMailFeatureTest extends TestCase
{
    #[Test]
    public function it_sends_search_results_mail_with_correct_data()
    {
        Mail::fake();

        $results = [
            ['value' => 'data 1'],
            ['value' => 'data 2']
        ];

        $searchType = 'random';
        $searchTerm = '';
        $url = 'https://example.com/results';
        $recipient = 'test@example.com';

        // Actually send the mail
        Mail::to($recipient)->send(new SearchResultsMail(
            $results,
            $searchType,
            $searchTerm,
            $url
        ));

        Mail::assertSent(SearchResultsMail::class, function ($mail) use ($results, $searchType, $searchTerm, $url, $recipient) {
            return $mail->hasTo($recipient) &&
                $mail->results === $results &&
                $mail->searchType === $searchType &&
                $mail->searchTerm === $searchTerm &&
                $mail->allResultsUrl === $url;
        });

        // Check the email content
        Mail::assertSent(SearchResultsMail::class, function ($mail) {
            $rendered = $mail->render();

            $this->assertStringContainsString('data 1', $rendered);
            $this->assertStringContainsString('data 2', $rendered);

            return true;
        });
    }
}

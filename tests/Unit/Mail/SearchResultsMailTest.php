<?php

namespace Tests\Unit\Mail;

use Tests\TestCase;
use App\Mail\SearchResultsMail;
use PHPUnit\Framework\Attributes\Test;

class SearchResultsMailTest extends TestCase
{
    #[Test]
    public function it_has_correct_subject()
    {
        $mail = new SearchResultsMail([], 'type', 'term', 'https://example.com');
        $envelope = $mail->envelope();

        $this->assertEquals(__('results.search_results'), $envelope->subject);
    }

    #[Test]
    public function it_uses_the_correct_view()
    {
        $mail = new SearchResultsMail([], 'type', 'term', 'https://example.com');
        $content = $mail->content();

        $this->assertEquals('emails.search_results', $content->view);
    }

    #[Test]
    public function it_passes_data_to_the_view()
    {
        $results = [['Chuck' => 'Norris']];
        $searchType = 'words';
        $searchTerm = 'chuck';
        $url = 'https://example.com';

        $mail = new SearchResultsMail($results, $searchType, $searchTerm, $url);
        $content = $mail->content();

        $this->assertEquals($results, $content->with['results']);
        $this->assertEquals($searchType, $content->with['searchType']);
        $this->assertEquals($searchTerm, $content->with['searchTerm']);
        $this->assertEquals($url, $content->with['allResultsUrl']);
    }

    #[Test]
    public function it_sets_constructor_properties()
    {
        $mail = new SearchResultsMail(['data'], 'type', 'term', 'url');

        $this->assertEquals(['data'], $mail->results);
        $this->assertEquals('type', $mail->searchType);
        $this->assertEquals('term', $mail->searchTerm);
        $this->assertEquals('url', $mail->allResultsUrl);
    }
}

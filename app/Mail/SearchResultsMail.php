<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SearchResultsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $results;
    public $searchType;
    public $searchTerm;
    public $allResultsUrl;

    /**
     * Create a new message instance.
     *
     * @param array $results
     * @param string $searchType
     * @param string $searchTerm
     */
    public function __construct(array $results, string $searchType, string $searchTerm, string $allResultsUrl)
    {
        $this->results = $results;
        $this->searchType = $searchType;
        $this->searchTerm = $searchTerm;
        $this->allResultsUrl = $allResultsUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('results.search_results'), 
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.search_results', 
            with: [
                'results' => $this->results,
                'searchType' => $this->searchType,
                'searchTerm' => $this->searchTerm,
               'allResultsUrl' => $this->allResultsUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
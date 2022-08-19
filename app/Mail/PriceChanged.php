<?php

namespace App\Mail;

use App\Models\ApplicationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PriceChanged extends Mailable
{
    use Queueable, SerializesModels;

    protected $applicationService;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ApplicationService $applicationService)
    {
        $this->applicationService = $applicationService;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $applicationService = $this->applicationService;
        return $this->view('emails.applications.serviceprice', compact('applicationService'))->subject('Цена на услугу отредактирована');
    }
}

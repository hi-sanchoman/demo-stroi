<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentCompleted extends Mailable
{
    use Queueable, SerializesModels;

    protected $payment;
    protected $paid;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Payment $payment, $paid)
    {
        $this->payment = $payment;
        $this->paid = $paid;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $payment = $this->payment;
        $paid = $this->paid;

        return $this->view('emails.payments.completed', compact('payment', 'paid'))->subject('Оплата произведена');
    }
}

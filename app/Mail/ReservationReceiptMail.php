<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use SebastianBergmann\CodeUnit\FunctionUnit;

class ReservationReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct($reservation)
    {
        $this->reservation = $reservation;
        //
    }

    public $reservation;
    public function build(){
        return $this->from(env('MAIL_FROM_ADDRESS'), 'Yoru Hotel')
        ->subject('Reservation Receipt')
        ->view('reservation.emailReceipt')
        ->with(['reservation' => $this->reservation]);
    
    }

    /**
     * Get the message envelope.
     */
   

    /**
     * Get the message content definition.
     */
   
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

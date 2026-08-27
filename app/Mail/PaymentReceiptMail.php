<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $recipientName;
    public string $bookingType;
    public string $description;
    public string $referenceId;
    public float $amountPaid;
    public string $paymentMethod;
    public string $datePaid;
    public string $status;

    public function __construct(
        string $recipientName,
        string $bookingType,
        string $description,
        string $referenceId,
        float $amountPaid,
        string $paymentMethod,
        string $datePaid,
        string $status
    ) {
        $this->recipientName  = $recipientName;
        $this->bookingType    = $bookingType;
        $this->description    = $description;
        $this->referenceId    = $referenceId;
        $this->amountPaid     = $amountPaid;
        $this->paymentMethod  = $paymentMethod;
        $this->datePaid       = $datePaid;
        $this->status         = $status;
    }

    public function build()
    {
        return $this->subject("Payment Receipt – Rem's Transport")
                    ->view('emails.payment_receipt');
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $bookingType,      // Van Rental / Tour Package / Joiner Trip
        public string $bookingRef,       // e.g. #51
        public string $description,      // destination or package name
        public ?string $referenceId,     // PayMongo payment id; null for cash
        public float $amountPaid,        // this payment only
        public string $paymentLabel,     // Downpayment / Full Payment / Installment Payment / Balance Payment
        public string $paymentMethod,
        public string $datePaid,
        public float $total,
        public float $remainingBalance,
    ) {
    }

    public function build()
    {
        return $this->subject("Payment Receipt {$this->bookingRef} – Rem's Transport")
                    ->view('emails.payment_receipt');
    }
}

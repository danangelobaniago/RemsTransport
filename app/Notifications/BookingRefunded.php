<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BookingRefunded extends Notification
{
    use Queueable;

    public function __construct(
        protected int $bookingId,
        protected string $destination,
        protected string $bookingType,
        protected float $amount,
        protected string $proofPath, // photo of the refund receipt on the public disk
    ) {
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message'    => "Your refund of ₱" . number_format($this->amount, 2) . " for {$this->bookingType} #{$this->bookingId} to {$this->destination} has been sent. The refund receipt is attached to your booking.",
            'booking_id' => $this->bookingId,
            'amount'     => $this->amount,
            'type'       => 'booking_refunded',
        ];
    }

    public function toMail($notifiable)
    {
        $fullName = trim(($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? ''));

        $mail = (new MailMessage)
            ->subject("Refund Sent 💸 — Rem's Transport")
            ->greeting('Hello ' . ($fullName ?: 'Customer') . ',')
            ->line("Your refund for **{$this->bookingType} #{$this->bookingId}** to **{$this->destination}** has been sent.")
            ->line('**Amount refunded:** ₱' . number_format($this->amount, 2))
            ->line('The photo of the refund receipt is attached to this email. Please check your account. If you have not received it within a few days, contact us at remstransport1@gmail.com.')
            ->action('View My Bookings', url('/my-bookings'))
            ->line("Thank you for your patience, and we hope to serve you again!");

        // Attach the receipt photo itself; read through the disk so it works on S3 too.
        try {
            $disk = Storage::disk('public');
            $ext  = pathinfo($this->proofPath, PATHINFO_EXTENSION) ?: 'jpg';
            $mail->attachData($disk->get($this->proofPath), "Refund-Receipt-{$this->bookingId}.{$ext}", [
                'mime' => $disk->mimeType($this->proofPath) ?: 'image/jpeg',
            ]);
        } catch (\Throwable $e) {
            Log::error("Refund receipt attachment failed for booking #{$this->bookingId}: " . $e->getMessage());
        }

        return $mail;
    }
}

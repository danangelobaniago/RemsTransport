<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

/**
 * Builds the downloadable official receipt (one A4 page) shared by van
 * rentals, tour packages and joiner trips. Controllers pass already-shaped
 * data so this stays the same for every booking type.
 */
class ReceiptPdf
{
    /**
     * @param array{receiptNo:string, bookingType:string, customer:string, email:?string,
     *              details:array<string,?string>, passengers:iterable, payments:iterable,
     *              total:float, totalPaid:float, balance:float} $data
     */
    public static function download(array $data)
    {
        $data += ['closedStatus' => null, 'closedReason' => null, 'refund' => null]; // 'rejected' / 'cancelled'
        $data['details']    = array_filter($data['details'], fn ($v) => $v !== null && trim((string) $v) !== '');
        $data['passengers'] = collect($data['passengers'])->filter()->values();
        $data['issuedAt']   = Carbon::now(LocalDate::TIMEZONE)->format('F d, Y h:i A');

        return Pdf::loadView('pdf.receipt', $data)
            ->setPaper('a4')
            // Embed only the glyphs used, or the DejaVu font alone makes the file ~1 MB
            ->setOption('isFontSubsettingEnabled', true)
            ->download("Rems-Transport-Receipt-{$data['receiptNo']}.pdf");
    }
}

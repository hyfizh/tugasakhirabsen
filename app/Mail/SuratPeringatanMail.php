<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Mahasiswa;

class SuratPeringatanMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mahasiswa;
    public $spLevel;
    public $spTitle;
    public $totalAlpaHours;
    public $compensationPenalty;
    public $pdfContent;

    public function __construct(Mahasiswa $mahasiswa, int $spLevel, string $spTitle, int $totalAlpaHours, int $compensationPenalty, $pdfContent = null)
    {
        $this->mahasiswa = $mahasiswa;
        $this->spLevel = $spLevel;
        $this->spTitle = $spTitle;
        $this->totalAlpaHours = $totalAlpaHours;
        $this->compensationPenalty = $compensationPenalty;
        $this->pdfContent = $pdfContent;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[PENTING] {$this->spTitle} - Politeknik Negeri Padang ({$this->mahasiswa->nama_lengkap})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.surat_peringatan',
        );
    }

    public function attachments(): array
    {
        if ($this->pdfContent) {
            $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $this->mahasiswa->nama_lengkap);
            return [
                Attachment::fromData(fn () => $this->pdfContent, "Surat_Peringatan_{$cleanName}_{$this->mahasiswa->nim}.pdf")
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}

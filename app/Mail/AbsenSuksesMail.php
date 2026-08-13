<?php

namespace App\Mail;

use App\Models\Mahasiswa;
use App\Models\Absensi;
use App\Models\Jadwal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AbsenSuksesMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mahasiswa;
    public $absensi;
    public $jadwal;

    /**
     * Create a new message instance.
     */
    public function __construct(Mahasiswa $mahasiswa, Absensi $absensi, ?Jadwal $jadwal = null)
    {
        $this->mahasiswa = $mahasiswa;
        $this->absensi   = $absensi;
        $this->jadwal    = $jadwal;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Notifikasi Presensi Berhasil - Absen TI Politeknik Negeri Padang',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.absen_sukses',
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

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyMail extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return \App\Mail\NotifyMail
     */
    public function build()
    {
        switch ($this->data['state']) {
            case 'Asignado':
                return $this->subject('Cita Asignada!')->view('emails.appointment', ['body' => $this->data]);
            case 'Agendado':
                return $this->subject('Cita Asignada!')->view('emails.appointment', ['body' => $this->data]);
            case 'Cancelado':
                return $this->subject('Cita Cancelada!')->view('emails.cancelation', ['body' => $this->data]);
            case 'Asistio':
                return $this->subject('Gracias!')->view('emails.cancelation', ['body' => $this->data]);
            default:
                return $this->subject('Cita Asignada!')->view('emails.appointment', ['body' => $this->data]);
        }
    }
}

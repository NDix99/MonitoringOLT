<?php

namespace App\Notifications;

use App\Models\Onu;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OnuAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Onu $onu,
        public string $alertType,
        public string $message,
        public ?float $value = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match($this->alertType) {
            'low_rx_power' => 'ONU Low RX Power Alert',
            'onu_offline' => 'ONU Offline Alert',
            'onu_dying_gasp' => 'ONU DyingGasp Alert',
            default => 'ONU Alert'
        };

        return (new MailMessage)
            ->subject($subject)
            ->line("OLT: {$this->onu->olt->name}")
            ->line("ONU Serial: {$this->onu->serial_number}")
            ->line("Status: {$this->onu->status_badge}")
            ->line("RX Power: {$this->onu->rx_power} dBm")
            ->line("Alert: {$this->message}")
            ->action('View Dashboard', url('/dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'onu_id' => $this->onu->id,
            'olt_name' => $this->onu->olt->name,
            'onu_serial' => $this->onu->serial_number,
            'alert_type' => $this->alertType,
            'message' => $this->message,
            'value' => $this->value,
            'status' => $this->onu->status_badge,
            'rx_power' => $this->onu->rx_power,
        ];
    }
}

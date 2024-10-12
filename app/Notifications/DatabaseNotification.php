<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class DatabaseNotification extends Notification
{
    public $tenant_id, $title, $body, $url;

    public function __construct($title, $body, $url, $tenant_id)
    {
        $this->title = $title;
        $this->body = $body;
        $this->url = $url;
        $this->tenant_id = $tenant_id;
    }

    public function via()
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            "title" => $this->title,
            "body" => $this->body,
            "url" => $this->url,
            "tenant_id" => $this->tenant_id
        ];
    }
}

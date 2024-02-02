<?php
namespace App\Http\Services;

use App\Models\Notification;

class NotificationService
{
    protected $userId;
    public function __construct($userId)
    {
        $this->userId = $userId;
    }
    public function send($text, $route)
    {
        return Notification::create([
            'user_id' => $this->userId,
            'text' => $text,
            'route' => $route,
        ]);
    }

    public function seen($notificationId)
    {
        return Notification::where('id', $notificationId)->update(['is_seen' => true]);
    }

    public function seenAll()
    {
        return Notification::where('user_id', $this->userId)->update(['is_seen' => true]);
    }

    public function list()
    {
        return Notification::where(['user_id' => $this->userId, 'is_seen' => false])->get();
    }
}

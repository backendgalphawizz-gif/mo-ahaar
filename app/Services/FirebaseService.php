<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseService
{
    private \Kreait\Firebase\Contract\Messaging $messaging;

    public function __construct()
    {
        $credentialsPath = base_path('swastik-foods-firebase-adminsdk-fbsvc-e1f77e54bc.json');

        $factory = (new Factory)->withServiceAccount($credentialsPath);

        $this->messaging = $factory->createMessaging();
    }

    /**
     * Send a push notification to a single FCM token.
     */
    public function sendToToken(string $fcmToken, string $title, string $body): void
    {
        try {
            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification(Notification::create($title, $body));

            $this->messaging->send($message);
        } catch (\Throwable $e) {
            Log::error('Firebase sendToToken failed', [
                'token' => substr($fcmToken, 0, 20) . '...',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send a push notification to multiple FCM tokens (batch multicast).
     *
     * @param string[] $fcmTokens
     */
    public function sendToTokens(array $fcmTokens, string $title, string $body): void
    {
        $fcmTokens = array_values(array_filter($fcmTokens));

        if (empty($fcmTokens)) {
            return;
        }

        try {
            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body));

            // kreait/firebase-php supports up to 500 tokens per multicast call.
            foreach (array_chunk($fcmTokens, 500) as $chunk) {
                $this->messaging->sendMulticast($message, $chunk);
            }
        } catch (\Throwable $e) {
            Log::error('Firebase sendToTokens failed', [
                'token_count' => count($fcmTokens),
                'error'       => $e->getMessage(),
            ]);
        }
    }
}

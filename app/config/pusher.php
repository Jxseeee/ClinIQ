<?php

use Pusher\Pusher;

require_once __DIR__ . '/database.php';

function pusherClient(): ?Pusher
{
    $key     = $_ENV['PUSHER_APP_KEY'] ?? '';
    $secret  = $_ENV['PUSHER_APP_SECRET'] ?? '';
    $appId   = $_ENV['PUSHER_APP_ID'] ?? '';
    $cluster = $_ENV['PUSHER_APP_CLUSTER'] ?? 'ap1';

    if ($key === '' || $secret === '' || $appId === '') {
        return null;
    }

    return new Pusher($key, $secret, $appId, [
        'cluster' => $cluster,
        'useTLS'  => true,
    ]);
}

function pusherPublicConfig(): array
{
    return [
        'key'     => $_ENV['PUSHER_APP_KEY'] ?? '',
        'cluster' => $_ENV['PUSHER_APP_CLUSTER'] ?? 'ap1',
    ];
}

function pusherConfigured(): bool
{
    return pusherClient() !== null;
}

function chatChannelName(int $studentId): string
{
    return 'private-chat-' . $studentId;
}

function adminInboxChannel(): string
{
    return 'private-admin-inbox';
}

function canAccessChatChannel(string $channel, string $role, int $userId): bool
{
    if ($channel === adminInboxChannel()) {
        return $role === 'admin';
    }

    if (preg_match('/^private-chat-(\d+)$/', $channel, $m)) {
        $studentId = (int) $m[1];
        return $role === 'admin' || ($role === 'student' && $userId === $studentId);
    }

    return false;
}

function broadcastChatMessage(int $studentId, array $payload): void
{
    $pusher = pusherClient();
    if (!$pusher) {
        return;
    }

    $pusher->trigger(chatChannelName($studentId), 'new-message', $payload);

    if ($payload['sender_role'] === 'student') {
        $pusher->trigger(adminInboxChannel(), 'inbox-update', [
            'student_id' => $studentId,
        ]);
    }
}

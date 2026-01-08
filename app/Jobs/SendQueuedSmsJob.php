<?php

namespace App\Jobs;

use App\Models\NotificationQueue;
use App\Services\SmsGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendQueuedSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $queueId;

    public function __construct(int $queueId)
    {
        $this->queueId = $queueId;
    }

    public function handle(): void
    {
        $queue = NotificationQueue::find($this->queueId);

        if (! $queue || $queue->status !== 'pending') {
            return;
        }

        $queue->attempts++;
        $queue->last_attempt_at = now();
        $queue->save();

        $result = SmsGateway::sendWithResponse($queue->phone, $queue->message);

        $queue->gateway_status_code = $result['status_code'];
        $queue->gateway_response    = $result['raw'] ?? null;

        if ($result['success']) {
            $queue->status  = 'sent';
            $queue->sent_at = now();
        } else {
            $queue->status        = 'failed';
            $queue->error_message = is_string($result['body'])
                ? $result['body']
                : json_encode($result['body']);
        }

        $queue->save();
    }
}

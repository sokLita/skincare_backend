<?php

namespace App\Console\Commands;

use App\Services\TelegramBotService;
use Illuminate\Console\Command;

class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {url? : The webhook URL to set}';
    protected $description = 'Set or get the Telegram bot webhook URL';

    public function handle(TelegramBotService $botService): void
    {
        $url = $this->argument('url');

        if ($url) {
            $this->info('Setting Telegram webhook...');
            $result = $botService->setWebhook($url);

            if (isset($result['ok']) && $result['ok']) {
                $this->info('✅ Webhook set successfully!');
                $this->info('URL: ' . ($result['result']['url'] ?? $url));
            } else {
                $this->error('❌ Failed to set webhook.');
                $this->error(json_encode($result));
            }
        } else {
            $this->info('Getting current webhook info...');
            $info = $botService->getWebhookInfo();

            if (isset($info['ok']) && $info['ok']) {
                $this->info('Current webhook URL: ' . ($info['result']['url'] ?? 'Not set'));
                $this->info('Has custom certificate: ' . ($info['result']['has_custom_certificate'] ? 'Yes' : 'No'));
                $this->info('Pending update count: ' . ($info['result']['pending_update_count'] ?? 0));
                $this->info('Last error date: ' . ($info['result']['last_error_date'] ?? 'None'));
                $this->info('Last error message: ' . ($info['result']['last_error_message'] ?? 'None'));
            } else {
                $this->error('❌ Failed to get webhook info.');
                $this->error(json_encode($info));
            }
        }
    }
}
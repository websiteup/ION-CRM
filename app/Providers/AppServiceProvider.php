<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramChannel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Telegram notification channel - Trebuie înregistrat în register(), nu în boot()
        // NU accesăm baza de date aici, deoarece conexiunea nu este încă disponibilă
        $this->app->afterResolving(ChannelManager::class, function ($manager) {
            if (class_exists('\NotificationChannels\Telegram\TelegramChannel')) {
                $manager->extend('telegram', function ($app) {
                    // Folosim container-ul Laravel pentru a rezolva automat dependențele (Dispatcher)
                    return $app->make(TelegramChannel::class);
                });
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure Telegram bot token from settings - Aici baza de date este disponibilă
        try {
            $settings = Setting::first();
            if ($settings) {
                // Configure Telegram - Trebuie configurat înainte de utilizare
                if ($settings->telegram_bot_token) {
                    // Folosim ambele metode pentru a ne asigura că token-ul este setat
                    config(['services.telegram.bot_token' => $settings->telegram_bot_token]);
                    Config::set('services.telegram.bot_token', $settings->telegram_bot_token);
                }
                
                // Configure Email SMTP from settings
                if ($settings->smtp_host) {
                    Config::set('mail.default', 'smtp');
                    Config::set('mail.mailers.smtp.host', $settings->smtp_host);
                    Config::set('mail.mailers.smtp.port', $settings->smtp_port ?? 587);
                    Config::set('mail.mailers.smtp.encryption', $settings->smtp_encryption ?? 'tls');
                    Config::set('mail.mailers.smtp.username', $settings->smtp_username);
                    Config::set('mail.mailers.smtp.password', $settings->smtp_password);
                    
                    if ($settings->smtp_from_email) {
                        Config::set('mail.from.address', $settings->smtp_from_email);
                    }
                    if ($settings->smtp_from_name) {
                        Config::set('mail.from.name', $settings->smtp_from_name);
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignorăm erorile de baza de date în boot() dacă tabelul nu există încă
        }
    }
}

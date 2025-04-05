<?php

namespace App\Providers;

use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Listeners\SendOrderConfirmationEmail;
use App\Listeners\SendOrderStatusEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        OrderPlaced::class => [
            SendOrderConfirmationEmail::class,
        ],
        OrderStatusChanged::class => [
            SendOrderConfirmationEmail::class,
            SendOrderStatusEmail::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Add event listeners for debugging
        Event::listen(OrderStatusChanged::class, function ($event) {
            Log::info('OrderStatusChanged event received in EventServiceProvider', [
                'order_id' => $event->order->id,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
                'event_class' => get_class($event)
            ]);
        });

        Event::listen('*', function ($eventName, array $data) {
            if (str_contains($eventName, 'Order')) {
                Log::info('Order-related event fired', [
                    'event' => $eventName,
                    'data' => json_encode($data, JSON_PRETTY_PRINT)
                ]);
            }
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
<?php

namespace App\Schedule;

use Zenstruck\ScheduleBundle\Schedule;
use Zenstruck\ScheduleBundle\Schedule\ScheduleBuilder;

class AppScheduleBuilder implements ScheduleBuilder
{
    public function buildSchedule(Schedule $schedule): void
    {
        $schedule->onSingleServer();

        $schedule
            ->addCommand('app:disable-expired-coupons')
            ->description('Automatically disable expired coupons.')
            ->withoutOverlapping(true)
            ->twiceDaily();

        $schedule
            ->addCommand('sylius:cancel-unpaid-orders')
            ->description('Removes order that have been unpaid for a configured period. Configuration parameter - sylius_order.order_expiration_period.')
            ->withoutOverlapping(true)
            ->daysOfWeek('1,3,5,7');

        $schedule
            ->addCommand('sylius:remove-expired-carts ')
            ->description('Removes carts that have been idle for a period set in `sylius_order.expiration.cart` configuration key.')
            ->withoutOverlapping(true)
            ->daysOfWeek('1,3,5,7');
    }
}

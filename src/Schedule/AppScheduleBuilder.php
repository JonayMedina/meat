<?php

namespace App\Schedule;

use Zenstruck\ScheduleBundle\Schedule;
use Zenstruck\ScheduleBundle\Schedule\ScheduleBuilder;

class AppScheduleBuilder implements ScheduleBuilder
{
    public function buildSchedule(Schedule $schedule): void
    {
        $schedule
            ->environments('prod')
            ->addCommand('messenger:consume push_notification')
            ->description('Process push notifications.')
            ->everyMinute();


        $schedule
            ->environments('prod')
            ->addCommand('app:disable-expired-coupons')
            ->description('Automatically disable expired coupons.')
            ->twiceDaily();

        $schedule
            ->environments('prod')
            ->addCommand('sylius:cancel-unpaid-orders')
            ->description('Removes order that have been unpaid for a configured period. Configuration parameter - sylius_order.order_expiration_period.')
            ->daysOfWeek('1,3,5,7');

        $schedule
            ->environments('prod')
            ->addCommand('sylius:remove-expired-carts ')
            ->description('Removes carts that have been idle for a period set in `sylius_order.expiration.cart` configuration key.')
            ->daysOfWeek('1,3,5,7');
    }
}

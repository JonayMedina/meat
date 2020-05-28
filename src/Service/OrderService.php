<?php

namespace App\Service;

use App\Entity\AboutStore;
use App\Entity\Holiday;
use App\Model\APIResponse;
use App\Repository\AboutStoreRepository;
use App\Repository\HolidayRepository;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderService
{
    /**
     * @var HolidayRepository
     */
    private $holidayRepository;

    /**
     * @var AboutStoreRepository
     */
    private $aboutStoreRepository;

    /**
     * OrderService constructor.
     * @param HolidayRepository $holidayRepository
     * @param AboutStoreRepository $aboutStoreRepository
     */
    public function __construct(HolidayRepository $holidayRepository, AboutStoreRepository $aboutStoreRepository)
    {
        $this->holidayRepository = $holidayRepository;
        $this->aboutStoreRepository = $aboutStoreRepository;
    }

    /**
     * Find next available date for order.
     * @param $preferredDeliveryDate
     * @param $scheduledDeliveryDate
     * @return Carbon
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getNextAvailableDay($preferredDeliveryDate, $scheduledDeliveryDate)
    {
        $today = Carbon::now();
        $aboutStore = $this->aboutStoreRepository->findLatest();

        if (!$aboutStore instanceof AboutStore) {
            throw new NotFoundHttpException('Settings are missing.');
        }

        try {
            $scheduledDeliveryDate = Carbon::parse($scheduledDeliveryDate);
        } catch (InvalidFormatException $exception) {
            throw new BadRequestHttpException('Settings are missing.');
        }

        if ($scheduledDeliveryDate->lessThanOrEqualTo($today)) {
            throw new BadRequestHttpException('La fecha elegida para el envío no es valida, seleccione una fecha futura.');
        }

        $daysDifference = $this->diffInDays($today, $scheduledDeliveryDate);

        if ((bool)$aboutStore->getDaysToChooseInAdvanceToPurchase() && ($daysDifference > $aboutStore->getDaysToChooseInAdvanceToPurchase())) {
            throw new BadRequestHttpException('El día de entrega seleccionado sobrepasa el rango permitido.');
        }

        $nextAvailableDay = $this->findValidDeliverDate($scheduledDeliveryDate);

        return $this->setTimeToAvailableDay($nextAvailableDay, $preferredDeliveryDate);
    }

    /**
     * @param Carbon $date
     * @return bool
     */
    private function isHoliday(Carbon $date): bool
    {
        $holiday = $this->holidayRepository
            ->createQueryBuilder('holiday')
            ->andWhere('holiday.date = :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->getOneOrNullResult();

        return ($holiday instanceof Holiday);
    }

    /**
     * @param Carbon $start
     * @param Carbon $end
     * @return mixed
     */
    private function diffInDays(Carbon $start, Carbon $end)
    {
        return $start->diffInDays($end);
    }

    /**
     * @param Carbon $scheduledDeliveryDate
     * @return Carbon
     */
    private function findValidDeliverDate(Carbon $scheduledDeliveryDate, $start = 'now'): Carbon
    {
        $isAvailable = false;
        $today = Carbon::parse($start);

        while (!$isAvailable) {
            $todayAtTwelve = $today->copy()->setHours(12)->setMinutes(00)->setSeconds(00);
            if ($this->isHoliday($today) || $today->isAfter($todayAtTwelve) || $today->isSunday()) {
                $this->addOneDay($today);
            } else {
                $isAvailable = true;
            }
        }

        $nextAvailableDay = $today;

        $scheduledDeliveryDateAtTwelve = $scheduledDeliveryDate->copy()->setHours(12)->setMinutes(00)->setSeconds(00);
        if ($scheduledDeliveryDate->isAfter($scheduledDeliveryDateAtTwelve)) {
            return $this->findValidDeliverDate($nextAvailableDay, $scheduledDeliveryDate->format('Y-m-d H:i:s'));
        }

        $scheduledDeliveryDate = ($scheduledDeliveryDate->isAfter($nextAvailableDay)) ? $scheduledDeliveryDate : $nextAvailableDay;

        return $scheduledDeliveryDate;
    }

    /**
     * Reset time and add one day.
     * @param Carbon $date
     * @return Carbon
     */
    private function addOneDay(Carbon $date): Carbon
    {
        /** Reset datetime to 00:00:00 */
        return $date
            ->setHours(0)
            ->setMinutes(00)
            ->setSeconds(00)
            ->addDays(1);
    }

    /**
     * @param Carbon $nextAvailableDay
     * @param $preferredDeliveryDate
     * @return Carbon
     */
    private function setTimeToAvailableDay(Carbon $nextAvailableDay, $preferredDeliveryDate): Carbon
    {
        $aboutStore = $this->aboutStoreRepository->findLatest();

        if (!$aboutStore instanceof AboutStore) {
            throw new NotFoundHttpException('Settings are missing.');
        }

        $hours = [];
        $deliveryHours = $aboutStore->getDeliveryHours();

        foreach ($deliveryHours as $deliveryHour) {
            $label = $deliveryHour['name'];
            $hours[$label] = $deliveryHour;
        }

        $timeRange = $hours[$preferredDeliveryDate] ?? null;

        if (!empty($timeRange)) {
            $end = $timeRange['end'];
            $chunks = explode(':', $end);

            return $nextAvailableDay
                ->setHours($chunks[0])
                ->setMinutes($chunks[1])
                ->setSeconds(00);
        }

        return $nextAvailableDay
            ->setHours(17)
            ->setMinutes(00)
            ->setSeconds(00);
    }
}

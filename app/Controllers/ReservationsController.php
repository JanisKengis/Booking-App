<?php

namespace App\Controllers;


use App\Database;
use App\Models\Apartment;
use App\Redirect;
use App\View;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use PDO;

class ReservationsController
{

    public function show(array $vars): View
    {
        $apartmentsQuery = Database::connect()->prepare('SELECT * FROM apartments WHERE id=?');
        $apartmentsQuery->execute([$vars['id']]);
        $apartmentQuery = $apartmentsQuery->fetchAll(PDO::FETCH_ASSOC);
        $app = $apartmentQuery[0];

        $apartment = new Apartment($app['name'], $app['address'], $app['description'],
            $app['available_from'], $app['available_till'], $app['price'], $app['id'], $_SESSION['id']);

        $reservationQuery = Database::connect()->prepare('SELECT * FROM reservations WHERE apartment_id=?');
        $reservationQuery->execute([$vars['id']]);
        $reservations = $reservationQuery->fetchAll(PDO::FETCH_ASSOC);

        $reservationDates = [];
        foreach ($reservations as $reservation) {
            $reservationDates[] = [$reservation['reserved_from'], $reservation['reserved_till']];
        }
        $reservationPeriod = [];
        foreach ($reservationDates as $date) {
            [$startDate, $endDate] = $date;
            $period = CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $value) {
                $reservationPeriod[] = $value->format('Y-m-d');
            }
        }

        return new View('Reservations/show', [
            'name' => $_SESSION['name'],
            'period' => $reservationPeriod,
            'apartment' => $apartment,
        ]);
    }

    public function reserve(array $vars): Redirect
    {
        $apartmentsQuery = Database::connect()->prepare('SELECT * FROM apartments WHERE id=?');
        $apartmentsQuery->execute([$vars['id']]);
        $apartmentQuery = $apartmentsQuery->fetchAll(PDO::FETCH_ASSOC);
        $app = $apartmentQuery[0];

        $requiredFrom = (Carbon::parse($_POST['reserve_from']))->timestamp;
        $requiredUntil = (Carbon::parse($_POST['reserve_till']))->timestamp;
        $availableFrom = (Carbon::parse($app['available_from']))->timestamp;
        $availableUntil = (Carbon::parse($app['available_till']))->timestamp;

        $newDate = Carbon::create($_POST['reserve_till'])->addDay();

        $dates = [];
        $period = CarbonPeriod::create($_POST['reserve_from'], $_POST['reserve_till']);
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        $totalPrice = (count($dates)-1) * $app['price'];

        if ($requiredFrom >= $availableFrom && $requiredFrom < $availableUntil && $requiredUntil <= $availableUntil) {
            $confirmedReservations = Database::connect()
                ->prepare('SELECT * from reservations where apartment_id = ?
        and reserved_from >= ? and reserved_till <= ?');
            $confirmedReservations->execute([$vars['id'], $_POST['reserve_from'], $_POST['reserve_till']]);
            $availableReservations = $confirmedReservations->fetchAll(PDO::FETCH_ASSOC);

            if (empty($availableReservations)) {
                $query = Database::connect()->prepare('INSERT INTO reservations(user_id, apartment_id,
                       reserved_from, reserved_till, total_price) VALUE (?, ?, ?, ?, ?)');
                $query->execute([$_SESSION['id'], $vars['id'], $_POST['reserve_from'], $_POST['reserve_till'], $totalPrice]);

                $updateQuery = Database::connect()->prepare('UPDATE apartments SET available_from=? WHERE id=?')
                    ->execute([$newDate , $vars['id']]);
                return new Redirect('/apartments/' . $vars['id']);
            }
            } return new Redirect('/reservations/error');

    }


    public function error(): View
    {
        return new View('Reservations/error');
    }
}
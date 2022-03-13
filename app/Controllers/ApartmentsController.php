<?php

namespace App\Controllers;

use App\Models\Apartment;
use App\Redirect;
use App\View;
use App\Database;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use PDO;

class ApartmentsController
{
    public function index(): View
    {
        $apartmentsQuery =  Database::connect()->prepare('SELECT * FROM apartments');
        $apartmentsQuery->execute();
        $apartmentQuery = $apartmentsQuery->fetchAll(PDO::FETCH_ASSOC);

        $availableApartments = [];

        foreach($apartmentQuery as $apartment) {
            $availableApartments[] = new Apartment($apartment['name'], $apartment['address'],
            $apartment['description'], $apartment['available_from'], $apartment['available_till'], $apartment['price'], $apartment['id'], $_SESSION['id']);
        }


        return new View('Apartments/index', [
            'availableApartments' => $availableApartments,
            'sessionId'=> $_SESSION['id'],
            'name'=>$_SESSION['name']]);
    }

    public function show(array $vars): View
    {
        $apartmentsQuery =  Database::connect()->prepare('SELECT * FROM apartments WHERE id=?');
        $apartmentsQuery->execute([$vars['id']]);
        $apartmentQuery = $apartmentsQuery->fetchAll(PDO::FETCH_ASSOC);
        $app= $apartmentQuery[0];

        $apartment = new Apartment($app['name'], $app['address'], $app['description'],
                                $app['available_from'], $app['available_till'], $app['price'], $app['id'], $_SESSION['id']);

        $query = Database::connect()->prepare('SELECT * FROM reviews WHERE apartment_id =? ORDER BY id DESC');
        $query->execute([$vars['id']]);
        $reviewsQuery = $query->fetchAll(PDO::FETCH_ASSOC);

        $pdo = Database::connect()->prepare('SELECT AVG(rating) FROM reviews WHERE apartment_id=?');
        $pdo->execute([$vars['id']]);
        $ratingQuery = $pdo->fetchColumn(0);

        $reservationQuery = Database::connect()->prepare('SELECT * FROM reservations WHERE apartment_id=? AND user_id=?');
        $reservationQuery->execute([$vars['id'], $_SESSION['id']]);
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
                $reservationPeriod[] = $value->format('Y/m/d');
            }
        }


        return new View('Apartments/show',
                ['apartment' => $apartment,
                 'user' => $app['user_id'],
                'sessionId' => $_SESSION['id'],
                'reservations' => $reservations,
                'period' => $reservationPeriod,
                'rating' => number_format($ratingQuery,2),
                'reviews' => $reviewsQuery,
                'name' => $_SESSION['name']
                ]);
    }

    public function add(): View
    {
        return new View('Apartments/add', [
            'name' => $_SESSION['name']
        ]);
    }

    public function save(): Redirect
    {
        if(empty($_POST['from'])){
            $availableFrom = Carbon::now();
        } else {
            $availableFrom = $_POST['from'];
        }

        if(empty($_POST['till'])){
            $availableTill = Carbon::now()->endOfYear();
        } else {
            $availableTill = $_POST['till'];
        }

        $query = Database::connect()->prepare('INSERT INTO apartments(name, address, description,
                       available_from, available_till, price, user_id) VALUE (?, ?, ?, ?, ?, ?, ?)');
        $query->execute([$_POST['name'], $_POST['address'], $_POST['description'],
            $availableFrom, $availableTill, $_POST['price'], $_SESSION['id']]);
        return new Redirect('/apartments');
    }

    public function delete(array $vars): Redirect
    {
        $query = Database::connect()->prepare('DELETE FROM apartments WHERE id=?')
            ->execute([$vars['id']]);
        return new Redirect('/apartments');
    }

    public function edit(array $vars): View
    {
        $apartmentsQuery =  Database::connect()->prepare('SELECT * FROM apartments WHERE id=?');
        $apartmentsQuery->execute([$vars['id']]);
        $apartmentQuery = $apartmentsQuery->fetchAll(PDO::FETCH_ASSOC);
        $app= $apartmentQuery[0];

        $apartment = new Apartment($app['name'], $app['address'], $app['description'],
            $app['available_from'], $app['available_till'], $app['price'], $app['id'], $_SESSION['id']);

        return new View('Apartments/edit', [
            'apartment'=>$apartment,
            'sessionId'=>$_SESSION['id'],
            'name'=>$_SESSION['name']
        ]);
    }

    public function update(array $vars): Redirect
    {
        $apartmentsQuery =  Database::connect()->prepare('SELECT available_from FROM apartments WHERE id=?');
        $apartmentsQuery->execute([$vars['id']]);
        $apartmentQuery = $apartmentsQuery->fetchColumn();

        if(empty($_POST['from'])) {
            $availableFrom = $apartmentQuery;
        } else {
            $availableFrom = $_POST['from'];
        }

        if(empty($_POST['till'])){
            $availableTill = Carbon::now()->endOfYear();
        } else {
            $availableTill = $_POST['till'];
        }

        $updateQuery = Database::connect()->prepare('UPDATE apartments SET name=?, address=?, description=?,
available_from=?, available_till=?, price=? WHERE id=?')
            ->execute([$_POST['name'], $_POST['address'], $_POST['description'],
                $availableFrom, $availableTill, $_POST['price'], $vars['id']]);

        return new Redirect('/apartments/' . $vars['id']);
    }
}
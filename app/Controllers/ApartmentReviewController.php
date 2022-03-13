<?php

namespace App\Controllers;


use App\Database;
use App\Redirect;
use App\View;
use PDO;

class ApartmentReviewController
{
    public function createReview(array $vars): View
    {
        if ($_SESSION['id'] == '') {
            var_dump('Log in to post review');
            return new View('home');
        }

        $pdo = Database::connect();
        $query = $pdo->prepare("SELECT * FROM apartments WHERE id = ?");
        $query->execute([$vars['apartmentId']]);
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        return new View('Apartments/show', [
            'id' => $data[0]['id']
        ]);
    }

    public function storeReview(array $data): Redirect
    {
        $pdo = Database::connect();
        $apartmentQuery = $pdo->prepare("SELECT * FROM apartments WHERE id = ?");
        $apartmentQuery->execute([$data['apartmentId']]);
        $apartment = $apartmentQuery->fetchAll(PDO::FETCH_ASSOC);
        $apartmentId = $apartment[0]['id'];


        $pdo = Database::connect();
        $query = $pdo->prepare('INSERT INTO reviews(user_id, author, apartment_id, rating, review) VALUE (?, ?, ?, ?, ?)');
        $query->execute([$_SESSION['id'], $_SESSION['name'], $apartmentId, $_POST['rating'], $_POST['review']]);

        return new Redirect('/apartments/'.$apartmentId);

    }

    public function deleteReview(array $data): Redirect
    {
        $apartmentId = $data['apartmentId'];
        $reviewId = $data['id'];
        $pdo = Database::connect();
        $query = $pdo->prepare('DELETE FROM reviews WHERE user_id = ? AND apartment_id =? AND id = ?');
        $query->execute([$_SESSION['id'], $apartmentId, $reviewId]);

        return new Redirect('/apartments/' . $apartmentId);
    }

}
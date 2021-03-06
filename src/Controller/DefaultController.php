<?php

namespace App\Controller;

use App\Repository\LikesRepository;
use App\Repository\UsersRepository;
use phpDocumentor\Reflection\Location;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    public function index(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard');
        }
        
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }

    /**
    * @Route("/dashboard", name="dashboard")
     */
    public function dashboard(LikesRepository $likesRepository)
    {
        $user = $this->getUser();
        $user_id = $user->getId();
        $user_sports = $user->getSport();
        $user_distance = $user->getDistance();
        $location = explode(';', $user->getLastLocation());
        $latitude = $location[0];
        $longitude = $location[1];
        $likes = $likesRepository->findBy(array('users' => $user_id));
        $users_liked= array();
        $listUsers= array();

        foreach ($likes as $like) {
            $users_liked[]=$like->getUsersLiked();
        }

        foreach ($user_sports as $sport) {
            $sportUsers = $sport->getUsers();
            foreach ($sportUsers as $users) {
                $distance_km = $users->getDistanceOpt($latitude, $longitude);
                if (count($users_liked) != 0) {
                    if (!in_array($users, $users_liked)) {
                        if (($users->getId() != $user_id) && (($distance_km <= $user_distance) && ($distance_km <= $users->getDistance()))) {
                            $listUsers[] = $users;
                        }
                    }
                } else {
                    if (($users->getId() != $user_id) && (($distance_km <= $user_distance) && ($distance_km <= $users->getDistance()))) {
                        $listUsers[] = $users;
                    }
                }
            }
        }
        $listUsers = array_unique($listUsers, SORT_REGULAR);
        
        
        
        return $this->render('default/dashboard.html.twig', [
            'users' => $listUsers , 'latitude' => $latitude, 'longitude' => $longitude,
        ]);
    }
}

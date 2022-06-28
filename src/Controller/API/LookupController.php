<?php

namespace App\Controller\API;

use App\CalebApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'app_api')]
class LookupController extends AbstractController
{
    #[Route('/lookup/{callsign}', name: '_lookup', methods: [Request::METHOD_GET])]
    public function byCallsign(string $callsign, CalebApi $api): Response
    {
        return $this->json([
            'nodes' => $api->getAllstarNodesByCallsign($callsign),
        ]);
    }
}

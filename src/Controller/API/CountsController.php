<?php

namespace App\Controller\API;

use App\CalebApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'app_api')]
class CountsController extends AbstractController
{
    #[Route('/nodes', name: '_nodes')]
    public function getNodes(CalebApi $api): Response
    {
        return $this->json([
            'p25_nodes' => $api->getP25Nodes(),
            'dmr_nodes' => $api->getDMRNodes(),
            'hh_nodes' => $api->getHHNodes(),
            'hoip_nodes' => $api->getHPNodes(),
            'allstar_users' => $api->getAllstarNodes(),
            'irlp_nodes' => $api->getIRLPNodes(),
        ]);
    }
}

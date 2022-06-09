<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testGetHomepage(): void
    {
        $client = self::createClient();
        $client->request('GET', '/');

        self::assertResponseStatusCodeSame(200);
    }
}

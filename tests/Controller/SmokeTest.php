<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeTest extends WebTestCase
{
    public function testApiDocUrlIsSuccessFul(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/doc');
        self::assertResponseIsSuccessful();
    }
}

<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    // On vérifie que l'apiToken ne soit pas null quand un new user et créé.
    public function testTheAutomaticApiTokenSettingWhenAnUserIsCreated(): void
    {
        $user = new User();
        $this->assertNotNull($user->getApiToken());
    }

    // On vérifie qu'un role a été attribué au new user.
    public function testThanAnUserHasLeastOneRoleUser(): void
    {
        $user = new User();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }
}

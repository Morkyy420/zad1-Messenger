<?php

require __DIR__.'/vendor/autoload.php';

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

$kernel = new \App\Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();
$entityManager = $container->get('doctrine')->getManager();
$passwordHasher = $container->get(UserPasswordHasherInterface::class);

// Create test user 1
$user1 = new User();
$user1->setUsername('TestUser');
$user1->setEmail('test@example.com');
$user1->setPassword($passwordHasher->hashPassword($user1, 'password123'));

// Create test user 2
$user2 = new User();
$user2->setUsername('JohnDoe');
$user2->setEmail('john@example.com');
$user2->setPassword($passwordHasher->hashPassword($user2, 'password123'));

// Create test user 3
$user3 = new User();
$user3->setUsername('JaneDoe');
$user3->setEmail('jane@example.com');
$user3->setPassword($passwordHasher->hashPassword($user3, 'password123'));

$entityManager->persist($user1);
$entityManager->persist($user2);
$entityManager->persist($user3);
$entityManager->flush();

echo "Test users created successfully!\n";
echo "- test@example.com / password123\n";
echo "- john@example.com / password123\n";
echo "- jane@example.com / password123\n";

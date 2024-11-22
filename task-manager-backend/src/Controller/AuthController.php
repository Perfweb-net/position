<?php
// src/Controller/AuthController.php
namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuthController extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    // Méthode d'inscription
    #[Route('/api/register', methods: ['POST'])]
    public function register(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérification des données
        if (empty($data['username']) || empty($data['password'])) {
            return new JsonResponse(['error' => 'Missing credentials'], 400);
        }

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $userRepository->findOneBy(['username' => $data['username']]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'User already exists'], 400);
        }

        // Création de l'utilisateur
        $user = new User();
        $user->setUsername($data['username']);
        $user->setPassword($this->passwordHasher->hash($data['password'])); // Utilisation du nouveau PasswordHasherInterface
        $user->setRoles(['ROLE_USER']); // Rôle par défaut

        // Enregistrer l'utilisateur en base de données
        $userRepository->save($user, true);

        return new JsonResponse(['status' => 'User created'], 201);
    }

    // Méthode de connexion (génération de token JWT)
    #[Route('/api/login', methods: ['POST'])]
    public function login(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        // Vérification des informations
        if (!$username || !$password) {
            return new JsonResponse(['error' => 'Missing credentials'], 400);
        }

        $user = $userRepository->findOneBy(['username' => $username]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['error' => 'Invalid credentials'], 401);
        }

        // Retourner le token JWT
        $token = 'TOKEN_GENERATED'; // Générez votre token JWT ici avec votre service JWT
        return new JsonResponse(['token' => $token]);
    }
}

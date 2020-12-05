<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/api")
 */
class UserController extends AbstractController
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Returns the list of users.
     *
     * @Route("/users", name="get_all_users", methods={"GET"})
     */
    public function getAll(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        $data = [];

        if (!$users) {
            return new JsonResponse(['error' => 'No users.'], Response::HTTP_NO_CONTENT);
        }

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'password' => $user->getPassword()
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * Creates new user.
     *
     * @Route("/users", name="add_user", methods={"POST"})
     */
    public function add(Request $request, UserPasswordEncoderInterface $passwordEncoder): JsonResponse
    {
        $user = new User();
        $data = json_decode($request->getContent(), true);

        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return new JsonResponse(['error' => 'Expecting mandatory parameters!'], Response::HTTP_BAD_REQUEST);
        }

        $username = $data['username'];
        $email = $data['email'];
        $password = $data['password'];

        $user = $this->userRepository->findOneBy(['username' => $username]);

        if ($this->userRepository->getUserByUsernameOrEmail($data['username'], $data['email'])) {
            return new JsonResponse(['error' => 'This user already exist!'], Response::HTTP_CONFLICT);
        }

        $password = $passwordEncoder->encodePassword($user, $password);

        $this->userRepository->saveUser($username, $email, $password);

        return new JsonResponse(['status' => 'User created!'], Response::HTTP_CREATED);
    }


    /**
     * Returns user of given id.
     *
     * @Route("/users/{id}", name="get_one_user", methods={"GET"})
     */
    public function show($id): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['id' => $id]);

        if (!$user) {
            return new JsonResponse(['error' => 'User doesn\'t exist!'], Response::HTTP_NO_CONTENT);
        }

        $data = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * Updates user of given id.
     *
     * @Route("/users/{id}", name="user_update", methods={"PUT", "PATCH"})
     */
    public function update($id, Request $request, UserPasswordEncoderInterface $passwordEncoder): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['id' => $id]);

        if (!$user) {
            return new JsonResponse(['error' => 'User doesn\'t exist!'], Response::HTTP_NO_CONTENT);
        }

        $data = json_decode($request->getContent(), true);

        empty($data['username']) ? true : $user->setUsername($data['username']);
        empty($data['email']) ? true : $user->setEmail($data['email']);
        empty($data['password']) ? true : $user->setPassword($passwordEncoder->encodePassword($user, $data['password']));

        $updatedUser = $this->userRepository->updateUser($user);

        return new JsonResponse($updatedUser->toArray(), Response::HTTP_OK);
    }

    /**
     * Deletes user of given id.
     *
     * @Route("/users/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete($id): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['id' => $id]);

        if (!$user) {
            return new JsonResponse(['error' => 'User doesn\'t exist!'], Response::HTTP_NO_CONTENT);
        }

        $this->userRepository->removeUser($user);

        return new JsonResponse(['status' => 'User deleted'], Response::HTTP_NO_CONTENT);
    }
}

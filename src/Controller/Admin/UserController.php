<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/user')]
class UserController extends AbstractController
{
    #[Route('', name: 'admin_user_index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $template = $request->query->get('ajax')
            ? '_list.html.twig'
            : 'index.html.twig';

        return $this->render('admin/user/' . $template, [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plain_password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);

            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            if ($request->query->get('ajax')) {
                return new Response(null, Response::HTTP_NO_CONTENT);
            }

            return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        $template = $request->query->get('ajax')
            ? '_form.html.twig'
            : 'new.html.twig';

        return $this->renderForm('admin/user/' . $template, [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_user_show', methods: ['GET'])]
    public function show(Request $request, User $user): Response
    {
        $template = $request->query->get('ajax')
            ? '_show.html.twig'
            : 'show.html.twig';

        return $this->render('admin/user/' . $template, [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(UserType::class, $user, ['edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string|null $plainPassword */
            $plainPassword = $form->get('plain_password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);

                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            if ($request->query->get('ajax')) {
                return new Response(null, Response::HTTP_NO_CONTENT);
            }

            return $this->redirectToRoute('admin_user_show', [
                'id' => $user->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        $template = $request->query->get('ajax')
            ? '_form.html.twig'
            : 'edit.html.twig';

        return $this->renderForm('admin/user/' . $template, [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_user_delete', methods: ['POST', 'DELETE'])]
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        $tokenId = sprintf('delete_user_%s', (int) $user->getId());
        $token = (string) $request->request->get('_token');

        if ($this->isCsrfTokenValid($tokenId, $token)) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
    }
}

<?php

namespace App\Controller;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\LdapService;
use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Form\LdapLoginType;


class GuideController extends AbstractController
{
    private LdapService $ldapService;

    public function __construct(LdapService $ldapService)
    {
        $this->ldapService = $ldapService;
    }

    #[Route('/guide', name: 'app_guide')]
    public function show(): Response
    {
        return $this->render('guide/guide.html.twig');
    }



    /**
     * @throws Exception
     */
    #[Route('/guide/wiki', name: 'users_auth_ldap')]
    public function login(Request $request): Response
    {
        $form = $this->createForm(LdapLoginType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $username = $data['username'];
            $password = $data['password'];

            if ($this->ldapService->authenticate($username, $password)) {
                $this->addFlash('success', 'Connexion réussie.');
                return $this->redirectToRoute('home');
            } else {
                $this->addFlash('error', 'Échec de l\'authentification.');
            }
        }

        return $this->render('guide/wiki.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/auth/ldapmain/callback', name: 'users_auth_ldapmain_callback', methods: ['POST'])]
    public function ldapCallback(Request $request, LoggerInterface $logger): Response
    {


        $username = $request->request->get('username');
        $password = $request->request->get('password');

        $logger->info("Tentative d'authentification LDAP", [
            'username' => $username,
        ]);




        if (!$password) {
            $logger->error(' mot de passe manquant');
            return new Response('mot de passe manquant', Response::HTTP_BAD_REQUEST);
        }


        try {
            if ($this->ldapService->authenticate($username, $password)) {
                $logger->info("Authentification réussie pour l'utilisateur $username.");
                return $this->redirectToRoute('home');
            } else {
                $logger->error("Échec de l'authentification pour l'utilisateur $username.");
            }
        } catch (Exception $e) {
            $logger->error('Erreur LDAP : ' . $e->getMessage());
            return new Response('Erreur lors de l\'authentification : ' . $e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }

        return new Response('Erreur d\'authentification LDAP', Response::HTTP_UNAUTHORIZED);
    }

}
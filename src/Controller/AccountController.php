<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Form\ImgModifyType;
use App\Entity\UserImgModify;
use App\Entity\PasswordUpdate;
use App\Form\RegistrationType;
use App\Form\PasswordUpdateType;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;

class AccountController extends AbstractController
{
    /**
     * Permet à l'utilisateur de se connecter
     *
     * @param AuthenticationUtils $utils
     * @return Response
     */
    #[Route('/login', name: 'account_login')]
    public function index(AuthenticationUtils $utils): Response
    {
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();
        $loginError = null;
        dump($error);
        if($error instanceof TooManyLoginAttemptsAuthenticationException)
        {
            // L'erreur est due à la limitation de la connexion (login throttling)
            $loginError = "Trop de tentatives de connexion. Réessayez plus tard";
        }

        $user = $this->getUser();

        if($user)
        {
            if(in_array('ROLE_USER',$user->getRoles()))
            {
                return $this->redirectToRoute("homepage");
            }
        }


        return $this->render('account/index.html.twig', [
            'hasError' => $error !== null,
            'username' => $username,
            'loginError' => $loginError
        ]);
    }

    /**
     * Permet de se déconnecter
     *
     * @return void
     */
    #[Route('/logout', name: 'account_logout')]
    public function logout(): void{}


    /**
     * Permet d'inscrire un nouvel utilisateur
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Route("/register", name:"account_register")]
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher) : Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // gestion de mon image
            $file = $form['picture']->getData();
            if(!empty($file))
            {
                $originalfilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalfilename);
                $newFilename = $safeFilename."-".uniqid().".".$file->guessExtension();

                try{
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                }catch(FileException $e)
                {
                    return $e->getMessage();
                }
                $user->setPicture($newFilename);
            }

            $hash = $hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hash);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash(
                'success',
                'Votre compte a bien été créé'
            );
            return $this->redirectToRoute('account_login');
        }

        return $this->render("account/registration.html.twig",[
            'myForm' => $form->createView()
        ]);
    }

    /**
     * Permet de modifier son profil
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/account/profile", name:"account_profile")]
    #[IsGranted("ROLE_USER")]
    public function profile(Request $request, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser(); // récup le user connecté
        $form = $this->createForm(AccountType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            // gestion image 
            $user->setSlug('');

            $manager->persist($user);
            $manager->flush();
            $this->addFlash(
            'success',
            "Les données ont été enregistrées avec succès"
            );
            return $this->redirectToRoute('account_index');
        }

        return $this->render("account/profile.html.twig",[
            "myForm" => $form->createView()
        ]);
    }

    /**
     * Permet de modifier le mot de passe
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Route("/account/password-update", name:"account_password")]
    #[IsGranted("ROLE_USER")]
    public function updatePassword(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $passwordUpdate = new PasswordUpdate();
        $user = $this->getUser();
        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            // vérif que le mot de passe correspond à l'ancien
            if(!password_verify($passwordUpdate->getOldPassword(), $user->getPassword()))
            {
                // gérer l'erreur
                $form->get("oldPassword")->addError(new FormError("Le mot de passe que vous avez tapé n'est pas votre mot de passe actuel"));
            }else{
                // hash le nouveau mot de passe
                $hash = $hasher->hashPassword($user, $passwordUpdate->getNewPassword());
                $user->setPassword($hash);
                $manager->persist($user);
                $manager->flush();
                $this->addFlash(
                    'success',
                    "Votre mot de passe a bien été modifié"
                );
                return $this->redirectToRoute('account_index');
            }
        }

        return $this->render("account/password.html.twig",[
            "myForm" => $form->createView()
        ]);

    }


    /**
     * Permet de modifier l'image (avatar) de l'utilisateur
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/account/imgmodify", name:"account_modifimg")]
    #[IsGranted("ROLE_USER")]
    public function imgModify(Request $request, EntityManagerInterface $manager): Response
    {
        $imgModify = new UserImgModify();
        $user = $this->getUser();
        $form = $this->createForm(ImgModifyType::class, $imgModify);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $file = $form['newPicture']->getData();
            if(!empty($file))
            {
                $originalfilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalfilename);
                $newFilename = $safeFilename."-".uniqid().".".$file->guessExtension();

                try{
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    // gestion de la non-obligation de l'image
                    // supprimer si une image était déjà présente
                    if(!empty($user->getPicture()))
                    {
                        unlink($this->getParameter('uploads_directory').'/'.$user->getPicture());
                    }

                }catch(FileException $e)
                {
                    return $e->getMessage();
                }                

                $user->setPicture($newFilename);
            }

            $manager->persist($user);
            $manager->flush();
            $this->addFlash(
            'success',
            'Votre avatar a bien été modifié'
            );
            return $this->redirectToRoute('account_index');


        }

        return $this->render("account/imgModify.html.twig",[
            'myForm' => $form->createView()
         ]);
    }

    /**
     * Permet de supprimer l'avatar de l'utilisateur
     *
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/account/delimg", name:"account_delimg")]
    #[IsGranted("ROLE_USER")]
    public function removeImg(EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        if(!empty($user->getPicture()))
        {
            unlink($this->getParameter('uploads_directory').'/'.$user->getPicture());
            $user->setPicture('');
            $manager->persist($user);
            $manager->flush();
            $this->addFlash(
                'success',
                'Votre avatar a bien été supprimé'
            );
        }
        return $this->redirectToRoute('account_index');
    }


    /**
     * Permet d'afficher les réseravations de l'utilisateur
     *
     * @return Response
     */
    #[Route("/account/booking", name:"account_booking")]
    #[IsGranted("ROLE_USER")]
    public function bookings(): Response
    {
        return $this->render("account/bookings.html.twig");
    }
}

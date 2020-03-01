<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;


use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\HttpFoundation\Session\Session;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index()
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    public function getTasques()
    {
      $user_repo = $this->getDoctrine()->getRepository(User::class);
      $users = $user_repo->findAll();

      foreach ($users as $user) {
        echo "<h2> {$user->getName()} {$user->getSurname()} </h2>";
        foreach ($user->getTasks() as $task) {
          echo $task->getTitle()." </br>";
        }
      }
      die();
    }

    public function crear(Request $request, UserPasswordEncoderInterface $encoder){

      $user = new User();
      $form=$this ->createFormBuilder($user)
                  ->setMethod('POST')
                    ->add('name',TextType::class,['attr'=>['placeholder'=>"Nom"]])
                    ->add('surname',TextType::class,['attr'=>['placeholder'=>"Cognom"]])
                    ->add('email',EmailType::class,['attr'=>['placeholder'=>"Email"]])
                    ->add('password',PasswordType::class,['attr'=>['placeholder'=>"Password"]])
                    ->add('submit',SubmitType::class,['label'=>'Crear Usuari','attr'=>['class'=>'btn']])
                    ->getForm();
      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid()){
        $user->setRole('ROLE_USER');
        $user->setCreatedAt(new \Datetime('now'));

        $encoded = $encoder->encodePassword($user, $user->getPassword());
        $user->setPassword($encoded);
        // var_dump($user);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $session = new Session();
        $session->getFlashBag()->add('message','Usuari creat correctament');

        return $this->redirectToRoute("registre");
      }

      return $this->render('user/registre.html.twig',[
        'form' => $form->createView()
      ]);
    }


    public function login(AuthenticationUtils $autentificationUtils) {
      $error = $autentificationUtils->getLastAuthenticationError();

      $lasUsername = $autentificationUtils->getLastUsername();

      return $this->render('user/login.html.twig',[
        'error' => $error,
        'lastUsername' => $lasUsername
      ]);
    }

}

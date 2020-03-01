<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Symfony\Component\HttpFoundation\Session\Session;

use App\Entity\Task;

class TaskController extends AbstractController
{
    /**
     * @Route("/task", name="task")
     */
    public function index()
    {
        return $this->render('task/index.html.twig', [
            'controller_name' => 'TaskController',
        ]);
    }

    public function getMails()
    {
      $em = $this->getDoctrine()->getManager();
      $task_repo = $this->getDoctrine()->getRepository(Task::class);
      $tasks = $task_repo->findAll();

      foreach ($tasks as $task) {
        echo $task->getUser()->getEmail()." : ".$task->getTitle()."<br>";
      };
      die();
    }

    public function llistarTasques(UserInterface $user)
    {
      $repo = $this->getDoctrine()->getRepository(Task::class);
      $tasks = $repo->findAll();
      return $this->render('task/listTask.html.twig',[
        // 'tasques' => $user->getTasks(),
        'tasques' => $tasks,
        'userid' => $user->getId(),
      ]);
    }

    public function creation(Request $request, UserInterface $user){
      $task= new Task();
      $form=$this ->createFormBuilder($task)
                  ->setMethod('POST')
                    ->add('title',TextType::class,['attr'=>['placeholder'=>"TITOL"]])
                    ->add('content',TextareaType::class,['attr'=>['placeholder'=>"Contingut",'rows'=>'4','cols'=>'50']])
                    ->add('priority',ChoiceType::class,[
                        'choices' => [
                            'Low'=>"low",
                            'Medium'=>'medium',
                            'High'=>'high',],
                          ])
                    ->add('hours',IntegerType::class,['attr'=>['placeholder'=>"Hores",'class'=>'hores']])
                    ->add('submit',SubmitType::class,['label'=>'Crear Tasca','attr'=>['class'=>'btn']])
                    ->getForm();
      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid()){
        $task->setUser($user);
        $task->setCreatedAt(new \Datetime('now'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($task);
        $em->flush();


        // return $this->redirectToRoute("listtaskes");
        return $this->redirect(
          $this->generateURL('detallTaska',['taskid'=> $task->getId()])
        );
      }

      return $this->render('task/createTask.html.twig',[
        'edit' => false,
        'form' => $form->createView()
      ]);
    }

    public function edit(Request $request,UserInterface $user,$taskid){
        $repo = $this->getDoctrine()->getRepository(Task::class);
        $task = $repo->find($taskid);
      if (!$user || ($user->getId() != $task->getUser()->getId())){
        return $this->redirectToRoute('listtaskes');
      }else{
        $form=$this ->createFormBuilder($task)
                    ->setMethod('POST')
                      ->add('title',TextType::class,['attr'=>['value'=>$task->getTitle()]])
                      ->add('content',TextareaType::class,['attr'=>['value'=>$task->getContent(),'rows'=>'4','cols'=>'50']])
                      ->add('priority',ChoiceType::class,[
                          'choices' => [
                              'Low'=>"low",
                              'Medium'=>'medium',
                              'High'=>'high',],'attr'=>['value'=>$task->getTitle()]])
                      ->add('hours',IntegerType::class,['attr'=>['value'=>$task->getHours(),'class'=>'hores']])
                      ->add('submit',SubmitType::class,['label'=>'Editar Tasca','attr'=>['class'=>'btn']])
                      ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

          $task->setUser($user);
          $task->setCreatedAt(new \Datetime('now'));

          $em = $this->getDoctrine()->getManager();
          $em->persist($task);
          $em->flush();


          return $this->redirectToRoute("listtaskes");
        }
      }
      return $this->render('task/createTask.html.twig',[
        'edit'=>true,
        'form'=>$form->createView()
      ]);

    }

    public function detalls(UserInterface $user, $taskid)
    {
      $repo = $this->getDoctrine()->getRepository(Task::class);
      $task = $repo->find($taskid);
      return $this->render('task/detallsTask.html.twig',[
        'tasca' => $task,
      ]);
    }

    public function delete(UserInterface $user, $taskid)
    {
      $repo = $this->getDoctrine()->getRepository(Task::class);
      $task = $repo->find($taskid);
      $em = $this->getDoctrine()->getManager();
      $em->remove($task);
      $em->flush();
      return $this->redirectToRoute("listtaskes");
    }
}

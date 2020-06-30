<?php

namespace App\Controller;

use App\Form\MessageFormType;
use App\Entity\Message;
use App\Entity\User;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{


    //TODO ADD FIRST NAME AND LAST NAME TO USER
     
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request)
    {   

        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User not authorized');

        $message = new Message();

        //set the message user to current user
        $message->setUser($this->getUser());

        // using getters
        $user = $message->getUser();
        $userid = $user->getId();

        // create form from class
        $form = $this->createForm(MessageFormType::class, $message);

        //Todo add form validation

        $form->handleRequest($request);

        // posted and validated
        if ($form->isSubmitted() && $form->isValid()) {

            //Todo this will be the twilio callback url
            $message->setStatus("Sent");
            $message = $form->getData();
            $message->setTimestamp(new \DateTime('now'));

            //Todo send to Rabbid queue
            //save to db
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            //Todo flash to view with proper message
            return $this->render('home/home.html.twig', [
                'message' => $form->createView(),
                'success' => 'Message successfully sent',
                // returnin the userID so we can generate the route for it
                'user' => $userid,
            ]);
            
        }

        return $this->render('home/home.html.twig', [
            'message' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{user}/messages", name="userMessages")
     */
    public function show($user)
    {
        // deny access if applicable
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User not authorized');

        //get user vars and store
        $currentUser = $this->getUser();
        $id = $currentUser->getId();
        $roles = $currentUser->getRoles();

        // if super admin then allow to view all messages
        if($roles[0] !== "ROLE_SUPER_ADMIN") {
            if($id != $user) {
                return $this->redirectToRoute('home');
            }
        }

        // use repository to get data
        $repository = $this->getDoctrine()->getRepository(Message::class);
        //get current user so we can only query ourselves
        $user = $this->getUser();

        $messages = $this->getDoctrine()
            ->getRepository(Message::class)
            ->getMessagesFromUser($user);


        return $this->render('home/messages.html.twig', [
            'messages' => $messages,
            'user' => $user,
        ]);
    }

    /**
     * @Route("/admin/messages", name="admin")
     */
    public function all(Request $request)
    {
        // deny access if applicable
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User not authorized');

        // use repository to get data
        $messages = $this->getDoctrine()->getRepository(Message::class)->findAll();

        return $this->render('admin/all.html.twig', [
            'messages' => $messages,
            'user' => $user,
        ]);
        
    }
}

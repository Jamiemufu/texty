<?php

namespace App\Controller;
use libphonenumber\PhoneNumberUtil;
use App\Form\MessageFormType;
use App\Entity\Message;
use App\Entity\User;
use Twilio\Rest\Client;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
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

            //Todo this will be the twilio callback
            $message = $form->getData();
            $message->setStatus("Sent");
            $message->setTimestamp(new \DateTime('now'));

            //Todo send to Rabbid queue
            //save to db
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            //Todo Twilio send
            $to = $message->getPhonenumber();
            $text = $message->getText();
            $twilio = $this->container->get('twilio.client');
            $twilio->messages->create($to, [
                'from' => $this->getParameter('twilio_number'),
                'body' => $text,
            ]);

            $this->addFlash(
                'notice',
                'Your message has been sent...'
            );
            
            return $this->redirect($request->getUri());
            
        }

        return $this->render('home/home.html.twig', [
            'message' => $form->createView(),
            'user' => $user,
        ]);
    }

    public function __toString()
    {
        return $phoneNumberUtil = PhoneNumberUtil::getInstance();
        // $number = $phoneNumberUtil->format($this->phone_number, \libphonenumber\PhoneNumberFormat::NATIONAL);     

        // return $this->nickName . " - " . $number;
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
        if($roles[0] != "ROLE_SUPER_ADMIN") {
            //if id's dont match, not authorized to view messages
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
     * @Route("/admin/all", name="admin")
     */
    public function all()
    {
        // deny access if applicable
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, 'User not authorized');

        // use repository to get data
        $messages = $this->getDoctrine()->getRepository(Message::class)->findAll();

        return $this->render('admin/all.html.twig', [
            'messages' => $messages,
            
        ]);
        
    }
}

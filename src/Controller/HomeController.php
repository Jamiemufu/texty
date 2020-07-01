<?php

namespace App\Controller;

use App\Message\Sendsms;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageFormType;
use libphonenumber\PhoneNumberUtil;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller
{

    /**
     * @Route("/", name="home")
     * 
     * display form to send message
     */
    public function index(Request $request, MessageBusInterface $bus)
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
        $form->handleRequest($request);

        // posted and validated
        if ($form->isSubmitted() && $form->isValid()) {

            // map form data to message
            $message = $form->getData();
            $message->setStatus("Processing"); //this will be a callback
            $message->setTimestamp(new \DateTime('now'));

            //save to db
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            //format phone_number object to string for twilio send
            $formatPhone = \libphonenumber\PhoneNumberUtil::getInstance();
            $to = $formatPhone->format($message->getPhoneNumber(), \libphonenumber\PhoneNumberFormat::INTERNATIONAL);
            $text = $message->getText();

            // send to queue
            $bus->dispatch(new Sendsms($message->getId(), $text, $this->getParameter('twilio_number'), $to));

            // simple flash message to view
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

    /**
     * @Route("/{user}/messages", name="userMessages")
     *
     * show users messages sent
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
        if ($roles[0] != "ROLE_SUPER_ADMIN") {
            //if id's dont match, not authorized to view messages
            if ($id != $user) {
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
     *
     * View all messages sent accross all users
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

    /**
     * @Route("/message/{id}/status", name="status")
     *
     * Get the status and messageID from the callback of Twilio send
     * Update database accordingly - Twilio will send another request when status changes
     */
    public function statusRequest($id, Request $request)
    {
        $messageID = $request->request->get('SmsSid');
        $status = $request->request->get('SmsStatus');

        $message = $this->getDoctrine()
            ->getRepository(Message::class)
            ->find($id);

        //check if they are already written and up to date
        if ($messageID !== $message->getId() || $status !== $message->getStatus()) {
            $entityManager = $this->getDoctrine()->getManager();
            $message->setSmsId($messageID);
            $message->setStatus($status);
            $entityManager->flush();
        }

        $response = new Response();
        $response->setContent(json_encode([
            'success' => "Successfully updated SMSID and Status",
        ]));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}

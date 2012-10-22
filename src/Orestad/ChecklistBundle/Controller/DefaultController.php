<?php

namespace Orestad\ChecklistBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Orestad\ChecklistBundle\Entity\User;
use Orestad\ChecklistBundle\Entity\Store;
use Orestad\ChecklistBundle\Entity\Relationship;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('OrestadChecklistBundle:Default:index.html.twig');
    }

    public function pageAction()
    {
        return $this->render('OrestadChecklistBundle:Default:page.html.twig');
    }

    public function userAddAction(Request $request)
    {
        $stores = $this->getDoctrine()->getRepository('OrestadChecklistBundle:Store')->findAll();
        $storeArray = array();
        foreach ($stores as $store) {
            $storeArray[$store->getId()] = $store->getName();
        }
        $user = new User();
        $form = $this->createFormBuilder($user)
            ->add('username', 'text')
            ->add('email', 'email')
            ->add('password', 'text')
            ->add('store', 'choice', array('choices' => $storeArray, 'multiple' => true))
            ->getForm();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {

                // Encode the password
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword('ryanpass', $user->getSalt());
                $user->setPassword($password);

                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($user);
                $em->flush();

                $storeId = '';

                $conn = $this->container->get('database_connection');
                $sql = "INSERT INTO user_store_relationship (user_id, store_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $userId = $user->getId();

                foreach ($user->getStore() as $storeId) {
                    $stmt->bindValue(1, $userId);
                    $stmt->bindValue(2, $storeId);
                    $stmt->execute();
                }

                // $message = \Swift_Message::newInstance()
                //     ->setSubject('Hello Email')
                //     ->setFrom('theorboman@gmail.com')
                //     ->setTo($user->getEmail())
                //     ->setBody('Hey there, new user!');
                // $this->get('mailer')->send($message);

                $this->get('session')->setFlash('orestad-notice', $this->get('translator')->trans('New user has been sucessfully added.'));
                return $this->redirect($this->generateUrl('orestad_checklist_user', array('uid' => $userId)));

            }
        }
        return $this->render('OrestadChecklistBundle:Default:useradd.html.twig', array('form' => $form->createView()));
    }

    public function userAction($uid) {
        $userRepository = $this->getDoctrine()->getRepository('OrestadChecklistBundle:User');
        $user = $userRepository->find($uid);
        $usr= $this->get('security.context')->getToken()->getUser();
        $username = $usr->getUsername();
        return $this->render('OrestadChecklistBundle:Default:user.html.twig', array('user' => $user, 'username' => $username));
    }

    public function userAllAction()
    {
        $allUsers = $this->getDoctrine()->getRepository('OrestadChecklistBundle:User')->findAll();
        $allStores = $this->getDoctrine()->getRepository('OrestadChecklistBundle:Store');

        $conn = $this->container->get('database_connection');
        $sql = "SELECT store_id FROM user_store_relationship WHERE user_id = ?";
        $stmt = $conn->prepare($sql);

        $users = array();
        $currUserId = 0;

        foreach ($allUsers as $user) {
            $userId = $user->getId();
            if ($userId != $currUserId) {
                $users[$userId] = array(
                    'id' => $userId,
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail(),
                );
                $stmt->bindValue(1, $userId);
                $stmt->execute();
                $results = $stmt->fetchAll();
                if (empty($results)) {
                    $users[$userId]['stores'] = false;
                } else {
                    foreach ($results as $result) {
                        $thisStore = $allStores->findOneById($result['store_id']);
                        $users[$userId]['stores'][$thisStore->getId()] = $thisStore->getName();
                    }
                }
            }
            $currUserId = $userId;
        }
        return $this->render('OrestadChecklistBundle:Default:userall.html.twig', array('users' => $users));
    }

    public function storeAddAction(Request $request)
    {
        $store = new Store();
        $form = $this->createFormBuilder($store)
            ->add('name', 'text')
            ->add('email', 'email')
            ->add('tel', 'text')
            ->add('address', 'text')
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {

                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($store);
                $em->flush();
                $this->get('session')->setFlash('orestad-notice', $this->get('translator')->trans('New store has been sucessfully added.'));

                return $this->redirect($this->generateUrl('orestad_checklist_store_all'));

            }
        }
        return $this->render('OrestadChecklistBundle:Default:storeadd.html.twig', array('form' => $form->createView()));

    }

    public function storeAllAction()
    {
        $stores = $this->getDoctrine()->getRepository('OrestadChecklistBundle:Store')->findAll();
        return $this->render('OrestadChecklistBundle:Default:storeall.html.twig', array('stores' => $stores));
    }

}

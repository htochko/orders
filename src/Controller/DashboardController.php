<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderFormType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use App\Services\RevoultApiWrapper;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_landing')]
    public function indexAction() {
        return $this->render('landing.html.twig');
    }
    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboardAction() {
        /* @var User $user*/
        $user = $this->getUser();
        return $this->render('order/dashboard.html.twig', ['orders' => $user->getOrders()]);
    }
    #[Route('/order', name: 'app_order', methods: ['GET', 'POST'])]
    public function orderAction(
            Request $request,
            RevoultApiWrapper $paymentService,
            EntityManagerInterface $entityManager,
            MailerInterface $mailer,
    ) {
        $order = new Order();
        $form = $this->createForm(OrderFormType::class, $order);
        $form->handleRequest($request);
        $user = $this->getUser();
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $order->setStatus(Order::STATUS_FAILED);
            $order->setUser($user);
            $order->setCreatedAt(new \DateTimeImmutable())
                  ->setUpdatedAt(new \DateTimeImmutable());
            $order = $paymentService->placeOrder($order);
            $entityManager->persist($order);
            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from(new Address($this->getParameter('app.mail'), $this->getParameter('app.mail_name')))
                ->to($form->get('email')->getData())
                ->subject("Deposit {$order->getAmount()}  {$order->getCurrency()}")
                ->htmlTemplate('order/confirmation_email.html.twig')
                ->context(['order' => $order]);

            $mailer->send($email);
        }

        return $this->render('order/order.html.twig', [
            'orderForm' => $form->createView(),
        ]);
    }
}
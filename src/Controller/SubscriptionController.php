<?php

namespace App\Controller;

use Stripe\Webhook;
use App\Service\PaymentService;
use App\Service\EmailNotificationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class SubscriptionController extends AbstractController
{
    // Route lorsque le paiement est réussi
    #[Route('/payment-success', name: 'app_payment_success')]
    public function paymentSuccess(Request $request, EmailNotificationService $ens, PaymentService $ps): Response
    {
     if($request->headers->get('referer') === 'https://checkout.stripe.com')
        {
            $subscription = $ps->addSubscription();
            $ens->sendEmail(
                $this->getUser()->getEmail() , [
                    'subject' => 'Thank you for your purchase',
                    'template' => 'premium',
                ]     
                );
            return $this->render('subscription/payment-success.html.twig', [
                'subscription' => $subscription,
            ]);
        }
        else {
            $this->addFlash('error', "You can't take a subscription without payment");
            return $this->redirectToRoute('app_subscription');
        }
       
    }
    
    // Route lorsque le paiement a échoué
    #[Route('/payment-cancel', name: 'app_payment_cancel')]
    public function paymentCancel(): Response
    {
        if($request){
             return $this->render('subscription/payment-cancel.html.twig');
        }
        else {
            $this->addFlash('error', "You can't take a subscription without payment");
            return $this->redirectToRoute('app_subscription');
        }
    }
    
    // Page de présentation de l'abonnement Premium
    #[Route('/subscription/', name: 'app_subscription', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('subscription/index.html.twig',[
            'controller_name' => 'SubscriptionController',
        ]);
    }

    /**
     * Redirection vers le paiement Stripe
     * Ici on utilise la classe RedirectResponse de HttpFoundation
     * Cela nous donne accès à la méthos redirect qui génère la requête
     * à partir de la session initié avec PaymentService->askCheckout()
     **/ 
    #[Route('/subscription/checkout', name: 'app_subscription_checkout', methods: ['GET'])]
    public function checkout(PaymentService $ps): RedirectResponse
    {
        return $this->redirect($ps->askCheckout()->url);
    }
}

<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ParserController extends AbstractDashboardController
{
//сделать роут до /parser
    #[Route('/', name: 'admin')]
    public function main(): Response
    {
        return $this->redirectToRoute('/parser');
    }

    #[Route('/parser', name: 'admin')]
    public function index(): Response
    {
        $form = $this->createFormBuilder()
            ->add('URL', TextType::class)
            ->add('Search', SubmitType::class)
            ->getForm();

        return $this->render('admin/form.html.twig', [
            'form' => $form->createView(),
    ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Parser for Ozon');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('parser', 'fa fa-home');
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}

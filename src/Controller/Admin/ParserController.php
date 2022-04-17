<?php

namespace App\Controller\Admin;

use App\Service\ParserService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;

class ParserController extends AbstractDashboardController
{
//сделать роут до /parser
    #[Route('/', name: 'admin')]
    public function main(): Response
    {
        return $this->redirectToRoute('/parser');
    }

    #[Route('/parser', name: 'admin')]
    public function form(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('URL', TextType::class)
            ->add('Search', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            //action
            $url = $request->request->all('form')['URL'];
            if ($url = filter_var($url, FILTER_VALIDATE_URL)) {
                $n = new ParserService();
                $n->collect($url);
                return $n;
            } else echo 0;

//            $client = new Client();
//            $new = $client->get($url);
//            $crawler = new Crawler($new->getBody()->getContents());

        }
        $url = ' ';
        return $this->render('admin/form.html.twig', [
            'form' => $form->createView(),
            'url' => $url,
    ]);
    }

    public function collectData()
    {

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

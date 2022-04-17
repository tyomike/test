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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;
use App\Entity\Product;
use App\Entity\Seller;

class ParserController extends AbstractDashboardController
{

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

//сделать роут до /parser
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->redirect('/parser');
    }

    #[Route('/parser', name: 'admin')]
    public function form(Request $request)
    {
        $displayCount = '';
        $error = '';
        $ParserService = new ParserService($this->em);
        $form = $this->createFormBuilder()
            ->add('URL', TextType::class)
            ->add('Search', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            //action
            $url = $request->request->all('form')['URL'];
            if ($url = filter_var($url, FILTER_VALIDATE_URL)) {
                $displayCount = $ParserService->collect($url);
            } else $error = 'URL has no valid items.';

//            $client = new Client();
//            $new = $client->get($url);
//            $crawler = new Crawler($new->getBody()->getContents());

        }
        return $this->render('admin/form.html.twig', [
            'form' => $form->createView(),
            'productsCount' => $displayCount,
            'error' => $error,
        ]);
    }

    public function displayData()
    {

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Parser for Ozon');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Parser', 'fa fa-home', '/parser');
        yield MenuItem::linkToCrud('Products', 'fas fa-list', Product::class);
        yield MenuItem::linkToCrud('Sellers', 'fas fa-list', Seller::class);
    }
}

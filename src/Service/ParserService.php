<?php

namespace App\Service;

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

class ParserService
{
    public function collect(string $url)
    {
        $client = new Client();
        $new = $client->get($url);
        $crawler = new Crawler($new->getBody()->getContents());

        $tag = $crawler->filterXPath('//*[@id="state-searchResultsV2-252189-default-1"]')->outerHtml();
        $cut = strstr($tag, '{"items');
        $encode = strstr($cut, '\'></div>', true);
        $encode = json_decode($encode, true)['items'];
        foreach ($encode as $test) {
            dd($test);
//            dd($price = $test['mainState'][0]['atom']['price']['price']);
//            dd($name = $test['mainState'][3]['atom']['textAtom']['text']);
//            dd($sku = $test['topRightButtons']['0']['favoriteProductMolecule']['sku']);
//            dd($review_count = $test['mainState'][4]['atom']['rating']['count']);
        }
//        $products = [];
//
//        for ($i = 0; $i < $count; $i++){
//            foreach ()
//        }

        return $url;
    }
}

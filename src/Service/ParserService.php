<?php

namespace App\Service;

use App\Controller\Admin\ParserController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use PHPMD\Parser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;

class ParserService extends ParserController
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
        $count = count($encode);
        $displayCount = 'Общее количество товаров: ' . $count;
        $productData = [];
        for ($i = 0; $i < $count; $i++) {
            foreach ($encode as $test) {
//                dd($test);
                $price= $test['mainState'][0]['atom']['price']['price'];
//                $name = $test['mainState'][3]['atom']['textAtom']['text'];
                $sku = $test['topRightButtons'][0]['favoriteProductMolecule']['sku'];
//                $review_count = $test['mainState'][4]['atom']['rating']['count'];

                $productData = [
                    'price' => $price,
//                    'name' => $name,
                    'sku' => $sku,
//                    'review_count' => $review_count,
                ];
            }
        }

        return $displayCount;
    }
}

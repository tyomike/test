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
            foreach ($encode as $data) {
//                dd($encode);
                if (isset($data['multiButton']['ozonSubtitle']['textAtomWithIcon']['text'])) {
//                dd($test);
                    $price = str_replace([' ₽', ' '], '', $data['mainState'][0]['atom']['price']['price']);
                    $name = $this->namePath($data);
                    $sku = $data['topRightButtons'][0]['favoriteProductMolecule']['sku'];
                    $review_count = $this->reviewChecker($data);
                    $seller = $data['multiButton']['ozonSubtitle']['textAtomWithIcon']['text'];
                    $seller = strstr($seller, 'продавец');
                    $seller = strip_tags($seller);
                    $seller = str_replace('продавец ', '', $seller);

                    $productData = [
                        'price' => $price,
                        'name' => $name,
                        'sku' => $sku,
                        'seller' => $seller,
                        'review_count' => $review_count,
                    ];
                    dd($productData);
                }
            }
        }
        return $displayCount;
    }

    public function namePath($data)
    {
        if (!isset($data['mainState'][3]['atom']['textAtom']['text'])) {
            return $data['mainState'][2]['atom']['textAtom']['text'];
        }
        return $data['mainState'][3]['atom']['textAtom']['text'];
    }

    public function reviewChecker($data)
    {
        if ($data['mainState'][4]['atom']['rating']['count'] !== null) {
            return (int)$data['mainState'][4]['atom']['rating']['count'];
        }
        return 0;
    }
}

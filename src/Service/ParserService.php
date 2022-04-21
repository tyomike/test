<?php

namespace App\Service;

use App\Controller\Admin\ParserController;
use App\Entity\Product;
use App\Entity\Seller;
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
use Doctrine\ORM\EntityManagerInterface;

class ParserService
{

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function collect(string $url)
    {
//new Guzzle client
        $client = new Client();
        $new = $client->get($url);
        $crawler = new Crawler($new->getBody()->getContents());
//Crawler
//        $tag = $crawler->filterXPath("//*[contains(concat(' ', @id, ' '), 'state-searchResultsV2')]")->outerHtml();
//        $tag = str_replace(['\'></div>', '<div id="state-searchResultsV2', '-252189-default-1" ', '-1304093-default-1" ', 'data-state=\''], '', $tag);
        $tag = $crawler->filterXPath('//*[@id="state-searchResultsV2-252189-default-1"]')->outerHtml();
        $cut = strstr($tag, '{"items');
        $encode = strstr($cut, '\'></div>', true);
        $encode = json_decode($encode, true)['items'];
        $count = count($encode);
        $displayCount = 'Общее количество товаров: ' . $count;

        for ($i = 0; $i < $count; $i++) {
            foreach ($encode as $data) {
//                dd($encode);
                if (isset($data['multiButton']['ozonSubtitle']['textAtomWithIcon']['text'])) {
//                dd($test);
                    $price = str_replace([' ₽', ' '], '', $data['mainState'][0]['atom']['price']['price']);
                    $name = $this->namePath($data);
                    $sku = $data['topRightButtons'][0]['favoriteProductMolecule']['sku'];
                    $review_count = $this->reviewChecker($data['mainState']);
                    $seller = $data['multiButton']['ozonSubtitle']['textAtomWithIcon']['text'];
                    $seller = strip_tags($seller);
                    $seller = stristr($seller, 'продавец');
                    $seller = str_replace('продавец ', '', $seller);

                    $productData = [
                        'price' => $price,
                        'name' => $name,
                        'sku' => $sku,
                        'seller' => $seller,
                        'review_count' => $review_count,
                    ];
//                    dd($productData);
                }
            }
        }
        $this->saveProduct($productData);

        return $displayCount;
    }

    public function namePath($data)
    {
        foreach ($data['mainState'] as $atom) {
            if ($atom['id'] === 'name') {
                return $atom['atom']['textAtom']['text'];
            }
        }
        return null;
    }

    public function reviewChecker($data)
    {
        foreach ($data as $value) {
            if ($value['atom']['type'] === 'rating') {
                return (int)$value['atom']['rating']['count'];
            }
        }
        return 0;
    }

    public function saveProduct(array $productData)
    {
        $em = $this->em;

            $seller = $em->getRepository(Seller::class)->findOneBy(['name' => $productData['seller']]);
            if (empty($seller)) {
                $seller = new Seller;
                $seller->setName($productData['seller']);
            }
            $em->persist($seller);
            $em->flush();

            $product = $em->getRepository(Product::class)->findOneBy(['sku' => $productData['sku']]);
            if (empty($product)) {
                $product = new Product;
                $product->setName($productData['name']);
                $product->setPrice($productData['price']);
                $product->setSku($productData['sku']);
                $product->setReviewsCount($productData['review_count']);
                $product->setSeller($seller);
            }
            $em->persist($product);
            $em->flush();

        return $productData;
    }
}

<?php

namespace App;

use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\EmptyCollectionException;

class Main
{
    protected $csrf = null;
    public string $txt;

    public function __construct($txt)
    {
        $this->txt = $txt;

        list($ch, $result) = $this->getCsrfToken();
        $this->setCsrf($result);
    }

    public function getCsrfToken()
    {
        return $this->request();
    }


    public function dispatch()
    {
        list($ch, $result) = $this->request();
        $url = $this->getPageUrl($ch);

        return $this->parsePage($url);
    }

    public function parsePage($main_url)
    {
        $main_dom = new Dom;
        $main_dom->loadFromUrl($main_url);


        try {
            $last_page = $main_dom->find('.goto-last-page')->getAttribute('data-gotopage');

        } catch (EmptyCollectionException $e) {
            $last_page = 0;
        }

        $product_array = [];

        for ($i = 0; $i <= $last_page; $i++) {

            $url = $main_url . '&p=' . $i;

            $dom = new Dom;
            $dom->loadFromUrl($url);

            $number_collection = $dom->find('.qa-tm-number');
            $classes_collection = $dom->find('.classes');
            $words_collection = $dom->find('.words');


            foreach ($number_collection as $k => $number_item) {

                $number = $number_item->text;
                $name = trim($words_collection[$k]->text);
                $classes = trim($classes_collection[$k]->text);
                $details_page_url = $number_item->getAttribute('href');

                $image = $dom->find('#TM' . $number_item->text . ' .image > img');
                $status = isset($dom->find('#TM' . $number_item->text . ' .status span')[0])
                    ? trim($dom->find('#TM' . $number_item->text . ' .status span')->text) : 'Status not available';

                try {
                    $logo_url = $image->getAttribute('src');
                } catch (EmptyCollectionException $e) {
                    $logo_url = '';
                }

                $product_array[] =
                    ['number' => $number,
                        'logo_url' => $logo_url,
                        'name' => $name,
                        'classes' => $classes,
                        'status' => $status,
                        'details_page_url' => 'https://search.ipaustralia.gov.au' . $details_page_url,
                        'page' => $i + 1,
                        'page_url' => $url
                    ];
            }
        }

        return json_encode([
            'count' => count($product_array),
            'data' => $product_array
        ]);

    }

    private function setCsrf($result)
    {
        $main_dom = new Dom;
        $main_dom->loadStr($result);
        $csrf = $main_dom->find('meta[name="_csrf"]')->getAttribute('content');
        $this->csrf = $csrf;
    }

    public function getPageUrl($ch)
    {
        $result = curl_getinfo($ch);

        return $result['redirect_url'];
    }

    public function request()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://search.ipaustralia.gov.au/trademarks/search/doSearch?_csrf=" .
            $this->csrf);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/../' . '/cookie.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/../' . '/cookie.txt');
        curl_setopt($ch, CURLOPT_POSTFIELDS, "wv[0]=" . $this->txt);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        $result = curl_exec($ch);

        return [$ch, $result];

    }
}
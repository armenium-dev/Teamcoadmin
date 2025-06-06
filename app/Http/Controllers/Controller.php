<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Shopify\Shopify;
use App\Settings;
use Illuminate\Support\Facades\DB;
use App\ProductShopify;

class Controller extends BaseController
{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $shopify;
    private $update_interval = 86400;

    public function __construct()
    {
        $this->shopify = new Shopify;
        #$this->updateShopifyProductsTable();
    }

    public function getShopifyProductsCount()
    {
        $count_url = '/admin/products/count';
        $result = $this->shopify->get($count_url);

        return $result->count;
    }

    public function getShopifyProducts()
    {
        $limit = 250;
        $shopify_products = [];

        $products_url = "/admin/products.json?since_id={since_id}&limit={$limit}&fields=id,title";
        $pages_count = ceil($this->getShopifyProductsCount() / $limit);

        $since_id = 0;
        for ($i = 1; $i <= $pages_count; $i++) {
            $url = str_replace('{since_id}', $since_id, $products_url);
            $result = $this->shopify->get($url);
            foreach ($result->products as $product) {
                $shopify_products[$product->id] = $product->title;
                $since_id = $product->id;
            }
        }

        #dd($shopify_products);

        return $shopify_products;
    }

    public function updateShopifyProductsTable()
    {
        $last_update = Settings::get('products_shopify_last_update');
        $update_interval = Settings::get('products_shopify_update_interval');

        $shopify_products_count = $this->getShopifyProductsCount();
        $products_shopify = ProductShopify::all(['shopify_id', 'title']);

        if ($last_update == 0 || time() > ($last_update + $update_interval) || $shopify_products_count != $products_shopify->count()) {
            $shopify_products = $this->getShopifyProducts();

            $tmp_products_shopify = $products_shopify->toArray();
            $products_shopify = [];
            foreach ($tmp_products_shopify as $product)
                $products_shopify[$product['shopify_id']] = $product['title'];


            if ($shopify_products) {
                foreach ($shopify_products as $product_id => $product_name) {
                    if (in_array($product_id, array_keys($products_shopify))) {
                        if ($products_shopify[$product_id] != $product_name) {
                            ProductShopify::where(['shopify_id' => $product_id])->update(['title' => $product_name]);
                        }
                        unset($products_shopify[$product_id]);
                    } else {
                        ProductShopify::create(['shopify_id' => $product_id, 'title' => $product_name]);
                    }
                }

                if (!empty($products_shopify)) {
                    $shopify_ids = array_keys($products_shopify);
                    ProductShopify::whereIn('shopify_id', $shopify_ids)->delete();
                }

            }

            if (empty($update_interval)) $update_interval = $this->update_interval;
            $last_update = time();

            Settings::set('products_shopify_last_update', $last_update);
            Settings::set('products_shopify_update_interval', $update_interval);
        }

    }
}

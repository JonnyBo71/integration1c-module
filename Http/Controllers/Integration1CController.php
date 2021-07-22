<?php

namespace Modules\Integration1C\Http\Controllers;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use App\Altrp\Model;
use App\Altrp\Column;
use App\Altrp\Table;
use CommerceML\Client;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Integration1C\Entities\Category;
use Modules\Integration1C\Entities\Functions;
use Modules\Integration1C\Entities\GenerateCommerceML;
use Modules\Integration1C\Entities\Guid;
use Modules\Integration1C\Entities\Offers;
use Modules\Integration1C\Entities\ParserCommerceML;
use Modules\Integration1C\Entities\PriceType;
use Modules\Integration1C\Entities\Products;
use Modules\Integration1C\Entities\Store;
use Modules\Integration1C\Entities\Unit;
use CommerceML\Nodes\CommercialInformation;
use Illuminate\Support\Facades\View;

class Integration1CController extends Controller
{

    public $config;

    public $functions;

    public $parser;

    public function __construct() {
      $this->config = require module_path('Integration1C','Config/config.php');
      $this->parser = new ParserCommerceML();
    }


    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {

      $method = $request->method();
      if ($request->isMethod('post')) {
        $posts = $request->all();
        dd($posts);
      }

      if (!file_exists(storage_path('app/public/1C/import') . '/import.xml')) {
        throw new \Exception('Нет файлов для импорта!', 404);
      }

      $model = new Model();

      $models = $model->all()->toArray();

      $this->parseFiles();

      $catalog = $this->parser->getCatalog();
      $guid = new Guid();
      $guids = $guid->getItems($catalog->owner->classifier->properties);
      //dd($guids);
      $config = $this->config;
      //dd($config);
      return View::make('integration1c::index', compact('models', 'guids', 'config'));
    }

    protected function parseFiles() {
      $importDir = storage_path('app/public/1C/import');
      $importFiles = Functions::getXMLFiles($importDir);
      if (!empty($importFiles)) {
        $this->parser->parseCommerceML($importFiles);
      }
    }

    public function import() {
      set_time_limit(0);
      $this->parseFiles();

      if ($catalog = $this->parser->getCatalog()) {
        //dd($catalog);
        //dd($this->parser->getOffers());
        if ($this->config['categoryModelId']) {
          $category = new Category();
          $category->getCategories($catalog->owner->classifier->groups);
          $category->save();
        }
        if ($this->config['unitModelId']) {
          $unit = new Unit();
          $unit->getItems($catalog->owner->classifier->xml->ЕдиницыИзмерения);
          $unit->save();
        }
        if ($this->config['pricetypeModelId']) {
          $pricetype = new PriceType();
          $pricetype->getItems($catalog->owner->classifier->xml->ТипыЦен);
          $pricetype->save();
        }
        if ($this->config['storeModelId']) {
          $store = new Store();
          $store->getItems($catalog->owner->classifier->xml->Склады);
          $store->save();
        }
        if ($this->config['guidModelId']) {
          $guid = new Guid();
          $guid->getItems($catalog->owner->classifier->properties);
          $guid->save();
        }

        if ($this->config['productModelId']) {
          $product = new Products();
          $product->getItems($catalog->products);
          $product->save();
        }

        if ($this->config['offerModelId']) {
          if ($offers = $this->parser->getOffers()) {
            $offer = new Offers();
            $offer->getItems($offers->offers);
            $offer->save();
          }
        }

        return \Redirect::to('plugins/integration1c')->withSuccess('Данные загружены');
        //dd($catalog);

      }
    }

    public function export(Request $request) {
      //сделать заполнение заказа

      $view = view('integration1c::order');
      $commerceML = Client::toCommerceML($view->render());
      $string = Client::toString($commerceML);
      $exportFile = storage_path('app/public/1C/export') . '/order.xml';
      file_put_contents($exportFile, $string);
    }

    public function guids() {
      $result = [];
      if ($catalog = $this->getCatalog()) {
        $guid = new Guid();
        $result = $guid->getItems($catalog->owner->classifier->properties);
      }
      return json_encode($result);
    }

    public function products(Request $request) {

      $user = $request->all();
      $categories = [];
      if (isset($user['client_id']) && $user['client_id'] > 0) {
        $currentUser = \DB::table('users')->where('id', $user['client_id'])->first();
        if ($currentUser->category)
          $categories = explode(',', trim($currentUser->category));
      }
      $result = [];

      $productModel = Model::find($this->config['productModelId']);
      $className = $productModel->namespace;
      $productModel = new $className;

      $conn = Model::find($this->config['connections']['product_category']);
      $className = $conn->namespace;
      $connectionModel = new $className;

      $connOffers = Model::find($this->config['connections']['product_offer']);
      $className = $conn->namespace;
      $connectionOfferModel = new $className;

      $products = $productModel->all()->toArray();
      $cat = new Category();
      $offer = new Offers();
      if (!empty($products)) {
        foreach ($products as $product) {
          //dd($product);
          $prod = [];
          $product['parentCategory'] = null;
          if ($connModel = $connectionModel->where('product_id', $product['id'])->first()) {
            $category_id = $connModel->category_id;
            if ($category_id) {
              $parentCategory = $cat->getParentCategory($category_id);
              $product['parentCategory'] = $parentCategory['name'];
              $product['parentCategoryId'] = $parentCategory['id'];
            }
          }
          $offers = $offer->getOffersByProductId($product['id']);
          $product['quantity'] = $offer->getMaxQuantity($offers);
          if (empty($categories) || (!empty($categories) && in_array($product['parentCategoryId'], $categories))) {
            $prod['name'] = $product['name'];
            $prod['part_number'] = $product['part_number'];
            $prod['category'] = $product['parentCategory'];
            $prod['quantity'] = $product['quantity'];
            $result[] = $prod;
          }
        }
      }
      return json_encode($result);
    }

    public function getSetting() {
      $guidCurrent = Model::find($this->config['guidModelId']);
      $className = $guidCurrent->namespace;
      $itemModel = new $className;
      //dd($itemModel->all()->toArray());
      $this->config['guid'] = $itemModel->all()->toArray();
      return json_encode($this->config);
    }

    protected function array_to_string($array) {
      $data = "\n";
      foreach ($array as $key => $value) {
        if (is_array($value))
          $data = $data . "   '" . $key . "' => [" . $this->array_to_string($value) . "],\n";
        else
          $data = $data . "   '" . $key . "' => '" . $value . "',\n";
      }
      return $data;
    }

    protected function clearConfig() {
      foreach ($this->config as $key => $conf) {

      }
    }

    public function setSetting(Request $request) {
      $posts = $request->all();
      //dd($posts);
      foreach ($posts as $key => $post) {
        if (isset($this->config[$key])) {
          if ($post) {
            if (is_array($post)) {
              //if (!empty($this->config[$key])) {
              foreach ($post as $k => $v) {
                $this->config[$key][$k] = $v;
              }
            } else {
              $this->config[$key] = $post;
            }
          } else {

          }
        }
      }
      $data = print_r($this->config, true);
      $data = str_replace('[', "'", $data);
      $data = str_replace(']', "'", $data);
      //dd($data);
      //dd($this->array_to_string($this->config));
      $conf = "<?php\n
      return [" . $this->array_to_string($this->config) . "];\n
      ";
      $fx = fopen(module_path('Integration1C', 'Config/config.php'), 'w');
      fwrite($fx, $conf);
      fclose($fx);
      return \Redirect::to('plugins/integration1c');
      //return json_encode('ok');
    }
}

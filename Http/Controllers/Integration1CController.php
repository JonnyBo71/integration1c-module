<?php

namespace Modules\Integration1C\Http\Controllers;

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
      $model = new Model();
      $models = $model->all()->toArray();
      $this->parseFiles();
      $catalog = $this->parser->getCatalog();
      $guid = new Guid();
      $guids = $guid->getItems($catalog->owner->classifier->properties);
      $config = $this->config;
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

      $this->parseFiles();

      if ($catalog = $this->parser->getCatalog()) {
        //dd($catalog->offers);
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


        dd($catalog);

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

    public function setSetting(Request $request) {
      $posts = $request->all();
      foreach ($posts as $key => $post) {
        if (isset($this->config[$key])) {
          if (is_array($this->config[$key])) {
            foreach ($this->config[$key] as $k => $v) {
              $this->config[$key][$k] = $v;
            }
          } else {
            $this->config[$key] = $post;
          }
        }
      }
      $data = print_r($this->config, true);
      $data = str_replace('[', "'", $data);
      $data = str_replace(']', "'", $data);
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

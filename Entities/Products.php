<?php


namespace Modules\Integration1C\Entities;


use App\Altrp\Model;
use App\User;
use Illuminate\Http\Request;
use \Zenwalker\CommerceML\CommerceML;
use \Zenwalker\CommerceML\Model\OfferPackage;
use \Zenwalker\CommerceML\Model\Product;
use \Zenwalker\CommerceML\Model\Offer;

class Products
{

  public $products = [];

  public function __construct() {
    $this->config = require module_path('Integration1C','Config/config.php');
  }

  public function getItems($products) {
    if (!empty($products)) {
      foreach ($products as $product) {
          $this->products[] = ['name' => (string)$product->xml->Наименование, 'id_1c' => (string)$product->xml->Ид, 'barcode' => (string)$product->xml->Штрихкод, 'part_number' => (string)$product->xml->Артикул, 'site_number' => (string)$product->xml->АртикулСайта, 'description' => (string)$product->xml->Описание, 'units' => $product->xml->БазоваяЕдиница, 'category' => $product->xml->Группы, 'property' => $product->xml->ЗначенияСвойств, 'images' => $product->xml->Картинка];
      }
    }
  }

  protected function saveCategory($categories, $product_id) {
    $category = new Category();
    $conn = Model::find($this->config['connections']['product_category']);
    $className = $conn->namespace;
    $connectionModel = new $className;
    if (!empty($categories)) {
      foreach ($categories as $cat) {
        $category_id = $category->getIdBy1cId((string)$cat->Ид);

        $currentModel = $connectionModel->where([['product_id', $product_id], ['category_id', $category_id]])->first();
        if ($currentModel === null) {
          $connectionModel->product_id = $product_id;
          $connectionModel->category_id = $category_id;
          $connectionModel->parent_category_id = $category->getParentCategory($category_id);
          $connectionModel->save();
        } else {
		  $currentModel->parent_category_id = $category->getParentCategory($category_id);
          $currentModel->save();
        }
      }
    }
  }

  protected function saveUnit($units, $product_id) {
    $unit = new Unit();
    $conn = Model::find($this->config['connections']['product_unit']);
    $className = $conn->namespace;
    $connectionModel = new $className;

  }

  protected function saveProperty($property, $product_id) {
    if (!empty($property->ЗначенияСвойства)) {
      $conn = Model::find($this->config['connections']['product_property']);
      $className = $conn->namespace;
      foreach ($property->ЗначенияСвойства as $prop) {
        $connectionModel = new $className;
        $guid = new Guid();
        $currentGuid = $guid->findGuidBy((string)$prop->Ид);
        if ($connectionModel->where([['product_id', $product_id], ['guid_id', $currentGuid->id]])->first() === null) {
          $connectionModel->product_id = $product_id;
          $connectionModel->guid_id = $currentGuid->id;
          if ($currentGuid->type == 'Справочник') {
            $guid = $guid->getPropertyBy1cId((string)$prop->Значение, $currentGuid->alias);
            if ($guid) {
              $connectionModel->property_id = $guid->id;
            }
          } else {
            $connectionModel->property_value = (string)$prop->Значение;
          }
          $connectionModel->save();
        }
      }
    }
  }

  protected function saveImage($images, $product_id) {
    Functions::addNewField('product_id', 'bigInteger', $this->config['imageModelId']);
    Functions::addNewField('filename', 'string', $this->config['imageModelId']);
    Functions::addNewField('fileurl', 'string', $this->config['imageModelId']);
    $image = Model::find($this->config['imageModelId']);
    $className = $image->namespace;
    $imageModel = new $className;
    if (!empty($images)) {
      foreach ($images as $img) {
        if (file_exists(storage_path('app/public/1C/import/') . (string) $img)) {
          $imageUrl = '/storage/1C/import/' . (string) $img;
          $imageFile = '1C/import/' . (string) $img;
          if ($imageModel->where([['product_id', $product_id], ['filename', $imageFile]])->first() === null) {
            $imageModel->product_id = $product_id;
            $imageModel->filename = $imageFile;
            $imageModel->fileurl = $imageUrl;
            $imageModel->save();
          }
        }
      }
    }
  }

  protected function saveOffers() {

  }

  public function save() {
    Functions::addNewField('name', 'string', $this->config['productModelId']);
    Functions::addNewField('part_number', 'string', $this->config['productModelId']);
    Functions::addNewField('site_number', 'string', $this->config['productModelId']);
    Functions::addNewField('id_1c', 'string', $this->config['productModelId']);
    Functions::addNewField('price', 'float', $this->config['productModelId']);
    Functions::addNewField('unit', 'string', $this->config['productModelId']);
    Functions::addNewField('barcode', 'string', $this->config['productModelId']);
    Functions::addNewField('description', 'longText', $this->config['productModelId']);
    Functions::addNewField('created_at', 'timestamp', $this->config['productModelId']);
    Functions::addNewField('updated_at', 'timestamp', $this->config['productModelId']);
    Functions::addNewField('parent_category', 'bigInteger', $this->config['productModelId']);
    $modelCurrent = Model::find($this->config['productModelId']);
    $className = $modelCurrent->namespace;
    if (!empty($this->products)) {
      foreach ($this->products as $product) {
        //dd($product);
        $productModel = new $className;
        $currentProduct = $productModel->where('id_1c', $product['id_1c'])->first();
        if ($currentProduct === null) {
          //новый товар
          $productModel->name = $product['name'];
          $productModel->id_1c = $product['id_1c'];
          $productModel->description = $product['description'];
          $productModel->barcode = $product['barcode'];
          $productModel->part_number = $product['part_number'];
          $productModel->site_number = $product['site_number'];
          $productModel->created_at = date('Y-m-d H:i:s');
          $productModel->updated_at = date('Y-m-d H:i:s');
          $productModel->save();
          $this->saveCategory($product['category'], $productModel->id);
          $this->saveProperty($product['property'], $productModel->id);
          $this->saveImage($product['images'], $productModel->id);
        } else {
          $currentProduct->name = $product['name'];
          //$currentProduct->id_1c = $product['id_1c'];
          $currentProduct->description = $product['description'];
          $currentProduct->barcode = $product['barcode'];
          $currentProduct->part_number = $product['part_number'];
          $currentProduct->site_number = $product['site_number'];
          //$currentProduct->created_at = date('Y-m-d H:i:s');
          $currentProduct->updated_at = date('Y-m-d H:i:s');
          $currentProduct->save();
          $this->saveCategory($product['category'], $currentProduct->id);
          $this->saveProperty($product['property'], $currentProduct->id);
          $this->saveImage($product['images'], $currentProduct->id);
        }
      }
    }
  }

  public function getProductIdByOfferId($id) {
    $ppoductId = explode('#', $id)[0];
    $modelCurrent = Model::find($this->config['productModelId']);
    $className = $modelCurrent->namespace;
    $productModel = new $className;
    if ($product = $productModel->where('id_1c', $ppoductId)->first())
      return $product->id;
    return null;
  }

}

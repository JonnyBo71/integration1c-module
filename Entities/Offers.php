<?php


namespace Modules\Integration1C\Entities;


use App\Altrp\Model;
use App\User;
use Illuminate\Http\Request;
use \Zenwalker\CommerceML\CommerceML;
use \Zenwalker\CommerceML\Model\OfferPackage;
use \Zenwalker\CommerceML\Model\Product;
use \Zenwalker\CommerceML\Model\Offer;

class Offers
{

  public $offer = [];

  public function __construct() {
    $this->config = require module_path('Integration1C','Config/config.php');
  }

  public function getItems($offers) {
    if (!empty($offers)) {
      foreach ($offers as $offer) {
          $this->offers[] = ['name' => (string)$offer->xml->Наименование, 'id_1c' => (string)$offer->xml->Ид, 'part_number' => (string)$offer->xml->Артикул, 'units' => $offer->xml->БазоваяЕдиница, 'property' => $offer->xml->ХарактеристикиТовара, 'price' => $offer->xml->Цены, 'quantity' => $offer->xml->Количество, 'store' => $offer->xml->Склад];
      }
    }
    //dd($this->offers);
  }

  protected function saveStore($store, $offer_id) {
    $storage = new Store();
    $conn = Model::find($this->config['connections']['product_store']);
    $className = $conn->namespace;
    $connectionModel = new $className;
    if (!empty($store)) {
      $store_id = $storage->getIdBy1cId((string)$store->attributes()->ИдСклада);
      if ($connectionModel->where([['offer_id', $offer_id], ['store_id', $store_id]])->first() === null && $store_id) {
        $connectionModel->offer_id = $offer_id;
        $connectionModel->store_id = $store_id;
        $connectionModel->save();
      }
    }
  }

  protected function savePriceType($price, $offer_id) {
    $priceType = new PriceType();
    $conn = Model::find($this->config['connections']['product_pricetype']);
    $className = $conn->namespace;
    $connectionModel = new $className;
    if (!empty($price)) {
      $pricetype_id = $priceType->getIdBy1cId((string)$price->Цены->ИдТипаЦены);
      if ($connectionModel->where([['offer_id', $offer_id], ['pricetype_id', $pricetype_id]])->first() === null && $pricetype_id) {
        $connectionModel->offer_id = $offer_id;
        $connectionModel->pricetype_id = $pricetype_id;
        $connectionModel->save();
      }
    }
  }

  protected function saveProductConnection($id_1c, $offer_id) {
    $productModel = new Products();
    $product_id = $productModel->getProductIdByOfferId($id_1c);
    $conn = Model::find($this->config['connections']['product_offer']);
    $className = $conn->namespace;
    $connectionModel = new $className;
    if ($connectionModel->where([['offer_id', $offer_id], ['product_id', $product_id]])->first() === null && $product_id) {
      $connectionModel->offer_id = $offer_id;
      $connectionModel->product_id = $product_id;
      $connectionModel->save();
    }
  }

  public function save() {
    Functions::addNewField('name', 'string', $this->config['offerModelId']);
    Functions::addNewField('part_number', 'string', $this->config['offerModelId']);
    Functions::addNewField('id_1c', 'string', $this->config['offerModelId']);
    Functions::addNewField('price', 'float', $this->config['offerModelId']);
    Functions::addNewField('unit', 'string', $this->config['offerModelId']);
    Functions::addNewField('created_at', 'timestamp', $this->config['offerModelId']);
    Functions::addNewField('updated_at', 'timestamp', $this->config['offerModelId']);
    $modelCurrent = Model::find($this->config['offerModelId']);
    $className = $modelCurrent->namespace;
    if (!empty($this->offers)) {
      foreach ($this->offers as $offer) {
        $offerModel = new $className;
        $currentOffer = $offerModel->where('id_1c', $offer['id_1c'])->first();
        if ($currentOffer === null) {
          $offerModel->name = $offer['name'];
          $offerModel->id_1c = $offer['id_1c'];
          $offerModel->part_number = $offer['part_number'];
          $offerModel->quantity = intval((string)$offer['quantity']);
          $offerModel->price = floatval((string)$offer['price']->Цена->ЦенаЗаЕдиницу);
          $offerModel->unit = (string)$offer['units']->attributes()->НаименованиеПолное;
          $offerModel->created_at = date('Y-m-d H:i:s');
          $offerModel->updated_at = date('Y-m-d H:i:s');
          $offerModel->save();
          $this->saveStore($offer['store'], $offerModel->id);
          $this->savePriceType($offer['price'], $offerModel->id);
          $this->saveProductConnection($offer['id_1c'], $offerModel->id);
        } else {
          $currentOffer->name = $offer['name'];
          $currentOffer->part_number = $offer['part_number'];
          $currentOffer->quantity = intval((string)$offer['quantity']);
          $currentOffer->price = floatval((string)$offer['price']->Цена->ЦенаЗаЕдиницу);
          $currentOffer->unit = (string)$offer['units']->attributes()->НаименованиеПолное;
          $currentOffer->updated_at = date('Y-m-d H:i:s');
          $currentOffer->save();
          $this->saveStore($offer['store'], $currentOffer->id);
          $this->savePriceType($offer['price'], $currentOffer->id);
          $this->saveProductConnection($offer['id_1c'], $currentOffer->id);
        }

      }
    }
  }

}

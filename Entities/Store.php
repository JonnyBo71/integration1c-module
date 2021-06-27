<?php


namespace Modules\Integration1C\Entities;


use App\Altrp\Model;
use App\User;
use Illuminate\Http\Request;
use \Zenwalker\CommerceML\CommerceML;
use \Zenwalker\CommerceML\Model\OfferPackage;
use \Zenwalker\CommerceML\Model\Product;
use \Zenwalker\CommerceML\Model\Offer;

class Store
{

  public $stores = [];

  public function __construct() {
    $this->config = require module_path('Integration1C','Config/config.php');
  }

  public function getItems($stores) {

    if (!empty($stores->Склад)) {
      foreach ($stores->Склад as $store) {
        $this->stores[] = ['name' => (string) $store->Наименование, 'id_1c' => (string) $store->Ид];
      }
    }
  }

  public function save() {
    Functions::addNewField('name', 'string', $this->config['storeModelId']);
    Functions::addNewField('id_1c', 'string', $this->config['storeModelId']);
    $modelCurrent = Model::find($this->config['storeModelId']);
    $className = $modelCurrent->namespace;
    if (!empty($this->stores)) {
      foreach ($this->stores as $store) {
        $storeModel = new $className;
        if ($storeModel->where('id_1c', $store['id_1c'])->first() === null) {
          $storeModel->name = $store['name'];
          $storeModel->id_1c = $store['id_1c'];
          $storeModel->save();
        }
      }
    }
  }

  public function getIdBy1cId($id_1c) {
    $modelCurrent = Model::find($this->config['storeModelId']);
    $className = $modelCurrent->namespace;
    $storeModel = new $className;
    if ($store = $storeModel->where('id_1c', $id_1c)->first()) {
      return $store->id;
    }
    return null;
  }

}

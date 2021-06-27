<?php


namespace Modules\Integration1C\Entities;


use App\Altrp\Model;
use App\User;
use Illuminate\Http\Request;
use \Zenwalker\CommerceML\CommerceML;
use \Zenwalker\CommerceML\Model\OfferPackage;
use \Zenwalker\CommerceML\Model\Product;
use \Zenwalker\CommerceML\Model\Offer;

class PriceType
{

  public $pricetypes = [];

  public function __construct() {
    $this->config = require module_path('Integration1C','Config/config.php');
  }

  public function getItems($pricetypes) {
    if (!empty($pricetypes->ТипЦены)) {
      foreach ($pricetypes->ТипЦены as $pricetype) {
        $this->pricetypes[] = ['name' => (string) $pricetype->Наименование, 'id_1c' => (string) $pricetype->Ид, 'currency' => (string) $pricetype->Валюта];
      }
    }
  }

  public function save() {
    Functions::addNewField('name', 'string', $this->config['pricetypeModelId']);
    Functions::addNewField('currency', 'string', $this->config['pricetypeModelId']);
    Functions::addNewField('id_1c', 'string', $this->config['pricetypeModelId']);
    $modelCurrent = Model::find($this->config['pricetypeModelId']);
    $className = $modelCurrent->namespace;
    if (!empty($this->pricetypes)) {
      foreach ($this->pricetypes as $pricetype) {
        $pricetypeModel = new $className;
        if ($pricetypeModel->where('id_1c', $pricetype['id_1c'])->first() === null) {
          $pricetypeModel->name = $pricetype['name'];
          $pricetypeModel->id_1c = $pricetype['id_1c'];
          $pricetypeModel->currency = $pricetype['currency'];
          $pricetypeModel->save();
        }
      }
    }
  }

  public function getIdBy1cId($id_1c) {
    $modelCurrent = Model::find($this->config['pricetypeModelId']);
    $className = $modelCurrent->namespace;
    $pricetypeModel = new $className;
    if ($pricetype = $pricetypeModel->where('id_1c', $id_1c)->first()) {
      return $pricetype->id;
    }
    return null;
  }

}

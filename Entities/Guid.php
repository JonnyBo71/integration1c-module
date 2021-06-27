<?php


namespace Modules\Integration1C\Entities;


use App\Altrp\Column;
use App\Altrp\Model;
use App\User;
use Illuminate\Http\Request;
use \Zenwalker\CommerceML\CommerceML;
use \Zenwalker\CommerceML\Model\OfferPackage;
use \Zenwalker\CommerceML\Model\Product;
use \Zenwalker\CommerceML\Model\Offer;

class Guid
{

  public $guids = [];

  public function __construct() {
    $this->config = require module_path('Integration1C','Config/config.php');
  }

  public function getItems($guids) {
    if (!empty($guids)) {
      foreach ($guids as $guid) {
        //dd($guid);
        $this->guids[] = ['id_1c' => (string) $guid->xml->Ид, 'name' => (string) $guid->xml->Наименование, 'type' => (string) $guid->xml->ТипЗначений, 'alias' => str_replace('-', '_', \Str::slug($guid->xml->Наименование)), 'items' => $guid->xml->ВариантыЗначений];
      }
    }
    return $this->guids;
  }

  protected function saveGuid($guid) {
    $guidCurrent = Model::find($this->config['guids'][$guid['alias']]);
    $className = $guidCurrent->namespace;
    foreach ($guid['items']->Справочник as $item) {

    }
  }

  protected function saveGuideItems($guid) {
    $guidCurrent = Model::find($this->config['guids'][$guid['alias']]);
    $className = $guidCurrent->namespace;
    if ($guid['type'] == 'Справочник' && !empty($guid['items']->Справочник)) {
      foreach ($guid['items']->Справочник as $item) {
        Functions::addNewField('name', 'string', $this->config['guids'][$guid['alias']]);
        Functions::addNewField('id_1c', 'string', $this->config['guids'][$guid['alias']]);
        $itemModel = new $className;
        if ($itemModel->where('id_1c', (string)$item->ИдЗначения)->first() === null) {
          $itemModel->name = (string)$item->Значение;
          $itemModel->id_1c = (string)$item->ИдЗначения;
          $itemModel->save();
        }
      }
    }
  }

  public function save() {
    if (!empty($this->guids)) {
      Functions::addNewField('name', 'string', $this->config['guidModelId']);
      Functions::addNewField('type', 'string', $this->config['guidModelId']);
      Functions::addNewField('id_1c', 'string', $this->config['guidModelId']);
      Functions::addNewField('alias', 'string', $this->config['guidModelId']);
      $guidCurrent = Model::find($this->config['guidModelId']);
      $className = $guidCurrent->namespace;
      foreach ($this->guids as $guid) {
        $guidModel = new $className;
        if ($guidModel->where('id_1c', $guid['id_1c'])->first() === null) {
          $guidModel->name = $guid['name'];
          $guidModel->id_1c = $guid['id_1c'];
          $guidModel->type = $guid['type'];
          $guidModel->alias = $guid['alias'];
          $guidModel->save();
        }
        if (array_key_exists($guid['alias'], $this->config['guids'])) {
          $this->saveGuideItems($guid);
        } else {
          //не задан справочник

        }
      }
    }
  }

  public function findGuidBy($id_1c) {
    $guidCurrent = Model::find($this->config['guidModelId']);
    $className = $guidCurrent->namespace;
    $guidModel = new $className;
    return $guidModel->where('id_1c', $id_1c)->first();
  }

  public function getModelGuidByAlias($alias) {
    if (array_key_exists($alias, $this->config['guids'])) {
      $guidCurrent = Model::find($this->config['guids'][$alias]);
      $className = $guidCurrent->namespace;
      return new $className;
    }
    return null;
  }

  public function getPropertyBy1cId($id_1c, $alias) {
    $guidModel = $this->getModelGuidByAlias($alias);
    return $guidModel->where('id_1c', $id_1c)->first();
  }

}

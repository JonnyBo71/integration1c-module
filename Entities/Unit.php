<?php


namespace Modules\Integration1C\Entities;


use App\Altrp\Model;
use App\User;
use Illuminate\Http\Request;
use \Zenwalker\CommerceML\CommerceML;
use \Zenwalker\CommerceML\Model\OfferPackage;
use \Zenwalker\CommerceML\Model\Product;
use \Zenwalker\CommerceML\Model\Offer;

class Unit
{

  public $units = [];

  public function __construct() {
    $this->config = require module_path('Integration1C','Config/config.php');
  }

  public function getItems($units) {
    if (!empty($units)) {
      if (is_array($units->ЕдиницаИзмерения)) {
        foreach ($units->ЕдиницаИзмерения as $unit) {
          $this->units[] = ['name' => (string)$unit->xml->НаименованиеКраткое, 'id_1c' => (string)$unit->xml->Ид, 'code' => $unit->xml->Код, 'items' => $unit->xml->ЕдиницаИзмерения];
        }
      } else {
        $this->units[] = ['name' => (string)$units->ЕдиницаИзмерения->НаименованиеКраткое, 'id_1c' => (string)$units->ЕдиницаИзмерения->Ид, 'code' => $units->ЕдиницаИзмерения->Код,];
      }
    }
  }

  public function save() {
    Functions::addNewField('name', 'string', $this->config['unitModelId']);
    Functions::addNewField('id_1c', 'string', $this->config['unitModelId']);
    Functions::addNewField('code', 'string', $this->config['unitModelId']);
    $modelCurrent = Model::find($this->config['unitModelId']);
    $className = $modelCurrent->namespace;
    if (!empty($this->units)) {
      foreach ($this->units as $unit) {
        $unitModel = new $className;
        if ($unitModel->where('id_1c', $unit['id_1c'])->first() === null) {
          $unitModel->name = $unit['name'];
          $unitModel->code = $unit['code'];
          $unitModel->id_1c = $unit['id_1c'];
          $unitModel->save();
        }
      }
    }
  }

}

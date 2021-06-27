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

class Category
{

  public $categories = [];

  public function __construct() {
    $this->config = require module_path('Integration1C','Config/config.php');
  }

  public function getCategories($groups, $parent = null) {
    if (!empty($groups)) {
      foreach ($groups as $group) {
        $this->categories[] = ['name' => (string) $group->xml->Наименование, 'id_1c' => (string) $group->xml->Ид, 'parent_id' => $parent];
        if ($group->children) {
          $this->getCategories($group->children, (string) $group->xml->Ид);
        }
      }
    }
  }

  public function save() {
    Functions::addNewField('name', 'string', $this->config['categoryModelId']);
    Functions::addNewField('parent_id', 'bigInteger', $this->config['categoryModelId']);
    Functions::addNewField('id_1c', 'string', $this->config['categoryModelId']);
    $modelCurrent = Model::find($this->config['categoryModelId']);
    $className = $modelCurrent->namespace;
    foreach ($this->categories as $category) {
      $categoryModel = new $className;
      if ($categoryModel->where('id_1c', $category['id_1c'])->first() === null) {
        $categoryModel->parent_id = null;
        if ($category['parent_id'] !== null) {
          $categoryModel->parent_id = $categoryModel->where('id_1c', $category['parent_id'])->first()->id;
        }
        $categoryModel->name = $category['name'];
        $categoryModel->id_1c = $category['id_1c'];
        //dd($categoryModel);
        $categoryModel->save();
      }
    }
  }

  public function getIdBy1cId($id_1c) {
    $modelCurrent = Model::find($this->config['categoryModelId']);
    $className = $modelCurrent->namespace;
    $categoryModel = new $className;
    if ($category = $categoryModel->where('id_1c', $id_1c)->first()) {
      return $category->id;
    }
    return null;
  }

  public function addNewField($fieldname, $fieldtype, $model_id) {
    $model = Model::find($this->config['categoryModelId']);
    if (!Column::where([['model_id', $model->id], ['name', $fieldname]])->first()) {
      $field = new Column([
        'name' => $fieldname,
        'title' => $fieldname,
        'description' => '',
        'type' => $fieldtype,
        'null' => 1,
      ]);
      $field->user_id = auth()->user()->id;
      $field->table_id = $model->altrp_table->id;
      $field->model_id = $model->id;
      $field->save();
    }
  }

}

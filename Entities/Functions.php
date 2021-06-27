<?php


namespace Modules\Integration1C\Entities;


use App\Altrp\Column;
use App\Altrp\Model;
use App\Altrp\Table;
use App\User;
use Illuminate\Http\Request;


class Functions
{

    public $config;

    public function __construct() {
        $this->config = require module_path('Integration1C','Config/config.php');
    }

    public static function getXMLFiles($directory) {
      $dir = scandir($directory);
      $result = [];
      //dd($dir);
      foreach($dir as $fileName) {
        if (!in_array($fileName,array(".", ".."))) {
          if (!is_dir($directory . $fileName)) {
            if (preg_match("/.xml$/i", $fileName)) {
              $result[] = $fileName;
            }
          }
        }
      }
      return $result;
    }

    public static function createGuide($property) {
      $guid = \Str::slug($property->xml->Наименование);
      $guid = str_replace('-', '_', $guid);
      $model = new Model();
      $currModel = $model->where('name', $guid)->first();
      if ($currModel == null) {

        $model->name = $guid;
        $model->title = (string)$property->xml->Наименование;
        $res = $model->save();
        /*
        self::addColumn('id_1c', 'Id 1C', 'string', $model);
        self::addColumn('name', 'Наименование', 'string', $model);
        self::addColumn('updated_at', 'updated_at', 'timestamp', $currModel);
        self::addColumn('created_at', 'created_at', 'timestamp', $currModel);
        self::addColumn('deleted_at', 'deleted_at', 'timestamp', $currModel);
        */
        dd($res);
      }
      //$currModel = self::createGuideItems($property->xml->ВариантыЗначений, $currModel);
      dd($currModel);
    }

    public static function addColumn($name, $title, $type, $model) {
      $field = new Column([
        'name' => $name,
        'title' => $title,
        'description' => '',
        'type' => $type,
      ]);
      $field->user_id = auth()->user()->id;
      $field->table_id = $model->altrp_table->id;
      $field->model_id = $model->id;
      $field->save();
      return $field;
    }

    public static function createGuideItems($property, $currModel) {
      if (!empty($property->Справочник)) {
        $modelCurrent = Model::find($currModel->id);
        $className = $modelCurrent->namespace;

        foreach ($property->Справочник as $pp) {
          $guidModel = new $className;
          if ($guidModel->where('id_1c', (string)$pp->ИдЗначения)->first() === null) {
            $guidModel->name = (string)$pp->Значение;
            $guidModel->id_1c = (string)$pp->ИдЗначения;
            $guidModel->save();
          }
        }
      }
      return $currModel;
      //dd($currModel);
    }

  public static function addNewField($fieldname, $fieldtype, $model_id) {
    $model = Model::find($model_id);
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

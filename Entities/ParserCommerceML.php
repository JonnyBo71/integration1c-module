<?php


namespace Modules\Integration1C\Entities;


use App\User;
use Illuminate\Http\Request;
use \Zenwalker\CommerceML\CommerceML;
use \Zenwalker\CommerceML\Model\OfferPackage;
use \Zenwalker\CommerceML\Model\Product;
use \Zenwalker\CommerceML\Model\Offer;

class ParserCommerceML
{

    protected $catalog;

    protected $offers;

    public function __construct() {
      $this->config = require module_path('Integration1C','Config/config.php');
    }

    public function parseCommerceML($importFiles) {
      $cml = new CommerceML();
      foreach ($importFiles as $item) {
        if (basename($item) == $this->config['importFiles'][0]) {
          $cml->loadImportXml(storage_path('app/public/1C/import') . '/' . basename($item));
        }
        if (basename($item) == $this->config['importFiles'][1]) {
          $cml->loadOffersXml(storage_path('app/public/1C/import') . '/' . basename($item));
        }
      }
      //dd($cml->offerPackage->offers);
      $this->catalog = $cml->catalog;
      $this->offers = $cml->offerPackage;
    }

    public function getCatalog() {
      return $this->catalog;
    }

    public function getOffers() {
      return $this->offers;
    }

    public function getPriseTypes($classifier) {
      return $classifier->xml->ТипыЦен;
    }

    public function getStores($classifier) {
      return $classifier->xml->Склады;
    }

    public function getUnits($classifier) {
      return $classifier->xml->ЕдиницыИзмерения;
    }


    public function getProperties($properties) {
      $result = [];
      if (!empty($properties)) {
        foreach ($properties as $property) {
          if ($property->xml->ТипЗначений == "Справочник") {
            //добавляем и заполняем справочник
            Functions::createGuide($property);
          } else {
            $result[] = ['name' => (string) $property->xml->Наименование, 'type' => (string) $property->xml->ТипЗначений, 'id_1c' => (string) $property->xml->Ид];
          }
        }
      }
      return $result;
    }

}

<?php


namespace Modules\Integration1C\Entities;


use App\User;
use Illuminate\Http\Request;
use CommerceML\Client;
use CommerceML\Implementation\CommercialInformation;
use CommerceML\Implementation\Document;
use CommerceML\Implementation\Counterparties;
use CommerceML\Implementation\Counterparty;
use CommerceML\Implementation\Address;
use CommerceML\Implementation\AddressField;
use CommerceML\Implementation\Representatives;
use CommerceML\Implementation\Representative;
use CommerceML\Implementation\Products;
use CommerceML\Implementation\Product;
use CommerceML\Implementation\BaseUnit;
use CommerceML\Implementation\RequisiteValues;
use CommerceML\Implementation\RequisiteValue;

class GenerateCommerceML
{

    protected $catalog;

    protected $offers;

    public function __construct() {
      $this->config = require module_path('Integration1C','Config/config.php');
    }

    public function createCommerceML() {
      $xml = new CommercialInformation([
        new Document(
          '142',
          '42',
          date('Y-m-d'),
          'Заказ товара',
          'Продавец',
          'UAH',
          '30',
          '4000',
          new Counterparties([
            new Counterparty(
              '19',
              'Jake',
              NULL,
              'User',
              'Jake Sully',
              'Sully',
              'Jake',
              new Address([
                new AddressField('Улица', 'Ул. Тестера'),
                new AddressField('Дом', '7а'),
                new AddressField('Квартира', '104'),
              ], '87698'),
              new Representatives([
                new Representative(
                  new Counterparty('20', 'Jenny', 'Admin')
                )
              ])
            )
          ]),
          date('H:i:s'),
          NULL,
          new Products([
            new Product(
              '192',
              'Тетріс',
              '4',
              new BaseUnit('796', 'Штука', 'PCE', 'шт'),
              '400',
              '5',
              '2000',
              new RequisiteValues([
                new RequisiteValue('ВидНоменклатуры', 'Товар')
              ])
            )
          ]),
          new RequisiteValues([
            new RequisiteValue('Заказ оплачен', TRUE),
            new RequisiteValue('Метод оплаты', 'Наличный расчет'),
            new RequisiteValue('Дата оплаты', '2007-10-16 15:44:44'),
          ])
        )
      ]);
      return  $xml;
    }

    public function getCatalog() {
      return $this->catalog;
    }

    public function getOrders() {
      return $this->offers;
    }

    public function getClient($classifier) {
      return $classifier->xml->ТипыЦен;
    }

    public function getProducts($classifier) {
      return $classifier->xml->Склады;
    }

    public function getOffers($classifier) {
      return $classifier->xml->ЕдиницыИзмерения;
    }


}

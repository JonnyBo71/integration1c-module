@extends('integration1c::layouts.app')

@section('content')
  <style>
    .form-label {width: 100%;}
  </style>
  <div class="container">
    <form method="post" action="/plugins/integration1c/setting">
      {{ csrf_field() }}
    <div class="row justify-content-center">
      <div class="col-md-12">
        <a href="{{url('/admin')}}" class="btn btn-secondary btn-sm mb-3"><- Back to Admin</a>
          <h3>Интеграция с 1С</h3>
          <div id="root">
            <div class="content-box">
              <div class="row g-3">

                  <div class="col-md-4">
                    <label class="form-label">Категории</label>
                    <select name="categoryModelId" class="control-select control-field" id="categoryModelId">
                      <option disabled="" value="" selected></option>
                      <? foreach ($models as $model) : ?>
                      <option value="<?= $model['id'] ?>" <?=($config['categoryModelId'] == $model['id']) ? 'selected' : '';?>><?= $model['title'] ?></option>
                      <? endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Товары</label>
                    <select class="control-select control-field" id="productModelId" name="productModelId">
                      <option disabled="" value="" selected></option>
                      <? foreach ($models as $model) : ?>
                      <option value="<?= $model['id'] ?>" <?=($config['productModelId'] == $model['id']) ? 'selected' : '';?>><?= $model['title'] ?></option>
                      <? endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Предложения</label>
                    <select class="control-select control-field" id="offerModelId" name="offerModelId">
                      <option disabled="" value="" selected></option>
                      <? foreach ($models as $model) : ?>
                      <option value="<?= $model['id'] ?>" <?=($config['offerModelId'] == $model['id']) ? 'selected' : '';?>><?= $model['title'] ?></option>
                      <? endforeach; ?>
                    </select>
                  </div>

              </div>
              <div class="row g-3">

                  <div class="col-md-4">
                    <label class="form-label">Типы цен</label>
                    <select class="control-select control-field" id="pricetypeModelId" name="pricetypeModelId">
                      <option disabled="" value="" selected></option>
                      <? foreach ($models as $model) : ?>
                      <option value="<?= $model['id'] ?>" <?=($config['pricetypeModelId'] == $model['id']) ? 'selected' : '';?>><?= $model['title'] ?></option>
                      <? endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Склады</label>
                    <select class="control-select control-field" id="storeModelId" name="storeModelId">
                      <option disabled="" value="" selected></option>
                      <? foreach ($models as $model) : ?>
                      <option value="<?= $model['id'] ?>" <?=($config['storeModelId'] == $model['id']) ? 'selected' : '';?>><?= $model['title'] ?></option>
                      <? endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Единицы измерения</label>
                    <select class="control-select control-field" id="unitModelId" name="unitModelId">
                      <option disabled="" value="" selected></option>
                      <? foreach ($models as $model) : ?>
                      <option value="<?= $model['id'] ?>" <?=($config['unitModelId'] == $model['id']) ? 'selected' : '';?>><?= $model['title'] ?></option>
                      <? endforeach; ?>
                    </select>
                  </div>

                </div>
                <div class="row g-3">
                  <div class="col-md-4">
                    <label class="form-label">Свойства</label>
                    <select class="control-select control-field" id="guidModelId" name="guidModelId">
                      <option disabled="" value="" selected></option>
                      <? foreach ($models as $model) : ?>
                      <option value="<?= $model['id'] ?>" <?=($config['guidModelId'] == $model['id']) ? 'selected' : '';?>><?= $model['title'] ?></option>
                      <? endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Изображения</label>
                    <select class="control-select control-field" id="imageModelId" name="imageModelId">
                      <option disabled="" value="" selected></option>
                      <? foreach ($models as $model) : ?>
                      <option value="<?= $model['id'] ?>" <?=($config['imageModelId'] == $model['id']) ? 'selected' : '';?>><?= $model['title'] ?></option>
                      <? endforeach; ?>
                    </select>
                  </div>

              </div>
              <div class="row g-1">
                <div class="col-12 mt-3"><h4>Связи:</h4></div>
              </div>
              <div class="row g-3">

                  <div class="col-md-4">
                    <label class="form-label">Товары и категории</label>
                    <select class="control-select control-field" id="product_category" name="product_category">
                      <option disabled="" value="" selected></option>
                      <? foreach ($models as $model) : ?>
                      <option value="<?= $model['id'] ?>" <?=($config['connections']['product_category'] == $model['id']) ? 'selected' : '';?>><?= $model['title'] ?></option>
                      <? endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Товары и свойства</label>
                    <select class="control-select control-field" id="product_property" name="product_property">
                      <option disabled="" value="" selected></option>
                      <? foreach ($models as $model) : ?>
                      <option value="<?= $model['id'] ?>" <?=($config['connections']['product_property'] == $model['id']) ? 'selected' : '';?>><?= $model['title'] ?></option>
                      <? endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Товары и предложения</label>
                    <select class="control-select control-field" id="product_offer" name="product_offer">
                      <option disabled="" value="" selected></option>
                      <? foreach ($models as $model) : ?>
                      <option value="<?= $model['id'] ?>" <?=($config['connections']['product_offer'] == $model['id']) ? 'selected' : '';?>><?= $model['title'] ?></option>
                      <? endforeach; ?>
                    </select>
                  </div>

              </div>
              <div class="row g-3">

                  <div class="col-md-4">
                    <label class="form-label">Товары и единицы измерения</label>
                    <select class="control-select control-field" id="product_unit" name="product_unit">
                      <option disabled="" value="" selected></option>
                      <? foreach ($models as $model) : ?>
                      <option value="<?= $model['id'] ?>" <?=($config['connections']['product_unit'] == $model['id']) ? 'selected' : '';?>><?= $model['title'] ?></option>
                      <? endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Товары и типы цен</label>
                    <select class="control-select control-field" id="product_pricetype" name="product_pricetype">
                      <option disabled="" value="" selected></option>
                      <? foreach ($models as $model) : ?>
                      <option value="<?= $model['id'] ?>" <?=($config['connections']['product_pricetype'] == $model['id']) ? 'selected' : '';?>><?= $model['title'] ?></option>
                      <? endforeach; ?>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Товары и склады</label>
                    <select class="control-select control-field" id="product_store" name="product_store">
                      <option disabled="" value="" selected></option>
                      <? foreach ($models as $model) : ?>
                      <option value="<?= $model['id'] ?>" <?=($config['connections']['product_store'] == $model['id']) ? 'selected' : '';?>><?= $model['title'] ?></option>
                      <? endforeach; ?>
                    </select>
                  </div>

              </div>


                <div class="row g-1">
                  <div class="col-12 mt-3"><h4>Справочники:</h4></div>
                </div>

                <div class="row g-3">
                <? foreach ($guids as $guid) : ?>
                <? //print_r($guid); ?>
                  <? if($guid['type'] == 'Справочник') : ?>

                    <div class="col-md-4">
                      <label class="form-label"><?=$guid['name']?></label>
                      <select class="control-select control-field" id="<?=$guid['alias']?>" name="<?=$guid['alias']?>">
                        <option disabled="" value="" selected></option>
                        <? foreach ($models as $model) : ?>
                        <option value="<?= $model['id'] ?>" <?=($config['guids'][$guid['alias']] == $model['id']) ? 'selected' : '';?>><?= $model['title'] ?></option>
                        <? endforeach; ?>
                      </select>
                    </div>

                  <? endif; ?>
                <? endforeach; ?>
                </div>

              <div class="row g-1">
                <div class="col-12 mt-3">
                  <button class="btn btn-primary" type="submit">Сохранить</button>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-4 mt-3">

                </div>
                <div class="col-4 mt-3">
                  <a class="btn btn-success" target="_blank" href="/api/plugins/integration1c/import">Импортировать каталог</a>
                </div>
                <div class="col-4 mt-3">
                  <a class="btn btn-success" target="_blank" href="/api/plugins/integration1c/export">Экспортировать заказы</a>
                </div>
              </div>

            </div>
            </div>
          </div>
        </div>

    </form>
  </div>
@endsection

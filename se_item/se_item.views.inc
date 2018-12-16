<?php

function se_item_views_data_alter(array &$data) {
  $data['node']['se_item_stock_count'] = [
    'title' => t('Stock count'),
    'field' => [
      'title' => t('Stock count'),
      'help' => t('Provide the count of stock for the item.'),
      'id' => 'se_item_stock_count',
    ],
  ];

  $data['node']['se_item_stock_count'] = [
    'title' => t('Stock count'),
    'filter' => [
      'title' => t('Stock count'),
      'help' => t('Filter on stock count for an item.'),
      'id' => 'se_item_stock_count',
    ],
  ];

}



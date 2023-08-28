<?php return array (
  'name' => 'classic-rocket',
  'display_name' => 'Classic Rocket',
  'version' => '3.1.0',
  'author' => 
  array (
    'name' => 'Prestarocket',
    'email' => 'contact@prestarocket.com',
    'url' => 'http://www.prestarocket.com',
  ),
  'meta' => 
  array (
    'compatibility' => 
    array (
      'from' => '1.7.7.x',
      'to' => NULL,
    ),
    'available_layouts' => 
    array (
      'layout-full-width' => 
      array (
        'name' => 'Full Width',
        'description' => 'No side columns, ideal for distraction-free pages such as product pages.',
      ),
      'layout-both-columns' => 
      array (
        'name' => 'Three Columns',
        'description' => 'One large central column and 2 side columns.',
      ),
      'layout-left-column' => 
      array (
        'name' => 'Two Columns, small left column',
        'description' => 'Two columns with a small left column',
      ),
      'layout-right-column' => 
      array (
        'name' => 'Two Columns, small right column',
        'description' => 'Two columns with a small right column',
      ),
    ),
  ),
  'assets' => NULL,
  'global_settings' => 
  array (
    'configuration' => 
    array (
      'PS_IMAGE_QUALITY' => 'png',
    ),
    'modules' => 
    array (
      'to_enable' => 
      array (
        0 => 'ps_linklist',
      ),
      'to_disable' => 
      array (
        0 => 'ps_searchbar',
      ),
    ),
    'hooks' => 
    array (
      'modules_to_hook' => 
      array (
        'displayNav1' => 
        array (
          0 => 'ps_contactinfo',
        ),
        'displayNav2' => 
        array (
          0 => 'ps_languageselector',
          1 => 'ps_currencyselector',
        ),
        'displayTop' => 
        array (
          0 => 'ps_customersignin',
          1 => 'ps_shoppingcart',
        ),
        'displayNavFullWidth' => 
        array (
          0 => 'ps_mainmenu',
        ),
        'displayHome' => 
        array (
          0 => 'ps_imageslider',
          1 => 'ps_featuredproducts',
          2 => 'ps_banner',
          3 => 'ps_customtext',
        ),
        'displayFooterBefore' => 
        array (
          0 => 'ps_emailsubscription',
          1 => 'ps_socialfollow',
        ),
        'displayFooter' => 
        array (
          0 => 'ps_linklist',
          1 => 'ps_customeraccountlinks',
          2 => 'ps_contactinfo',
        ),
        'displayLeftColumn' => 
        array (
          0 => 'ps_categorytree',
          1 => 'ps_facetedsearch',
        ),
        'displaySearch' => 
        array (
          0 => 'ps_searchbarjqauto',
        ),
        'displayProductAdditionalInfo' => 
        array (
          0 => 'ps_sharebuttons',
        ),
        'displayReassurance' => 
        array (
          0 => 'blockreassurance',
        ),
        'displayOrderConfirmation2' => 
        array (
          0 => 'ps_featuredproducts',
        ),
        'displayCrossSellingShoppingCart' => 
        array (
          0 => 'ps_featuredproducts',
        ),
      ),
    ),
    'image_types' => 
    array (
      'cart_default' => 
      array (
        'width' => 125,
        'height' => 125,
        'scope' => 
        array (
          0 => 'products',
        ),
      ),
      'small_default' => 
      array (
        'width' => 98,
        'height' => 98,
        'scope' => 
        array (
          0 => 'products',
          1 => 'categories',
          2 => 'manufacturers',
          3 => 'suppliers',
        ),
      ),
      'medium_default' => 
      array (
        'width' => 452,
        'height' => 452,
        'scope' => 
        array (
          0 => 'products',
          1 => 'manufacturers',
          2 => 'suppliers',
        ),
      ),
      'home_default' => 
      array (
        'width' => 250,
        'height' => 250,
        'scope' => 
        array (
          0 => 'products',
        ),
      ),
      'large_default' => 
      array (
        'width' => 800,
        'height' => 800,
        'scope' => 
        array (
          0 => 'products',
          1 => 'manufacturers',
          2 => 'suppliers',
        ),
      ),
      'category_default' => 
      array (
        'width' => 141,
        'height' => 180,
        'scope' => 
        array (
          0 => 'categories',
        ),
      ),
      'stores_default' => 
      array (
        'width' => 170,
        'height' => 115,
        'scope' => 
        array (
          0 => 'stores',
        ),
      ),
      'pdt_180' => 
      array (
        'width' => 180,
        'height' => 180,
        'scope' => 
        array (
          0 => 'products',
        ),
      ),
      'pdt_300' => 
      array (
        'width' => 300,
        'height' => 300,
        'scope' => 
        array (
          0 => 'products',
        ),
      ),
      'pdt_360' => 
      array (
        'width' => 360,
        'height' => 360,
        'scope' => 
        array (
          0 => 'products',
        ),
      ),
      'pdt_540' => 
      array (
        'width' => 540,
        'height' => 540,
        'scope' => 
        array (
          0 => 'products',
        ),
      ),
    ),
  ),
  'theme_settings' => 
  array (
    'default_layout' => 'layout-full-width',
    'layouts' => 
    array (
      'category' => 'layout-left-column',
      'best-sales' => 'layout-left-column',
      'new-products' => 'layout-left-column',
      'prices-drop' => 'layout-left-column',
      'contact' => 'layout-left-column',
    ),
  ),
  'dependencies' => 
  array (
    'modules' => 
    array (
      0 => 'ps_searchbarjqauto',
    ),
  ),
);

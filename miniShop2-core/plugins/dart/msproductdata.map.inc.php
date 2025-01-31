<?php
return array(
    'fields' => array (
		'vendor_article' => '',
		'barcode' => '',
		'price_mrc' => 0.0,
		'price_rrc' => 0.0,
		'available' => 1,
		'fixprice' => 0,
		'check_sizes' => 0,
		'weight_netto' => 0.0,
		'weight_brutto' => 0.0,
		'length' => 0.0,
		'width' => 0.0,
		'height' => 0.0,
		'places' => 1,
		'volume' => 0.0,
		'b24id' => '',
		'source_url' => '',
		'measure' => ''
	  ),
    'fieldMeta' => array (
		'vendor_article' => 
			array (
			  'dbtype' => 'varchar',
			  'precision' => '255',
			  'phptype' => 'string',
			  'null' => true,
			),
		'barcode' => 
			array (
			  'dbtype' => 'varchar',
			  'precision' => '255',
			  'phptype' => 'string',
			  'null' => true,
			),
		'price_mrc' => 
			array (
			  'dbtype' => 'decimal',
			  'precision' => '12,3',
			  'phptype' => 'float',
			  'null' => false,
			  'default' => 0.0,
			),
		'price_rrc' => 
			array (
			  'dbtype' => 'decimal',
			  'precision' => '12,3',
			  'phptype' => 'float',
			  'null' => false,
			  'default' => 0.0,
			),
		'available' => 
			array (
			  'dbtype' => 'int',
			  'precision' => '10',
			  'phptype' => 'integer',
			  'null' => false,
			  'default' => 99,
			),
		'fixprice' => 
			array (
			  'dbtype' => 'int',
			  'precision' => '11',
			  'phptype' => 'integer',
			  'null' => false,
			  'default' => 0,
			),
		'check_sizes' => 
			array (
			  'dbtype' => 'int',
			  'precision' => '1',
			  'phptype' => 'integer',
			  'null' => false,
			  'default' => 0,
			),
		'weight_netto' => 
			array (
			  'dbtype' => 'decimal',
			  'precision' => '13,3',
			  'phptype' => 'float',
			  'null' => false,
			  'default' => 0.0,
			),
		'weight_brutto' => 
			array (
			  'dbtype' => 'decimal',
			  'precision' => '13,3',
			  'phptype' => 'float',
			  'null' => false,
			  'default' => 0.0,
			),
		'length' => 
			array (
			  'dbtype' => 'decimal',
			  'precision' => '12,2',
			  'phptype' => 'float',
			  'null' => false,
			  'default' => 0.0,
			),
		'width' => 
			array (
			  'dbtype' => 'decimal',
			  'precision' => '12,2',
			  'phptype' => 'float',
			  'null' => false,
			  'default' => 0.0,
			),
		'height' => 
			array (
			  'dbtype' => 'decimal',
			  'precision' => '12,2',
			  'phptype' => 'float',
			  'null' => false,
			  'default' => 0.0,
			),
		'places' => 
			array (
			  'dbtype' => 'int',
			  'precision' => '10',
			  'phptype' => 'integer',
			  'null' => false,
			  'default' => 1,
			),
		'volume' => 
			array (
			  'dbtype' => 'decimal',
			  'precision' => '13,3',
			  'phptype' => 'float',
			  'null' => false,
			  'default' => 0.0,
			),
		'b24id' => 
			array (
			  'dbtype' => 'varchar',
			  'precision' => '255',
			  'phptype' => 'string',
			  'null' => true,
			  'default' => '',
			),
		'source_url' => 
			array (
			  'dbtype' => 'varchar',
			  'precision' => '255',
			  'phptype' => 'string',
			  'null' => true,
			  'default' => '',
			),
		'measure' => 
			array (
			  'dbtype' => 'varchar',
			  'precision' => '255',
			  'phptype' => 'string',
			  'null' => true,
			  'default' => '',
			)
    )
    ,'indexes' => array(
		'vendor_article' => 
		array (
		  'alias' => 'vendor_article',
		  'primary' => false,
		  'unique' => false,
		  'type' => 'BTREE',
		  'columns' => 
		  array (
			'vendor_article' => 
			array (
			  'length' => '',
			  'collation' => 'A',
			  'null' => false,
			),
		  ),
		),
		'barcode' => 
		array (
		  'alias' => 'barcode',
		  'primary' => false,
		  'unique' => false,
		  'type' => 'BTREE',
		  'columns' => 
		  array (
			'barcode' => 
			array (
			  'length' => '',
			  'collation' => 'A',
			  'null' => false,
			),
		  ),
		),
		'price_rrc' => 
		array (
		  'alias' => 'price_rrc',
		  'primary' => false,
		  'unique' => false,
		  'type' => 'BTREE',
		  'columns' => 
		  array (
			'price_rrc' => 
			array (
			  'length' => '',
			  'collation' => 'A',
			  'null' => false,
			),
		  ),
		),
		'price_mrc' => 
		array (
		  'alias' => 'price_mrc',
		  'primary' => false,
		  'unique' => false,
		  'type' => 'BTREE',
		  'columns' => 
		  array (
			'price_mrc' => 
			array (
			  'length' => '',
			  'collation' => 'A',
			  'null' => false,
			),
		  ),
		),
		'available' => 
		array (
		  'alias' => 'available',
		  'primary' => false,
		  'unique' => false,
		  'type' => 'BTREE',
		  'columns' => 
		  array (
			'available' => 
			array (
			  'length' => '',
			  'collation' => 'A',
			  'null' => false,
			),
		  ),
		),
        'fixprice' => 
		array (
		  'alias' => 'fixprice',
		  'primary' => false,
		  'unique' => false,
		  'type' => 'BTREE',
		  'columns' => 
		  array (
			'fixprice' => 
			array (
			  'length' => '',
			  'collation' => 'A',
			  'null' => false,
			),
		  ),
		),
		'check_sizes' => 
		array (
		  'alias' => 'check_sizes',
		  'primary' => false,
		  'unique' => false,
		  'type' => 'BTREE',
		  'columns' => 
		  array (
			'check_sizes' => 
			array (
			  'length' => '',
			  'collation' => 'A',
			  'null' => false,
			),
		  ),
		),
		'b24id' => 
		array (
		  'alias' => 'b24id',
		  'primary' => false,
		  'unique' => false,
		  'type' => 'BTREE',
		  'columns' => 
		  array (
			'b24id' => 
			array (
			  'length' => '',
			  'collation' => 'A',
			  'null' => false,
			),
		  ),
		)
    )
);
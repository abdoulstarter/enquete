<?php
App::uses('AppModel', 'Model');

/**
 * Class OrderDetail
 * 発注詳細
 */
class OrderDetail extends AppModel
{
	public $belongsTo = array(
		
		'Order' => array(
			'className'  => "Order",
			'foreignKey' => "order_id",
			'type'       => "LEFT",
			'conditions' => array('Order.del_flg' => 0),
			'fields' => array(
				"Order.id",
			),
		),
		'Storage' => array(
			'className'  => "Storage",
			'foreignKey' => "storage_id",
			'type'       => "LEFT",
			'conditions' => array('Storage.del_flg' => 0),
			'fields' => array(
				"Storage.id",
				"Storage.code",
				"Storage.name",
			),
		),
		'ProductClass' => array(
			'className'  => "ProductClass",
			'foreignKey' => "product_class_id",
			'type'       => "LEFT",
			// 'conditions' => array('ProductClass.del_flg' => 0),
			'fields' => array(
				"ProductClass.*",
			),
		),
	);

	

	/**
	 * 一覧取得用のSQLを生成する
	 */
	public function getQueryForIndex($dbo, $conditions, $sort)
	{
		App::uses('Model', 'Order');

		$Order = new Order();

		// 複数納品対応.発注(order)は１つだが、表示はset単位で行うため
		// detailをグルーピングする。(detail側にしかset情報がないため)
		$groupSubQuery = $dbo->buildStatement(
			array(
				'fields' => array(
					'`Order`.*',
					'OrderDetail.set_no',
					'OrderDetail.storage_id',
					'OrderDetail.arrival_scheduled_date',
					'(CASE WHEN EcOrderDetail.order_id IS NOT NULL THEN 1 END) AS order_id_flg'
				),
				'table' => $dbo->fullTableName($Order),
				'alias' => 'Order',
				'conditions' => $conditions,
				'joins' => array(
            		array(
						'type' => 'LEFT',
						'table' => 'order_details',
						'alias' => 'OrderDetail',
						'conditions' => 'Order.id = OrderDetail.order_id',
					),
            		array(
						'type' => 'LEFT',
						'table' => 'ec_order_details',
						'alias' => 'EcOrderDetail',
						'fields' => 'MAX(EcOrderDetail.order_id) AS ec_order',
						'conditions' => 'Order.id = EcOrderDetail.order_id',
						'group' => 'EcOrderDetail.order_id',
        			),
        		),
        		'group' => array(
        			'OrderDetail.order_id',
        			'OrderDetail.set_no',
        		),
			),
			$Order
		);

		// 発注毎の納品確認(複数納品先全て)　速度が改善できないので凍結
		/*
		$groupSubQuery2 = $dbo->buildStatement(
			array(
				'fields' => array(
					'OrderDetail.order_id',
					'IF(SUM(OrderDetail.arrived_quantity) > 0, 1, 0) AS arrived_flg',
				),
				'table' => $dbo->fullTableName($this),
				'alias' => 'OrderDetail',
				'conditions' => array('del_flg' => 0),
        		'group' => array(
        			'OrderDetail.order_id',
        		),
			),
			$this
		);
		*/

		// グループ化したものに、各種マスタ情報+納品サブクエリを紐付け
		$mainQuery = $dbo->buildStatement(
			array(
				'fields' => array(
					'`Order`.*',
					'Storage.name',
					'Maker.*',
					'Admin.*',
					//'OrderArrival.arrived_flg'
				),
				'table' => "($groupSubQuery)",
				'alias' => '`Order`',
				'joins' => array(
            		array(
            			'type' => 'LEFT',
	                	'table' => 'storages',
	                	'alias' => 'Storage',
	                	'conditions' => '`Order`.storage_id = Storage.id',
        			),
        			array(
            			'type' => 'LEFT',
	                	'table' => 'makers',
	                	'alias' => 'Maker',
	                	'conditions' => '`Order`.maker_id = Maker.id',
        			),
        			array(
            			'type' => 'LEFT',
	                	'table' => 'admins',
	                	'alias' => 'Admin',
	                	'conditions' => '`Order`.admin_id = Admin.id',
        			),
        		/*
        			array(
            			'type' => 'LEFT',
	                	'table' => "($groupSubQuery2)",
	                	'alias' => 'OrderArrival',
	                	'conditions' => '`Order`.id = OrderArrival.order_id',
        			),
        		*/
        		),
				'order' => $sort,
			),
			$Order
		);

		return $mainQuery;
	}
}

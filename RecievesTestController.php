<?php
App::uses('AppController', 'Controller');
/**
 * Orders Controller
 *
 * @property Order $Order
 * @property OrderDetail $OrderDetail
 * @property HOrder $HOrder
 * @property HOrderDetail $HOrderDetail
 * @property Admin $Admin
 * @property Arrival $Arrival
 * @property ArrivalDetail $ArrivalDetail
 * @property Contact $Contact
 * @property Product $Product
 * @property ProductClass $ProductClass
 * @property Maker $Maker
 * @property Storage $Storage
 * @property Shipment $Shipment
 * @property ShipmentDetail $ShipmentDetail
 *
 * @property PaginatorComponent $Paginator
 */
class RecievesTestController extends AppController
{

/**
 * Components
 *
 * @var array
 */
	public $components = array(
		'Paginator',
		"RequestHandler",
	);

	public $uses = array(
		'Order',
		'OrderDetail',
		'HOrder',
		'HOrderDetail',
		'Admin',
		'Arrival',
		'ArrivalDetail',
		'Contact',
		'Product',
		'ProductClass',
		'Maker',
		'Storage',
		'Shipment',
		'ShipmentDetail',
	);

	/**
	 * 受注一覧
	 */
	public function admin_index() 
	{
		// 検索パラメータを作成/保存
		$this->SearchParameter->make();
		$this->SearchParameter->save();
		
		$this->Order->Behaviors->load('Containable');
		//$this->OrderDetail->Behaviors->load('Containable');

		// 条件設定
		$conditions = $this->Order->makeIndexSearchConditions($this);
		$detail_conditions = $this->OrderDetail->makeIndexSearchConditions($this);
		$conditions = am($conditions, $detail_conditions);
		$conditions = am($conditions, array(
			'Order.maker_id' => $this->Auth->user('maker_id'),
			'Order.mail_status >=' => ORDER_MAIL_STATUS_SENT,
			'OR' => array(
				array(
					'Order.del_flg' => 0,
					'OrderDetail.del_flg' => 0,
				),
				array(
					'Order.del_flg' => 1,
					'OrderDetail.del_flg' => 1,
					'Order.status' => ORDER_STATUS_DENY
				),
			),
		));
		$sort = array('`Order`.id' => 'DESC');
		$limit = $this->_getResultNum();
		$recursive = 1;

		// 一覧用SQL取得
		$dbo = $this->Order->getDataSource();
		$mainQuery = $this->OrderDetail->getQueryForIndex($dbo, $conditions, $sort);
		$query = array(
			'limit' => $limit,
			'extra' => array(
				'type' => $mainQuery
			)
		);
		$this->Paginator->settings = $query;
		$list = $this->Paginator->paginate('Order');

		$this->set("list", $list);
	}

}

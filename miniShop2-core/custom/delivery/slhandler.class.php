<?php
if (!class_exists('msDeliveryInterface')) {
    require_once dirname(dirname(dirname(__FILE__))) . '/model/minishop2/msdeliveryhandler.class.php';
}

class slHandler extends msDeliveryHandler implements msDeliveryInterface
{
   
    public function getCost(msOrderInterface $order, msDelivery $delivery, $cost = 0.0)
    {
        $order_data = $order->get();
		$this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($order_data, 1), array(
			'target' => 'FILE',
			'options' => array(
				'filename' => 'orders.log'
			)
		));
        if(!empty($order_data['sl_data'])) {
            if($sl_data = json_decode($order_data['sl_data'],1)) {
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($sl_data, 1), array(
					'target' => 'FILE',
					'options' => array(
						'filename' => 'orders.log'
					)
				));
                if(!empty($sl_data['price'])) {
                    $cost += $sl_data['price'];
                }
            }
        }else{		
			if(!empty($order_data['delivery_data'])){
				if($dirty_data = json_decode($order_data['delivery_data'], 1)) {
					$this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($dirty_data, 1), array(
						'target' => 'FILE',
						'options' => array(
							'filename' => 'orders.log'
						)
					));
					$service = $dirty_data['service']['main_key'];
					$method = $dirty_data['service']['method'];
					$save_data = [
						'key' => $dirty_data['service']['main_key'],
						'method' => $dirty_data['service']['method'],
						'price' => $dirty_data['service'][$service]['price'][$method]['price'],
						'time' => $dirty_data['service'][$service]['price'][$method]['time'],
						'service' => $dirty_data['service']['main_key'],
						'delivery' => $dirty_data['service']['delivery']
					];
					if(!empty($save_data['price'])) {
						$cost += $save_data['price'];
					}
				}
			}
		}
		
		$this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($cost, 1), array(
			'target' => 'FILE',
			'options' => array(
				'filename' => 'orders.log'
			)
		));

		return parent::getCost($order, $delivery, $cost);
    }

}
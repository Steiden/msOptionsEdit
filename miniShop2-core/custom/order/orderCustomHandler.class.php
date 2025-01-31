<?php
class orderCustomHandler extends msOrderHandler {
	
	/**
     * @param miniShop2 $ms2
     * @param array $config
     */
    public function __construct(miniShop2 $ms2, array $config = array())
    {
        $this->ms2 = $ms2;
        $this->modx = $ms2->modx;

        $this->storage = $this->modx->getOption('ms2_tmp_storage', null, 'session');
        $this->storageInit();

        $this->config = array_merge(array(
            'order' => $this->storageHandler->get(),
        ), $config, $_POST);

        $this->order = &$this->config['order'];
        $this->modx->lexicon->load('minishop2:order');

        if (empty($this->order) || !is_array($this->order)) {
            $this->order = array();
        }
    }
	
	
	/**
     * Switch order status
     *
     * @param integer $order_id The id of msOrder
     * @param integer $status_id The id of msOrderStatus
     *
     * @return boolean|string
     */
    public function changeOrderStatus($order_id, $status_id)
    {
        if (empty($this->order) || !is_object($this->order)) {
            $ctx = !$this->modx->context->key || $this->modx->context->key == 'mgr'
                ? 'web'
                : $this->modx->context->key;
            $this->initialize($ctx);
        }

        $error = '';
        /** @var msOrder $order */
        if (!$order = $this->modx->getObject('msOrder', $order_id)) {
            $error = 'ms2_err_order_nf';
        }

        /** @var msOrderStatus $status */
        if (!$status = $this->modx->getObject('msOrderStatus', array('id' => $status_id, 'active' => 1))) {
            $error = 'ms2_err_status_nf';
        } /** @var msOrderStatus $old_status */
        else {
            if ($old_status = $this->modx->getObject('msOrderStatus',
                array('id' => $order->get('status'), 'active' => 1))
            ) {
                if ($old_status->get('final')) {
                    $error = 'ms2_err_status_final';
                } else {
                    if ($old_status->get('fixed')) {
                        if ($status->get('rank') <= $old_status->get('rank')) {
                            $error = 'ms2_err_status_fixed';
                        }
                    }
                }
            }
        }
        if ($order->get('status') == $status_id) {
            $error = 'ms2_err_status_same';
        }

        if (!empty($error)) {
            return $this->modx->lexicon($error);
        }

        $response = $this->ms2->invokeEvent('msOnBeforeChangeOrderStatus', array(
            'order' => $order,
            'status' => $order->get('status'),
        ));
        if (!$response['success']) {
            return $response['message'];
        }

        $order->set('status', $status_id);

        if ($order->save()) {
            $this->ms2->orderLog($order->get('id'), 'status', $status_id);
            $response = $this->ms2->invokeEvent('msOnChangeOrderStatus', array(
                'order' => $order,
                'status' => $status_id,
            ));
            if (!$response['success']) {
                return $response['message'];
            }

            $lang = $this->modx->getOption('cultureKey', null, 'en', true);
            if ($tmp = $this->modx->getObject('modUserSetting', array('key' => 'cultureKey', 'user' => $order->get('user_id')))) {
                $lang = $tmp->get('value');
            }
            else if ($tmp = $this->modx->getObject('modContextSetting', array('key' => 'cultureKey', 'context_key' => $order->get('context')))) {
                $lang = $tmp->get('value');
            }
            $this->modx->setOption('cultureKey', $lang);
            $this->modx->lexicon->load($lang . ':minishop2:default', $lang . ':minishop2:cart');

            $pls = $order->toArray();
            $pls['cost'] = $this->ms2->formatPrice($pls['cost']);
            $pls['cart_cost'] = $this->ms2->formatPrice($pls['cart_cost']);
            $pls['delivery_cost'] = $this->ms2->formatPrice($pls['delivery_cost']);
            $pls['weight'] = $this->ms2->formatWeight($pls['weight']);
            $pls['payment_link'] = '';
			
			
			$this->modx->log(1, print_r($order->toArray(), 1));
			
            if ($payment = $order->getOne('Payment')) {
				$this->modx->log(1, "Проверка 1");
                if ($class = $payment->get('class')) {
					$this->modx->log(1, "Проверка 2");
                    $this->ms2->loadCustomClasses('payment');
                    if (class_exists($class)) {
						$this->modx->log(1, "Проверка 3");
                        /** @var msPaymentHandler|PayPal $handler */
                        $handler = new $class($order);
                        if (method_exists($handler, 'getPaymentLink')) {
							$this->modx->log(1, "Проверка 4");
                            $link = $handler->getPaymentLink($order);
							$this->modx->log(1, $link);
                            $pls['payment_link'] = $link;
                        }
                    }
                }
            }
			
			// проверка что статус равен "Оплачен" и отправка в CRM
			// TODO: предусмотреть возврат и отмену (возможно, еще какие-то случаи)
			// usleep(200000);
			if($status_id == 2){
				$corePath = $this->modx->getOption('shoplogistic_core_path', array(), $this->modx->getOption('core_path') . 'components/shoplogistic/');
				$shopLogistic = $this->modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
				if ($shopLogistic) {
					$shopLogistic->loadServices('web');
					$shopLogistic->b24->initialize();
					// проверяем статус платежа
					$payment_stage = $this->modx->getOption("shoplogistic_payment_stage");
					$sub_orders = $this->modx->getCollection("slOrder", array("order_id" => $order_id));
					$fqn = $this->modx->getOption('mspyookassa_class', null, 'mspyookassa.mspYooKassa', true);
					$path = $this->modx->getOption('mspyookassa_core_path', null, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/mspyookassa/');
					if (!$mspYooKassa = $this->modx->getService($fqn, '', $path . 'model/', ['core_path' => $path])) {
						$this->modx->log(xPDO::LOG_LEVEL_ERROR,'mspYooKassa not load');
					}else{
						$object = $this->modx->getObject("mspYooKassaOrderPayment", array("id" => $order_id));
						if($object){
							$payment_data = $object->toArray();
						}
						foreach($sub_orders as $sub_order){
							$crm_id = $sub_order->get("crm_id");
							$data = array(
								"STAGE_ID" => $payment_stage,										// меняем стадию
								"UF_CRM_1678044555976" => 1,										// меняем поле "Товар оплачен"
								"UF_CRM_1700028487960" => $payment_data["object"]["id"]				// устанавливаем ID платежа
							);
							if($crm_id){
								$response = $shopLogistic->b24->updateDeal($crm_id, $data);
								/*
								$this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($response, 1), array(
									'target' => 'FILE',
									'options' => array(
										'filename' => 'bitrix24.log'
									)
								));
								*/
							}
						}
					}
				}else{
					$this->modx->log(xPDO::LOG_LEVEL_ERROR, "Shoplogistic not found!", array(
						'target' => 'FILE',
						'options' => array(
							'filename' => 'bitrix24.log'
						)
					));
				}
			}
			
			$userId = $pls['user_id'];
            $user = $this->modx->getObject('modUser', $userId);
            $time = time();
            $newUser = 10; // Сколько секунд пользователь считается новым
			
			
			if ($user && $status_id == 1) {
				$username = $user->get('username');
                $createdon = strtotime($user->get('createdon')) + $newUser;

				if ($createdon > $time) {
					$length = 8;

                    $pass = $this->modx->user->generatePassword($length);

                    //Сохраняем новый пароль
                    $user->set('password', $pass);
                    $user->save();
					$pls["userdata"] = array(
						"username" => $username,
						"password" => $pass
					);
					$user->addSessionContext("web");
					
					$user_id = $user->get("id");
				
					
					if($this->order["text_address"]){
						$address = $this->modx->newObject("slUserAddress");
						$address->set("createdon", time());
						$address->set("user_id", $user_id);
						$address->set("text_address", $this->order["text_address"]);
						$address->set("entrance", $this->order["entrance"]);
						$address->set("floor", $this->order["floor"]);
						$address->set("room", $this->order["room"]);
						$address->set("doorphone", $this->order["doorphone"]);
						$address->save();
					}
				}
			}

            if ($status->get('email_manager')) {
                $subject = $this->ms2->pdoTools->getChunk('@INLINE ' . $status->get('subject_manager'), $pls);
                $tpl = '';
                if ($chunk = $this->modx->getObject('modChunk', $status->get('body_manager'))) {
                    $tpl = $chunk->get('name');
                }
                $body = $this->modx->runSnippet('msGetOrder', array_merge($pls, array('tpl' => $tpl)));
                $emails = array_map('trim', explode(',',
                        $this->modx->getOption('ms2_email_manager', null, $this->modx->getOption('emailsender')))
                );
				$customer_profile = $this->modx->getObject('modUserProfile', array('internalKey' => $pls['user_id']));
				$replyto = $customer_profile->get('email');
                if (!empty($subject)) {
                    foreach ($emails as $email) {
                        if (preg_match('#.*?@.*#', $email)) {
                            $this->sendEmail($email, $subject, $body, $replyto);
                        }
                    }
                }
            }

            if ($status->get('email_user')) {
				
                if ($profile = $this->modx->getObject('modUserProfile', array('internalKey' => $pls['user_id']))) {
                    $subject = $this->ms2->pdoTools->getChunk('@INLINE ' . $status->get('subject_user'), $pls);
                    $tpl = '';
                    if ($chunk = $this->modx->getObject('modChunk', $status->get('body_user'))) {
                        $tpl = $chunk->get('name');
                    }
                    $body = $this->modx->runSnippet('msGetOrder', array_merge($pls, array('tpl' => $tpl)));
                    $email = $profile->get('email');
                    if (!empty($subject) && preg_match('#.*?@.*#', $email)) {
                        $this->sendEmail($email, $subject, $body, $this->modx->getOption("emailsender_to"));
                    }
                }
            }
        }

        return true;
    }
	
	/**
    * @param array $data
    *
    * @return array|string
    */
    public function submit($data = array())
    {
		
		$this->modx->log(xPDO::LOG_LEVEL_ERROR, 'data' . print_r($data, 1), array(
			'target' => 'FILE',
			'options' => array(
				'filename' => 'orders.log'
			)
		));
		
        $response = $this->ms2->invokeEvent('msOnSubmitOrder', array(
            'data' => $data,
            'order' => $this,
        ));
		
		$this->modx->log(xPDO::LOG_LEVEL_ERROR, 'before' . print_r($_SESSION['minishop2'], 1), array(
			'target' => 'FILE',
			'options' => array(
				'filename' => 'orders.log'
			)
		));
		
        if (!$response['success']) {
            return $this->error($response['message']);
        }
		
		$this->set($data);
		
        if (!empty($response['data']['data'])) {
            $this->set($response['data']['data']);
        }		
		
		$this->modx->log(xPDO::LOG_LEVEL_ERROR, 'after' . print_r($_SESSION['minishop2'], 1), array(
			'target' => 'FILE',
			'options' => array(
				'filename' => 'orders.log'
			)
		));

        $response = $this->getDeliveryRequiresFields();
        if ($this->ms2->config['json_response']) {
            $response = json_decode($response, true);
        }
        if (!$response['success']) {
            return $this->error($response['message']);
        }
        $requires = $response['data']['requires'];

        $errors = array();
        foreach ($requires as $v) {
            if (!empty($v) && empty($this->order[$v])) {
                $errors[] = $v;
            }
        }
        if (!empty($errors)) {
            return $this->error('ms2_order_err_requires', $errors);
        }

        $user_id = $this->ms2->getCustomerId();
        if (empty($user_id) || !is_int($user_id)) {
            return $this->error(is_string($user_id) ? $user_id : 'ms2_err_user_nf');
        }

        $cart_status = $this->ms2->cart->status();
        if (empty($cart_status['total_count'])) {
            return $this->error('ms2_order_err_empty');
        }
		
		$sl = $this->order['delivery_data'];
		$dirty_data = json_decode($sl, 1);
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
		$order_properties['sl'] = $save_data;
		
		$this->modx->log(xPDO::LOG_LEVEL_ERROR, 'after' . print_r($order_properties, 1), array(
			'target' => 'FILE',
			'options' => array(
				'filename' => 'orders.log'
			)
		));

        //$delivery_cost = $this->getCost(false, true);
		$all_cost = json_decode($this->getCost(true, false), true);
		$this->modx->log(1, print_r($all_cost, 1));
        //$cart_cost = $this->getCost(true, true) - $delivery_cost;
        $createdon = date('Y-m-d H:i:s');
        /** @var msOrder $order */
        $order = $this->modx->newObject('msOrder');
		//$this->modx->log(1, "teest");
		//$this->modx->log(1, print_r($this->order, 1));
		
        $order->fromArray(array(
            'user_id' => $user_id,
            'createdon' => $createdon,
            'num' => $this->getNum(),
            'delivery' => $this->order['delivery'],
            'payment' => $this->order['payment'],
            'cart_cost' => $all_cost['data']['cart_cost'] - $all_cost['data']['bonus_apply'],
            'weight' => $cart_status['total_weight'],
            'delivery_cost' => (float) $all_cost['data']['delivery_cost'],
            'cost' => (float) $all_cost['data']['cost'],
            'status' => 0,
            'context' => $this->ms2->config['ctx'],
			'properties' => $order_properties
        ));
		//$this->modx->log(1, "{$cart_cost} + {$delivery_cost}");

        // Adding address
        /** @var msOrderAddress $address */
		$properties = array(
			"geo" => json_decode($_REQUEST["geo"], 1),
			"geo_data" => json_decode($_REQUEST["geo_data"], 1)
		);
        $address = $this->modx->newObject('msOrderAddress');
        $address->fromArray(array_merge($this->order, array(
            'user_id' => $user_id,
            'createdon' => $createdon,
			'properties' => json_encode($properties, JSON_UNESCAPED_UNICODE)
        )));
        $order->addOne($address);
				
        // Adding products and save cart
        $cart = $this->ms2->cart->get();
		$corePath = $this->modx->getOption('shoplogistic_core_path', array(), $this->modx->getOption('core_path') . 'components/shoplogistic/');
		$shopLogistic = $this->modx->getService('shopLogistic', 'shopLogistic', $corePath . 'model/');
		if (!$shopLogistic) {
			return $this->error('Could not load shoplogistic class!');
		}
		$shopLogistic->loadServices('web');
		$custom_cart = $shopLogistic->cart->checkCart();
		$location = $shopLogistic->getLocationData('web');
		unset($custom_cart['stores']);
		
        $products = array();
		

        foreach ($cart as $v) {
            if ($tmp = $this->modx->getObject('msProduct', array('id' => $v['id']))) {
                $name = $tmp->get('pagetitle');
            } else {
                $name = '';
            }
            /** @var msOrderProduct $product */
            $product = $this->modx->newObject('msOrderProduct');
            $product->fromArray(array_merge($v, array(
                'product_id' => $v['id'],
                'name' => $name,
                'cost' => $v['price'] * $v['count'],
            )));
            
			$products[] = $product;
        }
		
        $order->addMany($products);

        $response = $this->ms2->invokeEvent('msOnBeforeCreateOrder', array(
            'msOrder' => $order,
            'order' => $this,
        ));
        if (!$response['success']) {
            return $this->error($response['message']);
        }

        if ($order->save()) {
            $response = $this->ms2->invokeEvent('msOnCreateOrder', array(
                'msOrder' => $order,
                'order' => $this,
            ));
            if (!$response['success']) {
                return $this->error($response['message']);
            }
			
			//Списываем бонусы
			if($all_cost['data']['bonus_apply']){
				$this->modx->log(1, "bonus_apply");
				//Есть ли у пользователя бонусный счёт?
				$bonus = $this->modx->getObject('slBonusAccount', array("user_id" => $user_id));

				//Если нет, создаём
				if(!$bonus){
					$bonus = $this->modx->newObject("slBonusAccount");
					$bonus->set("user_id", $user_id);
					$bonus->set("value", 0);
					$bonus->save();
				}
				
				$count_bonus = $all_cost['data']['bonus_apply']; //Всего бонусов нужно списать
				$cost_order = $all_cost['data']['cart_cost'] - $all_cost['data']['bonus_apply']; 
				$cost_percent = $cost_order / $all_cost['data']['bonus_apply'];
				
				foreach ($cart as $key => $v) {
					$this->modx->log(1, print_r($v, 1));
					
					//Списываем бонусы
					if($bonus){
						$this->modx->log(1, "bonus");
						
						//Расчёт, сколько бонусов списать за конкретный товар
						if ($key === array_key_last($cart)) {
							$bonus_count = $count_bonus;
						}else{
							$bonus_count = round(($v['price'] * $v['count']) / $cost_percent);
						}

						$count_bonus = $count_bonus - $bonus_count;
						
						$dateBonus = date('Y-m-d H:i:s');
						
						$bonusOperations = $this->modx->newObject("slBonusOperations");
						$bonusOperations->set("bonus_id", $bonus->get("id"));
						$bonusOperations->set("type", "minus");
						$bonusOperations->set("value", $bonus_count);
						$bonusOperations->set("comment", "Списание бонусов за покупку товара " . $v['price'] . " rub. / " . $v['count'] ."шт");
						$bonusOperations->set("product_id", $v['id']);
						$bonusOperations->set("context_type", "website");
						$bonusOperations->set("order_id", $order->get('id'));
						$bonusOperations->set("date", $dateBonus);
						$bonusOperations->save();

						$bonus->set("value", $bonus->get("value") - $bonus_count);
						$bonus->save();
					}
				}
			}
			
			

            $this->ms2->cart->clean();
            $this->clean();
            if (empty($_SESSION['minishop2']['orders'])) {
                $_SESSION['minishop2']['orders'] = array();
            }
            $_SESSION['minishop2']['orders'][] = $order->get('id');
			
			

            // Trying to set status "new"
            $response = $this->ms2->changeOrderStatus($order->get('id'), 1);
            if ($response !== true) {
                return $this->error($response, array('msorder' => $order->get('id')));
            }

            // Reload order object after changes in changeOrderStatus method
            $order = $this->modx->getObject('msOrder', array('id' => $order->get('id')));
			$properties = $order->get('properties');
			$delivery_data = $properties['sl']['delivery'];
			$delivery_key = $properties['sl']['key'];
			$delivery_method = $properties['sl']['method'];
			$delivery_address = $properties['sl']['address'];
			
			// set suborders
			$i = 1;
			foreach($custom_cart as $scart){
				$products = array();
				$sub_order = $this->modx->newObject('slOrder');				
				if($scart['type'] == 'slWarehouse'){
					$sub_order->set('store_id', $location['store']['id']);
					$sub_order->set('warehouse_id', $scart['object']);
				}else{
					$sub_order->set('store_id', $scart['object']);
					$sub_order->set('warehouse_id', 0);
				}
				$sub_order->set('tk', $delivery_key);
				$sub_order->set('order_id', $order->get('id'));
				$sub_order->set('status', $this->modx->getOption('shoplogistic_default_stage'));
				$sub_order->set('num', $order->get('num').'_'.$i);
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, $delivery_key.' '.$delivery_method, array(
					'target' => 'FILE',
					'options' => array(
						'filename' => 'order_handler.log'
					)
				));	
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($scart, 1), array(
					'target' => 'FILE',
					'options' => array(
						'filename' => 'order_handler.log'
					)
				));	
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, print_r($delivery_data, 1), array(
					'target' => 'FILE',
					'options' => array(
						'filename' => 'order_handler.log'
					)
				));				
				if($delivery_data[$scart['object']][$delivery_key][$delivery_method]['price']){
					if($delivery_data[$scart['object']][$delivery_key][$delivery_method]['price']['price']){
						$sub_order->set('delivery_cost', $delivery_data[$scart['object']][$delivery_key][$delivery_method]['price']['price']);
					}else{
						$sub_order->set('delivery_cost', $delivery_data[$scart['object']][$delivery_key][$delivery_method]['price']);
					}					
					$time = explode(" ", $delivery_data[$scart['object']][$delivery_key][$delivery_method]['time']);
					if($time[0]){
						$newDate = new DateTime();
						if($time[0] == "сегодня"){
							$today = time();
							$tsmp = $today + 10800;
							$newDate->setTimestamp($tsmp);
						}else{
							$interval = 'P'.$time[0].'D';
							$newDate->add(new DateInterval($interval));
						}
						
						$sub_order->set('delivery_date', $newDate->format('Y-m-d H:i:s'));
					}
				}else{
					$sub_order->set('delivery_cost', 0); 
				}
				
				$sub_order->set('createdon', time());
				$sub_order->set('active', 1);
				$cost = 0;
				foreach($scart['products'] as $product){
					$tmp = array();
					$tmp['product_id'] = $product['id'];
					$tmp['name'] = $product['pagetitle'];
					$tmp['count'] = $product['count'];
					$tmp['price'] = str_replace(" ", "", $product['price']);
					$tmp['weight'] = str_replace(" ", "", $product['weight']);
					$tmp['cost'] = str_replace(" ", "", $product['price']) * $product['count'];
					if($scart['type'] == 'slWarehouse'){
						$tmp['store_id'] = $location['store']['id'];
					}else{
						$tmp['store_id'] = $scart['object'];
					}
					$cost += $tmp['cost'];
					$products[] = $tmp;
				}				
				$sub_order->set('cost', $cost);
				$sub_order->set('cart_cost', $cost);
				$sub_order->save();
				$prods = array();
				foreach ($products as $v) {
					if ($tmp = $this->modx->getObject('msProduct', array('id' => $v['id']))) {
						$name = $tmp->get('pagetitle');
					} else {
						$name = '';
					}
					/** @var msOrderProduct $product */
					$product = $this->modx->newObject('slOrderProduct');
					$product->fromArray(array_merge($v, array(
						'order_id' => $sub_order->get('id')
					)));
					$product->save();
				}
				// Чекаем отгрузку и привязываемся
				if($sub_order->get('warehouse_id')){
					$ship = $shopLogistic->cart->findNearShipment($sub_order->get('warehouse_id'), $sub_order->get('store_id'));
					if($ship){
						$sub_order->set('ship_id', $ship['id']);
						$sub_order->save();
					}
				}				
				$i++;
			}
			// add to CRM
			$msOrder = $order;
			if($msOrder){
				$data = array();
				$data['order'] = $msOrder->toArray();
				$msAddress = $msOrder->getOne('Address');
				if($msAddress){
					$data['address'] = $msAddress->toArray();
				}	
				$msUserProfile = $msOrder->getOne('UserProfile');
				if($msUserProfile){
					$data['user'] = $msUserProfile->toArray();
				}
				$msStatus = $msOrder->getOne('Status');
				if($msStatus){
					$data['status'] = $msStatus->toArray();
				}	
				$msDelivery = $msOrder->getOne('Delivery');
				if($msDelivery){
					$data['delivery'] = $msDelivery->toArray();
				}
				$msPayment = $msOrder->getOne('Payment');
				if($msPayment){
					$data['payment'] = $msPayment->toArray();
				}
				$send_data = array();
				
				$suborders = $this->modx->getCollection('slOrder', array("order_id" => $data['order']['id']));
				foreach($suborders as $suborder){
					$store = $suborder->getOne('Store');
					if($store){
						$data['order']['store_id'] = $store->get('btx24_id');
						// $data['order']['type'] = 83;
					}
					$warehouse = $suborder->getOne('Warehouse');
					if($warehouse){
						$data['order']['warehouse_id'] = $warehouse->get('btx24_id');
						// $data['order']['type'] = 84;
					}
					$data['order']['num'] = $suborder->get('num');
					$data['order']['cart_cost'] = $suborder->get('cart_cost');
					// $data['STAGE_ID'] = $suborder->get('status');
					if($data["order"]["store_id"]){
						$send_data["UF_CRM_1678043469"] = $data["order"]["store_id"];
					}
					$send_data['UF_CRM_1678039920'] = $suborder->get('delivery_cost').'|RUB';
					$send_data['UF_CRM_1678040027'] = $suborder->get('delivery_date');
					// TODO: изменить логику при подключении API Yandex
					if($delivery_key){
						$send_data['UF_CRM_1678015298'] = 85;			// верный параметр "Тип поставщика товара" = "Удаленный поставщик"
						if($delivery_method == 'terminal'){
							$send_data['UF_CRM_1678014460'] = 81;		// верный параметр "Тип получения товара покупателем" = "ПВЗ ТК"
						}
						if($delivery_method == 'door'){
							$send_data['UF_CRM_1678014460'] = 79;		// верный параметр "Тип получения товара покупателем" = "Курьер"
						}
						if($delivery_key == 'postrf'){
							$send_data['UF_CRM_1678040452'] = 95;		// верный параметр "Служба доставки" = "Почта РФ" 
							$send_data['UF_CRM_1678015362'] = 91;		// верный параметр "Тип доставки до клиента" = "Доставка ТК"
						}
						if($delivery_key == 'yandex'){
							$send_data['UF_CRM_1678040452'] = 97;		// верный параметр "Служба доставки" = "Я.Доставка"
							$send_data['UF_CRM_1678015362'] = 1143;		// верный параметр "Тип доставки до клиента" = "Экспресс"
							$send_data['UF_CRM_1678014460'] = 79;		// верный параметр "Тип получения товара покупателем" = "Курьер"
						}
						if($delivery_key == 'cdek'){
							$send_data['UF_CRM_1678040452'] = 93;		// верный параметр "Служба доставки" = "СДЭК"
							$send_data['UF_CRM_1678015362'] = 91;		// верный параметр "Тип доставки до клиента" = "Доставка ТК"
						}
						if($delivery_key == 'evening'){
							$send_data['UF_CRM_1678040452'] = 97;		// верный параметр "Служба доставки" = "Я.Доставка"
							$send_data['UF_CRM_1678015362'] = 1257;		// верный параметр "Тип доставки до клиента" = "Вечерняя доставка"
							$send_data['UF_CRM_1678014460'] = 79;		// верный параметр "Тип получения товара покупателем" = "Курьер"
						}
					}else{
						$send_data['UF_CRM_1678040452'] = 99;			// верный параметр "Служба доставки" = "Не требуется"
						$send_data['UF_CRM_1678015298'] = 83;			// верный параметр "Тип поставщика товара" = "Магазин самовывоза"
						$send_data['UF_CRM_1678015362'] = 87;			// верный параметр "Тип доставки до клиента" = "Не требуется"
						$send_data['UF_CRM_1678014460'] = 77;			// верный параметр "Тип получения товара покупателем" = "Магазин"
					}	
					if($suborder->get("warehouse") && $suborder->get("store")){
						$send_data['UF_CRM_1678015362'] = 89;
					}
					if(!isset($send_data['UF_CRM_1678015362'])){
						$send_data['UF_CRM_1678015362'] = 87;
					}
					if($_SESSION["UTM"]){
						foreach($_SESSION["UTM"] as $key => $val){
							$send_data[$key] = $val;
						}
					}
					$shopLogistic->b24->initialize();
					$data['products'] = array();
					$prs = $suborder->getMany('Products');
					foreach ($prs as $pr) {
						$product_id = $pr->get('product_id');
						$p = $pr->toArray();
						$product = $this->modx->getObject('msProduct', $product_id);
						if($product->get('b24id')){
							$p['b24id'] = $product->get('b24id');
						}else{
							$p['b24id'] = $shopLogistic->b24->addProduct($product_id);
						}
						$data['products'][] = $p;
					}
					$this->modx->log(1, print_r($send_data, 1));
					$response = $shopLogistic->b24->addDeal($data, $send_data);
				}
			}
			
			/** @var msPayment $payment */
            if (
                $payment = $this->modx->getObject(
                    'msPayment',
                    array('id' => $order->get('payment'), 'active' => 1)
                )
            ) {
                $res = $payment->send($order);	
				$response = json_decode($res, 1);
				$response['data']['order'] = $order->toArray();
                if ($this->config['json_response']) {
                    @session_write_close();
                    exit(is_array($response) ? json_encode($response) : $response);
                } else {
                    if (!empty($response['data']['redirect'])) {
                        $this->modx->sendRedirect($response['data']['redirect']);
                    } elseif (!empty($response['data']['msorder'])) {
                        $this->modx->sendRedirect(
                            $this->modx->context->makeUrl(
                                $this->modx->resource->id,
                                array('msorder' => $response['data']['msorder'])
                            )
                        );
                    } else {
                        $this->modx->sendRedirect($this->modx->context->makeUrl($this->modx->resource->id));
                    }

                    return $this->success();
                }
            } else {
                if ($this->ms2->config['json_response']) {
                    return $this->success('', array('msorder' => $order->get('id')));
                } else {
                    $this->modx->sendRedirect(
                        $this->modx->context->makeUrl(
                            $this->modx->resource->id,
                            array('msorder' => $response['data']['msorder'])
                        )
                    );

                    return $this->success();
                }
            }
        }

        return $this->error();
    }


    /**
     * Function for sending email
     *
     * @param string $email
     * @param string $subject
     * @param string $body
     *
     * @return void
     */

    public function sendEmail($email, $subject, $body = '', $replyto = '')
    {
        $this->modx->getParser()->processElementTags('', $body, true, false, '[[', ']]', array(), 10);
        $this->modx->getParser()->processElementTags('', $body, true, true, '[[', ']]', array(), 10);


        $mail = $this->modx->getService('mail', 'mail.modPHPMailer');
        $mail->setHTML(true);

        $mail->address('to', trim($email));
        $mail->set(modMail::MAIL_SUBJECT, trim($subject));
        $mail->set(modMail::MAIL_BODY, $body);
        $mail->set(modMail::MAIL_FROM, $this->modx->getOption('emailsender'));
        $mail->set(modMail::MAIL_FROM_NAME, $this->modx->getOption('site_name'));
		if($replyto){
			$mail->address('reply-to',$replyto);
		}
        if (!$mail->send()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR,
                'An error occurred while trying to send the email: ' . $mail->mailer->ErrorInfo
            );
        }
        $mail->reset();
    }
    /**
     * Return current number of order
     *
     * @return string
     */
    public function getNum()
    {
        $num = 0;
        $c = $this->modx->newQuery('msOrder');
        $c->select('num');
        $c->sortby('id', 'DESC');
        $c->limit(1);
        if ($c->prepare() && $c->stmt->execute()) {
            $num = $c->stmt->fetchColumn();
        }
        $num = $num + 1;

        return $num;
    }
}
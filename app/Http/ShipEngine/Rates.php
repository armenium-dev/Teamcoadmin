<?php
namespace App\Http\ShipEngine;

use App\Settings;
use Illuminate\Support\Arr;

class Rates extends ShipEngine{
	
	private $endpoint_url = '/rates/estimate';
	private $request_pattern = [
		"carrier_ids" => [],
		"from_country_code" => "CA",
		"from_state_province" => "ON",
		"from_postal_code" => "L3R 1B9",
		"to_country_code" => "CA",
		"to_state_province" => "",
		"to_postal_code" => "",
		"confirmation" => "none",
		"address_residential_indicator" => "no",
		"weight" => ["value" => 0, "unit" => "pound"],
	];
	private $lbs_per_units = ['low' => 0.5, 'high' => 1];
	private $se_settings = [];
	private $admin_view = false;
	
	public function setAdminView($value){
		$this->admin_view = $value;
	}
	
	public function getEstimateRates($params){
		$results = [];
		$config = config('app.shipengine');
		$this->getServiceOptions();
		
		if(!empty($this->se_settings['carrier_ids'])){
			$this->request_pattern['carrier_ids'] = $this->se_settings['carrier_ids'];
		}else{
			return ['raw' => $results, 'html' => ''];
		}

		$this->request_pattern['from_country_code'] = !is_null($this->se_settings['from_country_code']) ? $this->se_settings['from_country_code'] : $config['SHIPENGINE_FROM_COUNTRY_CODE'];
		$this->request_pattern['from_state_province'] = !is_null($this->se_settings['from_state_province']) ? $this->se_settings['from_state_province'] : $config['SHIPENGINE_FROM_STATE_PROVINCE'];
		$this->request_pattern['from_postal_code'] = !is_null($this->se_settings['from_postal_code']) ? $this->se_settings['from_postal_code'] : $config['SHIPENGINE_FROM_POSTAL_CODE'];;
		
		$this->request_pattern['to_country_code'] = isset($params['to_country_code']) ? $params['to_country_code'] : $this->se_settings['to_country_code'];
		$this->request_pattern['to_state_province'] = $params['to_state_province'];
		$this->request_pattern['to_postal_code'] = $params['to_postal_code'];
		
		
		$this->request_pattern['weight']['value'] = $params['units'] * floatval($this->se_settings['jersey_type_options'][$params['jersey_type']]['cost']);
		#dd($this->request_pattern);
		$results = $this->post($this->endpoint_url, json_encode($this->request_pattern));
		
		return ['raw' => $results, 'html' => $this->toHtml($results), 'desc' => $this->se_settings['result_description']];
	}
	
	private function toHtml($data){
		$html = '';
		$rows = [];
		
		if(isset($data['errors'])){
			foreach($data['errors'] as $error){
				if($error['field_name'] == 'to_postal_code'){
					$error['message'] = 'Invalid "Country code" or "State Province code" or "Postal code".';
				}
				$rows[] = sprintf('<tr><td colspan="3" class="text-center error warning">%s</td></tr>', str_replace('_', ' ', $error['message']));
			}
		}else{
			$data = $this->formatResults($data);
			
			if(!empty($data)){
				foreach($data as $id => $item){
					if(!empty($item['error_messages'])){
						$rows[] = sprintf('<tr><td colspan="3" class="text-center error info">%s</td></tr>', implode(PHP_EOL, $item['error_messages']));
					}else{
						if($this->admin_view){
							$item['service_type'] .= '<br><small class="font-red">'.$id.'</small>';
						}
						$rows[] = sprintf('<tr id="%s"><td>%s</td><td>%s</td><td>%s</td></tr>',
							$id,
							$item['service_type'],
							$item['delivery_days'],
							isset($item['estimate']) ? $item['estimate'] : '-',
						);
					}
				}
			}
		}
		
		if(!empty($rows))
			$html = implode(PHP_EOL, $rows);

		return $html;
	}
	
	private function formatResults($data){
		$results = $this->addPickupData();
		$data = $this->removeUPSExpressSaver($data);
		
		foreach($data as $item){
			$sc = trim($item['service_code']);
			
			if($this->se_settings['display_only_specific_services']){
				if(!isset($this->se_settings['services_options'][$sc])){
					continue;
				}
			}
			
			if(isset($this->se_settings['services_options'][$sc]) && intval($this->se_settings['services_options'][$sc]['status']) == 0){
				continue;
			}
			
			if(!isset($results[$sc])){
				$results[$sc] = [];
			}
			
			$delivery_days       = intval($item['delivery_days']);
			$shipping_amount     = isset($item['shipping_amount']['amount']) ? floatval($item['shipping_amount']['amount']) : 0;
			$insurance_amount    = isset($item['insurance_amount']['amount']) ? floatval($item['insurance_amount']['amount']) : 0;
			$confirmation_amount = isset($item['confirmation_amount']['amount']) ? floatval($item['confirmation_amount']['amount']) : 0;
			$other_amount        = isset($item['other_amount']['amount']) ? floatval($item['other_amount']['amount']) : 0;
			
			$total_amount = $shipping_amount + $insurance_amount + $confirmation_amount + $other_amount;
			
			$results[$sc]['error_messages'] = $item['error_messages'];
			
			$service_type   = $item['service_type'];
			if(isset($this->se_settings['services_options'][$sc]) && $this->se_settings['services_options'][$sc]['type'] != $item['service_type']){
				$service_type = $this->se_settings['services_options'][$sc]['type'];
			}
			if(isset($this->se_settings['services_options'][$sc]) && $this->se_settings['services_options'][$sc]['desc'] != ''){
				$results[$sc]['service_type'] = sprintf('<small>%s</small>%s', $this->se_settings['services_options'][$sc]['desc'], $service_type);
			}else{
				$results[$sc]['service_type'] = $service_type;
			}
			
			$delivery_days_label = 'business day';
			$delivery_days_suffix = ($delivery_days) == 1 ? '' : 's';
			if(isset($this->se_settings['services_options'][$sc]) && isset($this->se_settings['services_options'][$sc]['transit_time'])){
				if($this->se_settings['services_options'][$sc]['transit_time'] == 'cday'){
					$delivery_days_label = 'calendar day';
					
					if($delivery_days > 2){
						$delivery_days_label = 'business day';
						$delivery_days = ($delivery_days - 2).'-'.($delivery_days + 1);
					}
				}
				if($this->se_settings['services_options'][$sc]['desc'] == 'Canada Post'){
					$delivery_days_label = 'business day';
				}
			}
			
			$results[$sc]['delivery_days'] = sprintf('%s %s%s', $delivery_days, $delivery_days_label, $delivery_days_suffix);
			
			if(isset($this->se_settings['services_options'][$sc])){
				$total_amount += $this->se_settings['services_options'][$sc]['rate'];
			}
			$results[$sc]['estimate'] = ($total_amount > 0) ? sprintf('$%s', $total_amount) : '-';
			$results[$sc]['estimate_sort'] = $total_amount;
			
		}
		
		$results = $this->removeErrorFromResults($results);
		$results = $this->sortResults($results);
		#dd($results);
		
		return $results;
	}
	
	/**
	 * removeUPSExpressSaver()
	 *
	 * Added an option for UPS Express Saver:
	 * * If the API does not provide a transit time for a specific instance, then it can we program it, so that the transit time field shows as blank on the shipping results
	 * * OR: if the API is providing "1 business day" as the transit time, can we program it so that:
	 * ** UPS Express Saver can never have a transit time lower than that of UPS Express
	 * ** If the UPS Express Saver shows a lower transit time than UPS Express, it is not displayed on the shipping results
	 *
	 * @param $data
	 * @return mixed
	 */
	private function removeUPSExpressSaver($data){
		#dd($data);
		
		$ups_next_day_air__delivery_days = 0;
		$ups_next_day_air_saver__delivery_days = 0;
		$ups_next_day_air_saver__key = 0;
		
		foreach($data as $k => $item){
			$sc = trim($item['service_code']);
			$delivery_days = intval($item['delivery_days']);
			
			# UPS Express
			if($sc == 'ups_next_day_air'){
				$ups_next_day_air__delivery_days = $delivery_days;
			}
			
			# UPS Express Saver
			if($sc == 'ups_next_day_air_saver'){
				$ups_next_day_air_saver__delivery_days = $delivery_days;
				$ups_next_day_air_saver__key = $k;
			}
		}
		
		if(is_numeric($ups_next_day_air_saver__delivery_days)){
			if($ups_next_day_air_saver__delivery_days == 1 && $ups_next_day_air_saver__delivery_days < $ups_next_day_air__delivery_days){
				unset($data[$ups_next_day_air_saver__key]);
			}
		}
		
		return $data;
	}
	
	private function removeErrorFromResults($results){
		if(count($results) > 0){
			unset($results[""]);
		}
		
		return $results;
	}
	
	private function sortResults($results){
		foreach($results as $k => $v){
			if(!isset($v['estimate'])) $results[$k]['estimate'] = 0;
			if(!isset($v['estimate_sort'])) $results[$k]['estimate_sort'] = 0;
		}
		
		$estimate_sort = array_column($results, 'estimate_sort');
		array_multisort($estimate_sort, SORT_ASC, $results);
		
		return $results;
	}
	
	private function getServiceOptions(){
		
		$service_settings = Settings::getLike('ship_engine_');
		
		foreach($service_settings as $key => $value){
			$_key = str_replace('ship_engine_', '', $key);
			
			switch($_key){
				case 'carrier_ids':
					$this->se_settings[$_key] = array_map('trim', explode(',', $value));
					break;
				case 'jersey_type_options':
					$options = [];
					$services_options = json_decode($value, true);
					if(!empty($services_options)){
						foreach($services_options as $k => $v){
							$options[$k] = $v;
						}
					}
					$this->se_settings[$_key] = $options;
					break;
				case 'services_options':
					$options = [];
					$services_options = json_decode($value, true);
					if(!empty($services_options)){
						foreach($services_options as $k => $v){
							$options[$v['code']] = $v;
						}
					}
					$this->se_settings[$_key] = $options;
					break;
				default:
					if(is_numeric($value)){
						$value = intval($value);
					}
					$this->se_settings[$_key] = $value;
					break;
			}
		}
		
		#dd($this->se_settings);
	}
	
	private function addPickupData(){
		$results = [];
		
		if($this->se_settings['display_pickup_service']){
			$results['pickup'] = [
				"error_messages" => [],
				"service_type"   => "Pickup",
				"delivery_days"  => "N/A",
				"estimate"            => "N/A",
				"estimate_sort"       => 0,
			];
		}
		
		return $results;
	}
	
}

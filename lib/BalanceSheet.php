<?php

ini_set('display_errors', '0');

class BalanceSheet {
	
	public $cif;
	public $year;

	private $url = 'http://www.mfinante.ro/infocodfiscal.html?cod=';

	public function __construct ($cif, $year) {
		$this->cif = (string)$cif;
		$this->year = (string)$year;
	}

	public function getJson(){
		$html = $this->getHtmlData();
		$rows = $this->parseDom($html);

		//return $rows;
		$data = $this->getData($rows);

		return stripslashes(json_encode($data, JSON_UNESCAPED_UNICODE));
	}

	private function getHtmlData(){
		$url = $this->url.$this->cif;

		$fields = [
						'TS018732dc_id' => 3,
						'TS018732dc_cr' => '0ecc7978d27000058c9f41d2427d7be7:jjii:P1N20iZH:57966577',
						'TS018732dc_76' => 0,
						'TS018732dc_86' => 0,
						'TS018732dc_md' => 2,
						'TS018732dc_rf' => 'http://www.mfinante.ro/infocodfiscal.html?cod='.$this->cif,
						'TS018732dc_ct' => 'application/x-www-form-urlencoded',
						'TS018732dc_pd' => 'an=WEB_AN'.$this->year.'&cod='.$this->cif.'&captcha=null&method.bilant=VIZUALIZARE'
				];
		$fields_values[1] = '';

		//url-ify the data for the POST
		foreach($fields as $key=>$value) { $fields_values[1] .= $key.'='.$value.'&'; }
		rtrim($fields_values[1], '&');

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_values[1]);
		curl_setopt($ch, CURLOPT_URL, trim($url));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


		//execute post
		$html = curl_exec($ch);
		if(curl_error($ch)) {
			return curl_error($ch);
		}
		//close connection
		curl_close($ch);

		return $html;
	}

	private function parseDom($html) {
		$dom = new domDocument;
		$dom->loadHTML($html); 
		$dom->preserveWhiteSpace = false;
		$center = $dom->getElementsByTagName('center');

		if(!$center) {
			return false;
		}

		$table = $center->item(0)->childNodes->item(1);
		//$rows = $table->nodeValue;
		$rows = $table->childNodes;

		return $rows;
	}

	private function getData($rows) {
		$values = [];
		$balances = $rows;

		for ($i=0; $i < $rows->length; $i++) {
			$values[$i] = $rows->item($i)->childNodes->item(2)->nodeValue;
		}

		foreach ($values as &$value) {
			$value = trim($value);
			$value = preg_replace('/\s+/', ' ', $value);

			if($value === '-'){
				$value = NULL;
			}
		}

		 $data = [
		 	'active_imobilizate_total' => $values[2],
		 	'active_circulante_total' => $values[3],
		 	'stocuri' => $values[4],
		 	'creante' => $values[5],
		 	'casa_si_banci' => $values[6],
		 	'cheltuieli_avans' => $values[7],
		 	'datorii_total' => $values[8],
		 	'venituri_avans' => $values[9],
		 	'provizioane' =>$values[10],
		 	'capitaluri_total' => $values[11],
		 	'capital_social' => $values[12],
		 	'patrimoniu_regie' => $values[13],
		 	'patrimoniu_public' => $values[14],
		 	'cifra_neta' => $values[16],
		 	'venituri_totale' => $values[17],
		 	'cheltuieli_totale' => $values[18],
		 	'profit_brut' => $values[20],
		 	'pierdere_bruta' => $values[21],
		 	'profit_net' => $values[23],
		 	'pierdere_neta' => $values[24],
		 	'numar_salariati' => $values[26],
		 	'caen' => $values[27]
		 	];

		 return $data;
	}
}
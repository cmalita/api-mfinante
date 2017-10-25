<?php

class Company {

	public $cif;

	private $url = 'http://mfinante.ro/infocodfiscal.html?cod=';
	private $data;

	public function __construct($cif = '6859662') {
		$this->cif = (string)$cif;
	}

	public function getJson() {
		
		if (Validation::CIF($this->cif)) {
			
			$html = $this->getHtmlData();

			$rows = !empty($html) ? $this->parseDom($html) : NULL;
			$data = !empty($rows) ? $this->getData($rows) : NULL;

			if(empty($html)) {
				$data['error'] = 'Nu s-a putut contacta serviciul Ministerului de Finanțe.
				Vă rugăm contactați dezvoltatorul aplicației (cristimalita@gmail.com).';
			}

			if (empty($data)) {
				$data['error'] = 'Societatea comercială cu CIF '.$this->cif.' nu există
				în baza de date a Ministerului de Finanțe.';
			}

	} else {
		$data['error'] = 'CIF invalid!';
	}


		return stripslashes(json_encode($data, JSON_UNESCAPED_UNICODE));
	}

	private function getHtmlData(){
		$url = $this->url.$this->cif;

		$fields = $GLOBALS['MF_requestCookie'];

		$fields_values[1] = '';

		//url-ify the data for the POST
		foreach($fields as $key=>$value) { $fields_values[1] .= $key.'='.$value.'&'; }
		rtrim($fields_values[1], '&');

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, trim($url));
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_values[1]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


		//execute post
		$html = curl_exec($ch);
		if(curl_error($ch)) {
			return false;
		}
		//close connection
		curl_close($ch);

		return $html;
	}

//Parse the extremely crappy mfinante.ro DOM

	private function parseDom($html) {
		$dom = new domDocument;
		$dom->loadHTML($html); 
		$dom->preserveWhiteSpace = false;
		$center = $dom->getElementsByTagName('center');

		if(!$center) {
			return false;
		}
		if (is_object($center->item(0)))		
			$table = $center->item(0)->childNodes->item(0);
		else 
			return false;
	
		$rows = $table->childNodes;

		$balances = '';

		$main = $dom->getElementById('main');
		
		foreach($main->childNodes as $node) {
        	if($node instanceof DOMComment) {
            	$balances = $node->nodeValue;
        	}
		}

		$balances = str_replace('bil:', '', $balances);
		$balances = explode(',', $balances);

		foreach ($balances as &$balance) {
			$balance = str_replace('WEB_AN', '', $balance);
			$balance = str_replace('WEB_IR_AN', '', $balance);
		}

		$rows = [$rows, $balances];
		
		return $rows;
	}

	//finally process the data to make it suitable for working with 

	private function getData($rows) {
		$values = [];
		$balances = $rows[1];

		for ($i=0; $i < $rows[0]->length; $i++) {
			$values[$i] = $rows[0]->item($i)->childNodes->item(2)->nodeValue;
		}

		$values[3] = preg_replace('/[^a-zA-Z0-9\']/', ' ', $values[3]);

		foreach ($values as &$value) {
			$value = trim($value);
			$value = preg_replace('/\s+/', ' ', $value);

			if($value === '-' || $value =='NU') {
				$value = NULL;
			}
		}

		foreach ($balances as &$balance) {
			$balance = trim($balance);
		}

		$values[3] = str_replace(' ', '/', $values[3]);
		$values[1] = htmlentities($values[1]);
		$addrArray = explode('&nbsp;&nbsp;', $values[1]);
		$addr = trim($addrArray[0].$addrArray[1]);
		$city = trim($addrArray[2]);

		//Assign values to data dictionary

		$data = [
			'cif' => $this->cif,
			'nume' => $values[0],
			'adresa' => $addr,
			'oras' => $city,
			'judet' => $values[2],
			'onrc' => $values[3],
			'act_autorizatie' => $values[4],
			'cod_postal' => $values[5],
			'telefon' => $values[6],
			'fax' => $values[7],
			'stare' => $values[8],
			'observatii' => $values[9],
			'data_declaratie' => $this->fixDate($values[10], 1),
			'data_prelucrare' => $this->fixDate($values[11], 1),
			'data_profit' => $this->fixDate($values[12]),
			'data_venit_micro' => $this->fixDate($values[13]),
			'data_acciza' => $this->fixDate($values[14]),
			'data_tva' => $this->fixDate($values[15]),
			'data_asig_sociale' => $this->fixDate($values[16]),
			'data_asig_accidente' => $this->fixDate($values[17]),
			'data_somaj' => $this->fixDate($values[18]),
			'data_fond_creante' => $this->fixDate($values[19]),
			'data_asig_sanatate' => $this->fixDate($values[20]),
			'data_concedii' => $this->fixDate($values[21]),
			'data_taxa_jocuri' => $this->fixDate($values[22]),
			'data_impozit_salarii' => $this->fixDate($values[23]),
			'data_impozit_constructii' => $this->fixDate($values[24]),
			'data_impozit_titei' => $this->fixDate($values[25]),
			'data_redevente_miniere' => $this->fixDate($values[26]),
			'data_redevente_petrol' => $this->fixDate($values[27]),
			'bilanturi' => $balances
		];

		return $data;


	}

	private function fixDate($date, $style=NULL) {

		if (!$style) {
			$date = DateTime::createFromFormat('j-M-y', $date);
		} else {
			$date = DateTime::createFromFormat('j M Y', $date);
		}

		if($date) {
			return $date->format('d.m.Y');
		} else {
			return NULL;
		}

	}

}

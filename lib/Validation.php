<?php

class Validation {
	public static function CIF($cif) {
		// Daca este string, elimina atributul fiscal si spatiile
        if(!is_int($cif)){
                $cif = strtoupper($cif);
                if(strpos($cif, 'RO') === 0){
                        $cif = substr($cif, 2);
                }
                $cif = (int) trim($cif);
        }
        
        // daca are mai mult de 10 cifre sau mai putin de 6, nu-i valid
        if(strlen($cif) > 10 || strlen($cif) < 6){
                return false;
        }
        // numarul de control
        $v = 753217532;
        
        // extrage cifra de control
        $c1 = $cif % 10;
        $cif = (int) ($cif / 10);
        
        // executa operatiile pe cifre
        $t = 0;
        while($cif > 0){
                $t += ($cif % 10) * ($v % 10);
                $cif = (int) ($cif / 10);
                $v = (int) ($v / 10);
        }
        
        // aplica inmultirea cu 10 si afla modulo 11
        $c2 = $t * 10 % 11;
        
        // daca modulo 11 este 10, atunci cifra de control este 0
        if($c2 == 10){
                $c2 = 0;
        }
        return $c1 === $c2;
	}
}
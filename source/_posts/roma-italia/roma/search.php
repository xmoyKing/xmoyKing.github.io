<?php

/* 
Based on jQuery Autocomplete plugin, http://jquery.bassistance.de/autocomplete/
This is a "fake" database; real Ajax search would hit a database, not a php page.
 */

sleep(0);
$q = strtolower($_GET["q"]);
if (!$q) return;
$items = array(
"Ancient Ostia"=>" ",
"Ancient Rome"=>" ",
"Arch of Constantine"=>" ",
"Aventine"=>" ",
"Baths of Caracalla"=>" ",
"Campidoglio"=>" ",
"Campo Marzio"=>" ",
"Capella"=>" ",
"Capitol"=>" ",
"Castel St. Angelo"=>" ",
"Circus Maximus"=>" ",
"Colesseum"=>" ",
"Colosseo"=>" ",
"Constantine"=>" ",
"Hadrian"=>" ",
"Hadrian's Villa"=>" ",
"Hotel"=>" ",
"Il Vittoriano"=>" ",
"Lazio"=>" ",
"Michelangelo"=>" ",
"Pantheon"=>" ",
"Quirinal Palace"=>" ",
"Roma"=>" ",
"Roman Forum"=>" ",
"Rome"=>" ",
"Sistine Chapel"=>" ",
"Spanish Steps"=>" ",
"St. Peter"=>" ",
"St. Peter's Basilica"=>" ",
"Termini"=>" ",
"Tivoli"=>" ",
"Tour"=>" ",
"Trajan"=>" ",
"Trastevere"=>" ",
"Trevi Fountain"=>" ",
"Underground Rome Tour"=>" ",
"Vatican"=>" ",
"Vatican City"=>" ",
"Vatican Museums"=>" ",
"Villa Borghese"=>" ",
"Villa d'Este"=>" ",
"Villa"=>" ",
"Vittore Emanuele II"=>" "
);

foreach ($items as $key=>$value) {
	if (strpos(strtolower($key), $q) !== false) {
		echo "$key|$value\n";
	}
}

?>
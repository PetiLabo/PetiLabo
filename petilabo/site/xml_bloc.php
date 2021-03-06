<?php

class xml_bloc extends xml_abstract {
	private $position = null;
	private $elems = array();
	private $idx_elems = array();
	private $nb_elems_admin = 0;

	// Méthodes publiques
	public function __construct($repere, $taille, $style, $position) {
		$this->enregistrer_chaine("repere", $repere);$this->enregistrer_entier("taille", $taille);
		$this->enregistrer_chaine("style", $style);
		$this->position = $this->normaliser_position($position);
	}
	public function ajouter_elem($balise) {
		$tab_non_admin = array(_PAGE_DRAPEAUX, _PAGE_SAUT, _PAGE_SOCIAL, _PAGE_FORM_CONTACT, _PAGE_PLAN_DU_SITE, _PAGE_CREDITS, _PAGE_MENTIONS_LEGALES, _PAGE_BOUTON_ADMIN, _PAGE_CODE_HTML);
		$tab_occ = array_count_values($this->elems);
		$nb_occ = isset($tab_occ[$balise])?(int) $tab_occ[$balise]:0;
		$this->elems[] = $balise;
		$this->idx_elems[] = $nb_occ;
		// On incrémente le nombre d'éléments administrables
		if (!(in_array($balise, $tab_non_admin))) {
			$this->nb_elems_admin += 1;
		}
	}
	public function set_balise_elem($index, $balise) {
		if ($index >= 0) {
			$this->elems[$index] = $balise;
			$this->idx_elems[$index] = 0;
		}
		return true;
	}
	public function get_nb_elems() {return count($this->elems);}
	public function get_elem($index) {return $this->elems[$index];}
	public function get_idx_elem($index) {return $this->idx_elems[$index];}
	public function get_position() {return $this->position;}
	public function get_nb_elems_admin() {return $this->nb_elems_admin;}
	
	private function normaliser_position($param) {
		$ret = $param;
		if (strlen($ret) > 0) {
			$ret = trim(strtolower($ret));
			if ((strcmp($ret, _XML_HAUT)) && (strcmp($ret, _XML_BAS))) {
				$ret = _XML_MILIEU;
			}
		}
		return $ret;
	}
}
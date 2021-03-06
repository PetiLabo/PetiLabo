<?php
require_once(_PHP_PATH_INCLUDE."visites.php");

// La classe moteur_site hérite de la classe moteur
class moteur_site extends moteur {
	private $cookies = null;

	// Méthodes publiques
	public function __construct() {
		// Récupération du nom de domaine en cours
		$domaine = htmlentities($_SERVER['SERVER_NAME']);
		$this->nom_domaine = $domaine;

		// Récupération du nom de la page en cours
		$self = htmlentities($_SERVER['PHP_SELF']);
		$page_en_cours = basename($self);
		$this->nom_page = str_replace(_PXP_EXT, "", $page_en_cours);
		$this->dir_page = dirname($self);

		// Cas de la page actu
		if (!(strcmp($page_en_cours, _HTML_PATH_ACTU))) {
			$this->est_actu = true;
			$param = new param();
			$param_actu = $param->get(_PARAM_ID);
			$this->no_actu = (int) $param_actu;
		}
		
		// Initialisation des cookies (loi européenne)
		$this->cookies = new cookies($this->nom_page);

		// Chargement des structures XML et des langues
		$this->charger_xml();
		$this->charger_langue();
		
		// Comptage de la visite
		$pa = $this->page->get_meta_pa();
		if (strlen($pa) > 0) {
			$analitix = new xml_analitix();
			if (!($analitix)) {return;}
			$config = $analitix->ouvrir($pa, true);
			if (!($config)) {return;}
			Visites::Listes_a_bloquer($analitix->get_filtre_ip(), $analitix->get_filtre_pays(), $analitix->get_filtre_referents());
			Visites::Ajouter_visite($this->nom_page, $this->langue_page, $analitix->get_anonymisation_ip(), $analitix->get_respect_dnt());
		}
	}
	public function ouvrir_entete() {
		$tab_langues = array("href" => array(), "hreflang" =>array());
		$page_multilingue = $this->page->get_meta_multilingue();

		$this->cookies->init();
		$this->html->ouvrir($this->langue_page);
		if ($page_multilingue) {
			$nb_langues = $this->texte->get_nb_langues();
			if ($nb_langues > 1) {
				$racine = $this->get_racine();
				for ($cpt = 0;$cpt < $nb_langues; $cpt++) {
					$langue = $this->texte->get_langue($cpt);
					$href = $racine.((strcmp($langue, $this->texte->get_langue_par_defaut()))?($langue."/"):"");
					$href .= $this->nom_page._PXP_EXT;
					$tab_langues["href"][] = $href;
					$tab_langues["hreflang"][] = $langue;
				}
			}
		}
		$this->html->ouvrir_head($tab_langues);
	}
	public function ecrire_entete() {
		// Ecriture de l'entête
		$noindex = $this->page->get_meta_noindex();
		if ($noindex) {$this->html->ecrire_meta_noindex();}
		$this->html->ecrire_meta_titre($this->get_meta_titre());
		$this->html->ecrire_meta_descr($this->get_meta_descr());
		// Chargement des polices utilisées dans les styles
		$this->charger_polices_par_defaut();
		// Chargement JS et CSS
		$this->html->charger_js_cdn("//code.jquery.com/jquery-1.12.0.min.js");
		$this->html->charger_js_cdn("//code.jquery.com/jquery-migrate-1.2.1.min.js");
		$this->html->charger_css("css/style.css");
		$this->charger_xml_css(false);
		// On charge les JS supplémentaires uniquement si nécessaire
		$has_bx = $this->page->has_bx();
		if ($has_bx) {$this->html->charger_js("js/bx.min.js");}
		$has_rs = $this->page->has_rs();
		if ($has_rs) {$this->html->charger_js("js/rs.min.js");}
		$has_lb = $this->page->has_lb();
		if ($has_lb) {$this->html->charger_js("js/lb.min.js");}
		$has_form = $this->page->has_form();
		if ($has_form) {
			$this->html->charger_js("js/form_".$this->langue_page.".js");
			$this->html->charger_js("js/form.js");
		}
		
		// Passage du paramètre largeur_responsive à Javascript
		$largeur_responsive = $this->site->get_largeur_responsive();
		$responsive_js = (int) ((strlen($largeur_responsive) > 0)?trim(str_replace("px", "", $largeur_responsive)):"1");
		$this->html->ecrire_js("var largeur_responsive=".$responsive_js.";");
		// Chargement systématique des animations
		$this->html->charger_js("js/anims.js");
		$this->html->charger_js_ie("js/ie.js");
		$this->charger_xml_js(false);
	}

	public function fermer_entete() {
		$this->html->fermer_head();
	}

	public function ouvrir_corps() {
		$this->html->ouvrir_body($this->police_par_defaut);
		// Cas de Google Analytics
		$code_ga = $this->page->get_meta_ga();
		if (strlen($code_ga) > 0) {
			$loi_cookie = $this->site->get_loi_cookie();
			$le_site = $this->texte->get_label_le_site($this->langue_page);
			if (!(strcmp($loi_cookie, _SITE_ATTR_LOI_COOKIE_FORT))) {
				$texte_ga = $this->texte->get_label_installer_ga($this->langue_page);
			}
			else {
				$texte_ga = $this->texte->get_label_utiliser_ga($this->langue_page);
			}
			$poursuite = $this->texte->get_label_poursuite($this->langue_page);
			$accepter = $this->texte->get_label_accepter($this->langue_page);
			$refuser = $this->texte->get_label_refuser($this->langue_page);
			if (!($this->cookies->is_set())) {
				$this->html->inserer_panneau_ga($loi_cookie, $le_site, $this->site->get_url_racine(), $texte_ga, $poursuite, $accepter, $refuser);
			}
			if ((!($this->site->has_loi_cookie())) || ($this->cookies->is_ok())) {
				$this->html->inserer_ga($code_ga);
			}
		}
		// Traitement du cas papier peint <= IE8
		$papierpeint = $this->site->get_papierpeint_exterieur();
		if (strlen($papierpeint) > 0) {$this->html->afficher_papierpeint_ie($papierpeint);}
		$this->html->ouvrir_page($this->site->get_largeur(),$this->site->get_largeur_max(),$this->site->get_largeur_min());
	}

	public function ecrire_corps() {
		$nb_contenus = $this->page->get_nb_contenus();
		for ($cpt_cont = 0;$cpt_cont < $nb_contenus;$cpt_cont++) {
			$obj_contenu = $this->page->get_contenu($cpt_cont);
			if ($obj_contenu) {
				$style = $obj_contenu->get_style();
				$obj_style = $this->style->get_style_contenu($style);
				$type_contenu = ($obj_style)?$obj_style->get_type_special():null;
				$this->ecrire_contenu($obj_contenu, $cpt_cont, $type_contenu);
			}
		}
	}

	public function fermer_corps() {
		$proprietaire = $this->site->get_proprietaire();
		$webmaster = $this->texte->get_label_webmaster($this->langue_page);
		$social = $this->texte->get_label_social($this->langue_page);
		$tab_social = $this->site->get_social();
		// Si page d'actu alors on intègre le no de l'actu dans le nom
		$page = $this->nom_page;
		if (!(strcmp($page, _HTML_PREFIXE_ACTU))) {
			$param = new param();
			$param_actu = $param->get(_PARAM_ID);
			if (strlen($param_actu) > 0) {$page .= "-".$param_actu;}
		}
		$this->html->fermer_page(false, $page, $proprietaire, $webmaster, $social, $tab_social);
		$this->html->fermer_body();
		$this->html->fermer();
	}

	// Méthodes protégées
	protected function ecrire_contenu(&$obj_contenu, $cpt_cont, $type_contenu) {
		$nb_blocs = $obj_contenu->get_nb_blocs();
		$style_contenu = $obj_contenu->get_style();
		$signet_contenu = $obj_contenu->get_signet();
		$semantique_contenu = $obj_contenu->get_semantique();
		$this->html->ouvrir_balise_html5($semantique_contenu);
		$this->html->ouvrir_contenu($cpt_cont, $nb_blocs, $style_contenu, $type_contenu);
		if (strlen($signet_contenu) > 0) {$this->html->ecrire_signet($signet_contenu);}
		for ($cpt_bloc = 0;$cpt_bloc < $nb_blocs; $cpt_bloc++) {
			$obj_bloc = $obj_contenu->get_bloc($cpt_bloc);
			if ($obj_bloc) {
				// Vérification de la présence d'un style
				$style = null;
				$nom_style = $obj_bloc->get_style();
				if (strlen($nom_style) > 0) {$style = $this->style->get_style_bloc($nom_style);}
				// Ouverture du bloc et de son style
				$this->html->ouvrir_bloc($obj_bloc, $obj_contenu->get_taille_totale(), false);
				if ($style) {$this->html->ouvrir_style_bloc($style);}
				// Ecriture du bloc
				$this->ecrire_bloc(_PETILABO_MODE_SITE, $obj_bloc, $cpt_cont, $cpt_bloc);
				// Fermeture du bloc et de son style
				if ($style) {$this->html->fermer_style_bloc($style);}
				$this->html->fermer_bloc($obj_bloc);
			}
		}
		$this->html->fermer_contenu();
		$this->html->fermer_balise_html5($semantique_contenu);
	}

	// Méthodes privées
	private function get_meta_titre() {
		$titre = $this->page->get_meta_titre();
		$titre_editable = $this->page->get_meta_titre_editable();
		$ret = (strlen($titre_editable) > 0)?$this->texte->get_texte($titre_editable, $this->langue_page):$titre;
		return $ret;
	}
	private function get_meta_descr() {
		$descr = $this->page->get_meta_descr();
		$descr_editable = $this->page->get_meta_descr_editable();
		$ret = (strlen($descr_editable) > 0)?$this->texte->get_texte($descr_editable, $this->langue_page):$descr;
		return $ret;
	}
	private function get_racine() {
		$racine = $this->site->get_url_racine();
		if (strlen($racine) == 0) {
			$racine = "http://".$this->nom_domaine.$this->dir_page."/";
		}
		else {
			if (strcmp(substr($racine, 4), "http")) {$racine = "http://".$racine;}
			if (strcmp(substr($racine, -1), "/")) {$racine .= "/";}
		}
		return $racine;
	}
}
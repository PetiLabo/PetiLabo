<?php
	require_once "inc/path.php";
	inclure_inc("const", "param", "session");
	inclure_site("xml_const", "xml_site", "xml_texte");
	
	class form_texte {
		private $nom_page = null;
		private $id_edit = null;
		private $site = null;
		private $texte = null;
		private $nb_langues = 0;
		private $langues = array();
		private $traductions = array();
		private $positions = array();
		
		public function __construct($nom_page, $id_edit) {
			$this->nom_page = $nom_page;
			$this->id_edit = $id_edit;
			
			// Chargement XML Site
			$this->site = new xml_site();
			$ret = $this->site->ouvrir(_XML_PATH._XML_GENERAL._XML_EXT);
			if (!$ret) {
				echo "<p>Erreur lors de l'ouverture du fichier XML site</p>\n";
				return null;
			}
			
			// Gestion des textes
			$this->texte = new xml_texte();
			
			// Création de la liste des langues
			$this->nb_langues = $this->site->get_nb_langues();
			for ($cpt_langue = 0;$cpt_langue < $this->nb_langues;$cpt_langue++) {
				$code_langue = $this->site->get_code_langue($cpt_langue);
				$this->texte->ajouter_langue($code_langue);
				$this->langues[((int) $cpt_langue)] = $code_langue;
				$this->positions[((int) $cpt_langue)] = $this->texte->get_position($code_langue);
			}

			// Lecture des textes pour le site et la page
			$this->texte->ouvrir(_XML_SOURCE_SITE, _XML_PATH._XML_TEXTE._XML_EXT);
			$this->texte->ouvrir(_XML_SOURCE_PAGE, _XML_PATH_PAGES.$this->nom_page."/"._XML_TEXTE._XML_EXT);
			if ($this->site->has_module(_SITE_MODULE_ACTU)) {
				$ret = $this->texte->ouvrir(_XML_SOURCE_MODULE, _XML_PATH_MODULES._XML_TEXTE._XML_EXT);
			}
			
			// Récupération de la source de l'id
			$this->source_edit = $this->texte->get_source($id_edit);
			if (!($this->source_edit)) {
				echo "<p>Erreur lors de la lecture de l'identifiant texte</p>\n";
				return null;
			}

			for ($cpt_langue = 0;$cpt_langue < $this->nb_langues;$cpt_langue++) {
				$code_langue = $this->langues[((int) $cpt_langue)];
				$this->traductions[((int) $cpt_langue)] = $this->texte->get_texte($id_edit, $code_langue);
			}
		}
		
		public function get_nb_langues() {return ((int) $this->nb_langues);}
		public function get_source() {return $this->source_edit;}
		public function get_code_langue($cpt) {return $this->langues[((int) $cpt)];}
		public function get_position($cpt) {return $this->positions[((int) $cpt)];}
		public function get_traduction($cpt) {return $this->traductions[((int) $cpt)];}
		public function strip_tags_attributes($texte) {return $this->texte->strip_tags_attributes($texte);}
	}

	$session = new session();
	if (is_null($session)) {
		header("Location: "._SESSION_URL_FERMETURE);
		exit;
	}

	$session->check_session();
	
	$param = new param();
	$id_texte = $param->get(_PARAM_ID);
	if (strlen($id_texte) == 0) {
		$session->fermer_session();
		exit;
	}

	$page = $session->get_session_param(_SESSION_PARAM_PAGE);
	if (strlen($page) == 0) {
		$session->fermer_session();
		exit;
	}
	
	$form = new form_texte($page, $id_texte);

	echo "<div class=\"form_lb\">\n";
	echo "<form id=\"id_form_texte\" name=\"form_texte\" accept-charset=\"UTF-8\" method=\"post\" action=\"submit_texte.php\">\n";
	echo "<ul class=\"tabs\">\n";
	for ($cpt = 0;$cpt < $form->get_nb_langues(); $cpt++) {
		$code_langue = $form->get_code_langue($cpt);
		$active = ($cpt == 0)?" class=\"active\"":"";
		echo "<li".$active.">";
		echo "<a style=\"background-position:".$form->get_position($cpt)."\" href=\"#tab".$cpt."\" title=\"".strtoupper($code_langue)."\">&nbsp;</a>";
		echo "</li>";
	}
	echo "</ul>\n";
	echo "<div style=\"clear:both;\"></div>\n";
	echo "<div class=\"tab_container\">\n";
	for ($cpt = 0;$cpt < $form->get_nb_langues(); $cpt++) {
		$texte = $form->get_traduction($cpt);
		$texte = $form->strip_tags_attributes($texte);
		$code_langue = $form->get_code_langue($cpt);
		$id_champ = "id_texte_".$code_langue;
		$display = ($cpt == 0)?"block":"none";
		echo "<div id=\"tab".$cpt."\" class=\"tab_content\" style=\"display:".$display."\">";
		echo "<p class=\"champ\">";
		echo "<textarea class=\"texte_champ\" id=\"".$id_champ."\" name=\"".$code_langue."\" rows=\"3\">".$texte."</textarea>";
		echo "</p></div>\n";
	}
	echo "</div>\n";
	echo "<p class=\"champ\"><input type=\"hidden\" name=\"id_texte\" value=\"".$id_texte."\" /></p>\n";
	echo "<p class=\"champ\"><input type=\"hidden\" name=\"src_texte\" value=\"".$form->get_source()."\" /></p>\n";
	echo "<p class=\"champ\"><input class=\"bouton\" type=\"submit\" name=\"valider\" value=\"Enregistrer\"></p>\n";
	echo "</form>\n";
	echo "</div>\n";
<?php
	require_once "inc/path.php";

	define("_TYPE_AJUSTEMENT_SANS", "0");
	define("_TYPE_AJUSTEMENT_ACTUEL", "1");
	define("_TYPE_AJUSTEMENT_ORIGINE", "2");

	class fichier_image {
		// Constantes
		const qualite_jpg = 80;
		const qualite_jpg_reduite = 60;
		const qualite_png = 7;
		const qualite_png_reduite = 7;
		const ratio_reduction = 0.7;

		// Propriétés
		private $src = null;private $src_reduite = null;
		private $dest = null;private $dest_reduite = null;
		private $ext = null;
		private $largeur = 0;private $hauteur = 0;
		private $largeur_standard = 0;private $hauteur_standard = 0;
		private $width_reduite = 0;private $height_reduite = 0;
		
		public function __construct($src, $src_reduite, $dest, $dest_reduite, $ext) {
			$f_exists = @file_exists($src);
			$f_reduite_exists = @file_exists($src_reduite);
			$this->src = ($f_exists)?$src:null;
			$this->src_reduite = ($f_reduite_exists)?$src_reduite:null;
			$this->dest = $dest;
			$this->dest_reduite = $dest_reduite;
			$this->ext = $ext;
			if (strlen($this->src) > 0) {
				list($this->largeur, $this->hauteur) = @getimagesize($this->src);
			}
		}
		public function is_null() {
			$ret = false;
			if (strlen($this->src) == 0) {
				$ret = true;
			}
			elseif ((strcmp($this->ext, _UPLOAD_EXTENSION_JPG)) && (strcmp($this->ext, _UPLOAD_EXTENSION_GIF)) && (strcmp($this->ext, _UPLOAD_EXTENSION_JPEG)) && (strcmp($this->ext, _UPLOAD_EXTENSION_PNG))) {
				$ret = true;
			}
			elseif (($this->largeur < 1) || ($this->hauteur < 1)) {
				$ret = true;
			}
			return $ret;
		}
		public function remplacer(&$image, $type_ajustement) {
			switch ($type_ajustement) {
				case _TYPE_AJUSTEMENT_ACTUEL :
					// Taille actuelle
					list($delta_l, $delta_h) = $this->calculer_delta($image, $this->get_largeur(), $this->get_hauteur());
					$image->retailler($this->get_largeur(), $this->get_hauteur(), $delta_l, $delta_h);
					$status = @copy($image->get_src(), $this->get_dest());
					if ($status) {@unlink($this->get_src());}
					// Taille réduite
					$this->generer_reduite($image);
					break;
				case _TYPE_AJUSTEMENT_ORIGINE :
					// Taille standard
					if (($this->get_largeur_standard() > 0) && ($this->get_hauteur_standard() > 0)) {
						list($delta_l, $delta_h) = $this->calculer_delta($image, $this->get_largeur_standard(), $this->get_hauteur_standard());
						$largeur = $this->get_largeur_standard();
						$hauteur = $this->get_hauteur_standard();
					}
					elseif (($this->get_largeur_standard() > 0) && ($this->get_hauteur_standard() <= 0)) {
						$delta_l = 0;$delta_h = 0;
						$largeur = $this->get_largeur_standard();
						$hauteur = $image->get_hauteur() * (float) (((float) $this->get_largeur_standard()) / ((float) max($image->get_largeur(),1)));
					}
					elseif (($this->get_largeur_standard() <= 0) && ($this->get_hauteur_standard() > 0)) {
						$delta_l = 0;$delta_h = 0;
						$hauteur = $this->get_hauteur_standard();
						$largeur = $image->get_largeur() * (float) (((float) $this->get_hauteur_standard()) / ((float) max($image->get_hauteur(),1)));
					}
					if (($this->get_largeur_standard() > 0) || ($this->get_hauteur_standard() > 0)) {
						$image->retailler($largeur, $hauteur, $delta_l, $delta_h);
					}
					$status = @copy($image->get_src(), $this->get_dest());
					if ($status) {@unlink($this->get_src());}
					// Taille réduite
					$this->generer_reduite($image);
					break;
				case _TYPE_AJUSTEMENT_SANS :
				default :
					// Pas de retaillage : on effectue une simple copie
					$status = @copy($image->get_src(), $this->get_dest());
					if ($status) {@unlink($this->get_src());}
					// Taille réduite
					$this->generer_reduite($image);
					break;
			}
		}
		
		public function set_largeur_standard($param) {$this->largeur_standard = (int) $param;}
		public function set_hauteur_standard($param) {$this->hauteur_standard = (int) $param;}
		public function set_largeur_reduite($param) {$this->largeur_reduite = (int) $param;}
		public function set_hauteur_reduite($param) {$this->hauteur_reduite = (int) $param;}
		public function get_src_reduite() {return $this->src_reduite;}
		public function get_src() {return $this->src;}
		public function get_dest_reduite() {return $this->dest_reduite;}
		public function get_dest() {return $this->dest;}
		public function get_ext() {return $this->ext;}
		public function get_largeur() {return $this->largeur;}
		public function get_hauteur() {return $this->hauteur;}
		public function get_largeur_standard() {return $this->largeur_standard;}
		public function get_hauteur_standard() {return $this->hauteur_standard;}
		public function get_largeur_reduite() {return $this->largeur_reduite;}
		public function get_hauteur_reduite() {return $this->hauteur_reduite;}

		private function retailler($nouvelle_largeur, $nouvelle_hauteur, $delta_largeur, $delta_hauteur, $reduite = false) {
			$ret = false;
			if (!($this->is_null())) {
				$src_r = null;
				if ((!(strcmp($this->get_ext(), _UPLOAD_EXTENSION_JPG))) || (!(strcmp($this->get_ext(), _UPLOAD_EXTENSION_JPEG)))) {
					$src_r = imagecreatefromjpeg($this->get_src());
				}
				elseif (!(strcmp($this->get_ext(), _UPLOAD_EXTENSION_PNG))) {
					$src_r = imagecreatefrompng($this->get_src());
				}
				elseif (!(strcmp($this->get_ext(), _UPLOAD_EXTENSION_GIF))) {
					$src_r = imagecreatefromgif($this->get_src());
				}
				if ($src_r) {
					if (($this->get_ext() == _UPLOAD_EXTENSION_JPG) || ($this->get_ext() == _UPLOAD_EXTENSION_JPEG)) {
						$dst_r = ImageCreateTrueColor($nouvelle_largeur, $nouvelle_hauteur);
						if ($dst_r) {
							imagecopyresampled($dst_r, $src_r,
												0, 0, 
												$delta_largeur, $delta_hauteur, 
												$nouvelle_largeur, $nouvelle_hauteur, 
												$this->get_largeur() - (2*$delta_largeur), $this->get_hauteur() - (2*$delta_hauteur));
							$qualite = ($reduite)?(self::qualite_jpg_reduite):(self::qualite_jpg);
							$ret = imagejpeg($dst_r, $this->get_src(), $qualite);
							// Mise à jour des nouvelles dimensions
							$this->largeur = $nouvelle_largeur;
							$this->hauteur = $nouvelle_hauteur;
							imagedestroy($dst_r);
						}
					}
					elseif ($this->get_ext() == _UPLOAD_EXTENSION_PNG) {
						$src_alpha = $this->png_has_transparency($this->get_src());
						$dst_r = ImageCreateTrueColor($nouvelle_largeur, $nouvelle_hauteur);
						if ($dst_r) {
							if ($src_alpha) {
								imagealphablending( $dst_r, false );
								imagesavealpha( $dst_r, true );
							}
							imagecopyresampled($dst_r, $src_r,
												0, 0, 
												$delta_largeur, $delta_hauteur, 
												$nouvelle_largeur, $nouvelle_hauteur, 
												$this->get_largeur() - (2*$delta_largeur), $this->get_hauteur() - (2*$delta_hauteur));
							/* En cas d'image non transparente on reduit à une image avec palette (pb de taille) */
							if (!$src_alpha) {
								$tmp = ImageCreateTrueColor($nouvelle_largeur, $nouvelle_hauteur);
								ImageCopyMerge($tmp, $dst_r, 0, 0, 0, 0, $nouvelle_largeur, $nouvelle_hauteur, 100);
								ImageTrueColorToPalette($dst_r, false, 8192);
								ImageColorMatch($tmp, $dst_r);
								ImageDestroy($tmp );
							}
							$qualite = ($reduite)?(self::qualite_png_reduite):(self::qualite_png);
							$ret = imagepng($dst_r, $this->get_src(), $qualite);
							// Mise à jour des nouvelles dimensions
							$this->largeur = $nouvelle_largeur;
							$this->hauteur = $nouvelle_hauteur;
							imagedestroy($dst_r);
						}
					}
					elseif ($this->get_ext() == _UPLOAD_EXTENSION_GIF) {
						$dst_r = ImageCreateTrueColor($nouvelle_largeur, $nouvelle_hauteur);
						if ($dst_r) {
							imagecopyresampled($dst_r, $src_r,
												0, 0, 
												$delta_largeur, $delta_hauteur, 
												$nouvelle_largeur, $nouvelle_hauteur, 
												$this->get_largeur() - (2*$delta_largeur), $this->get_hauteur() - (2*$delta_hauteur));
							$ret = imagegif($dst_r, $this->get_src());
							// Mise à jour des nouvelles dimensions
							$this->largeur = $nouvelle_largeur;
							$this->hauteur = $nouvelle_hauteur;
							imagedestroy($dst_r);
						}
					}
					imagedestroy($src_r);
				}
			}
			return $ret;
		}
		
		private function generer_reduite(&$image) {
			if (($this->get_largeur_reduite() > 0) && ($this->get_hauteur_reduite() > 0)) {
				list($delta_l, $delta_h) = $this->calculer_delta($image, $this->get_largeur_reduite(), $this->get_hauteur_reduite());
				$largeur = $this->get_largeur_reduite();
				$hauteur = $this->get_hauteur_reduite();
			}
			elseif (($this->get_largeur_reduite() > 0) && ($this->get_hauteur_reduite() <= 0)) {
				$delta_l = 0;$delta_h = 0;
				$largeur = $this->get_largeur_reduite();
				$hauteur = $image->get_hauteur() * (float) (((float) $this->get_largeur_reduite()) / ((float) max($image->get_largeur(),1)));
			}
			elseif (($this->get_largeur_reduite() <= 0) && ($this->get_hauteur_reduite() > 0)) {
				$delta_l = 0;$delta_h = 0;
				$hauteur = $this->get_hauteur_reduite();
				$largeur = $image->get_largeur() * (float) (((float) $this->get_hauteur_reduite()) / ((float) max($image->get_hauteur(),1)));
			}
			if (($this->get_largeur_reduite() > 0) || ($this->get_hauteur_reduite() > 0)) {
				$image->retailler($largeur, $hauteur, $delta_l, $delta_h);
				$status = @rename($image->get_src(), $this->get_dest_reduite());
				if ($status) {@unlink($this->get_src_reduite());}
			}
		}
		private function calculer_delta(&$image, $largeur, $hauteur) {
			if ($image->get_hauteur() == 0) {$rapport_1 = 1;}
			else {$rapport_1 = (float) (((float) $image->get_largeur()) / ((float) $image->get_hauteur()));}
			if ($hauteur == 0) {$rapport_0 = 1;}
			else {$rapport_0 = (float) (((float) $largeur) / ((float) $hauteur));}
			if ($rapport_0 > $rapport_1) {
				$delta_l = 0;
				$delta_h = (int) (($image->get_hauteur() * $largeur - $image->get_largeur() * $hauteur) / (2 * max($largeur, 1)));
			}
			elseif ($rapport_0 < $rapport_1) {
				$delta_l = (int) (($image->get_largeur() * $hauteur - $image->get_hauteur() * $largeur) / (2 * max($hauteur,1)));
				$delta_h = 0;
			}
			else {
				$delta_l = 0;
				$delta_h = 0;
			}
			return array($delta_l, $delta_h);
		}

		// Grand merci à http://www.jonefox.com/ !!!
		private function png_has_transparency($filename) {
			if (strlen($filename) == 0 || !file_exists($filename)) return false;
			if (ord(file_get_contents($filename, false, null, 25, 1)) & 4) return true;
			$contents = file_get_contents($filename);
			if (stripos($contents, 'PLTE') !== false && stripos($contents, 'tRNS') !== false) return true;
			return false;
		}
	}

	$session = new session();
	if (is_null($session)) {
		header("Location: "._SESSION_URL_FERMETURE);
		exit;
	}

	$session->check_session();

	$page = $session->get_session_param(_SESSION_PARAM_PAGE);
	if (strlen($page) == 0) {
		$session->fermer_session();
		header("HTTP/1.0 404 Not Found");
		exit;
	}

	$param = new param();
	$id_image = $param->post("id_image");
	if (strlen($id_image) == 0) {
		$session->fermer_session();
		header("HTTP/1.0 404 Not Found");
		exit;
	}

	$src_image = $param->post("src_image");
	if (strlen($src_image) == 0) {
		$session->fermer_session();
		header("HTTP/1.0 404 Not Found");
		exit;
	}
	
	if (!(strcmp($src_image, _XML_SOURCE_SITE))) {
		$fichier_xml = _XML_PATH._XML_MEDIA._XML_EXT;
	}
	elseif (!(strcmp($src_image, _XML_SOURCE_PAGE))) {
		$fichier_xml = _XML_PATH_PAGES.$page."/"._XML_MEDIA._XML_EXT;
	}
	elseif (!(strcmp($src_image, _XML_SOURCE_MODULE))) {
		$fichier_xml = _XML_PATH_MODULES._XML_MEDIA._XML_EXT;
	}
	elseif (!(strncmp($src_image, _XML_SOURCE_LIBRAIRIE, strlen(_XML_SOURCE_LIBRAIRIE)))) {
		$nom_librairie = substr($src_image, strlen(_XML_SOURCE_LIBRAIRIE)+1);
		$fichier_xml = _XML_PATH_LIBRAIRIE.$nom_librairie."/"._XML_MEDIA._XML_EXT;
	}
	else {
		$session->fermer_session();
		header("HTTP/1.0 404 Not Found");
		exit;
	}

	$type_ajustement = (int) $param->post("ajustement");

	// On teste la présence d'un fichier dans le dossier d'upload
	$src_1 = null;
	$ext_1 = null;
	$upload_path = getcwd()."/"._UPLOAD_DOSSIER;
	$fichier_jpg = $upload_path._UPLOAD_FICHIER."."._UPLOAD_EXTENSION_JPG;
	$fichier_png = $upload_path._UPLOAD_FICHIER."."._UPLOAD_EXTENSION_PNG;
	$fichier_gif = $upload_path._UPLOAD_FICHIER."."._UPLOAD_EXTENSION_GIF;
	if (file_exists($fichier_jpg)) {
		$src_1 = $fichier_jpg;
		$ext_1 = _UPLOAD_EXTENSION_JPG;
	}
	elseif (file_exists($fichier_png)) {
		$src_1 = $fichier_png;
		$ext_1 = _UPLOAD_EXTENSION_PNG;
	}
	elseif (file_exists($fichier_gif)) {
		$src_1 = $fichier_gif;
		$ext_1 = _UPLOAD_EXTENSION_GIF;
	}

	$img_1 = new fichier_image($src_1, null, null, null, $ext_1);
	if (!($img_1->is_null())) {
		$xml_media = new xml_media();
		$xml_media->ouvrir($src_image, $fichier_xml);
		$img_media = $xml_media->get_image($id_image);
		if ($img_media) {
			$src_0 = $img_media->get_src();
			$src_reduite_0 = $img_media->get_src_reduite();
			$dest_0 = $img_media->get_dest();
			$dest_reduite_0 = $img_media->get_dest_reduite();
			$ext_0 = $img_media->get_extension();
			$img_0 = new fichier_image($src_0, $src_reduite_0, $dest_0, $dest_reduite_0, $ext_0);
			if (!($img_0->is_null())) {
				$img_0->set_largeur_standard($img_media->get_width_standard());
				$img_0->set_hauteur_standard($img_media->get_height_standard());
				$img_0->set_largeur_reduite($img_media->get_width_reduite());
				$img_0->set_hauteur_reduite($img_media->get_height_reduite());
				$img_0->remplacer($img_1, $type_ajustement);
			}
		}
	}

	// Redirection finale
	$id_tab = $param->post(_PARAM_FRAGMENT);
	$ret_page = preparer_redirection($session, $id_tab);
	header("Location: ".$ret_page);
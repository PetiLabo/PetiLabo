<?php
	require_once "inc/path.php";

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
	$xml_media = new xml_media();
	$xml_media->ouvrir($src_image, $fichier_xml);
	$img_media = $xml_media->get_image($id_image);
	if ($img_media) {$img_media->set_vide();}

	// Redirection finale
	$id_tab = $param->post(_PARAM_FRAGMENT);
	$ret_page = preparer_redirection($session, $id_tab);
	header("Location: ".$ret_page);
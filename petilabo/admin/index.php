<?php
	require_once "inc/path.php";
	inclure_admin("moteur_admin");

	$session = new session();
	if (is_null($session)) {
		header("Location: "._SESSION_URL_FERMETURE);
		exit;
	}
	
	$session->check_session();
	
	$page = $session->get_session_param(_SESSION_PARAM_PAGE);
	if (strlen($page) == 0) {
		$session->fermer_session();
		exit;
	}
	$fragment = $session->get_session_param(_SESSION_PARAM_FRAGMENT);

	$moteur_adm = new moteur_admin($page, $fragment);
	$moteur_adm->ouvrir_entete();
	$moteur_adm->ecrire_entete();
	$moteur_adm->fermer_entete();

	$moteur_adm->ouvrir_corps();
	$moteur_adm->ecrire_corps();
	$moteur_adm->fermer_corps();
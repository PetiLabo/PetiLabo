<?php

define("_DB_VISITES_TEMPORAIRE", "./analitix/db.tmp");
define("_DB_VISITES_DUREE_ARCHIVAGE", "31");

define("_DB_VISITES_INDICATEUR_MOBILE", "M");
define("_DB_VISITES_INDICATEUR_REFERER", "R");

define("_DB_VISITES_REFERER_DIRECT", "D");
define("_DB_VISITES_REFERER_SELF", "S");

class Visites {
	public static $IP_a_bloquer = array();
	public static function IP_a_bloquer(&$arg) {self::$IP_a_bloquer = $arg;}
	public static function Ajouter_visite($nom_page, $langue) {
		// Récupération des infos
		if (strlen($nom_page) == 0) {return;}
		$ip = self::Get_adresse_ip();
		if (strlen($ip) == 0) {return;}
		if (in_array($ip, self::$IP_a_bloquer)) {return;}
		$agent = self::Get_user_agent();
		if (strlen($agent) == 0) {return;}
		if (self::Is_bot($agent)) {return;}
		$referer = self::Get_referer();
		if (self::Is_spam($referer)) {return;}
		$ip = ((self::Is_mobile($agent))?_DB_VISITES_INDICATEUR_MOBILE:"").$ip;
		$ip = strtoupper($langue).$ip;
		$ip .= _DB_VISITES_INDICATEUR_REFERER.(self::Beautify_referer($referer));
		$date_courante = date("ymd");
		$date_peremtion = date("ymd", strtotime("-"._DB_VISITES_DUREE_ARCHIVAGE." days"));

		// Stockage dans le fichier db de la page
		$nom_db = _DB_PATH_ROOT.$nom_page._DB_EXT;
		$tmp = @gzopen(_DB_VISITES_TEMPORAIRE, "w");
		if (!($tmp)) {return;}
		// if (!(@flock($tmp, LOCK_EX))) {@fclose($tmp);return;}
		$db_a_jour = false;
		$fichier = @gzopen($nom_db, "r");
		if ($fichier) {
			while (!(@gzeof($fichier))) { 
				$ligne = @gzgets($fichier);
				$champs = explode("|", $ligne);
				if (count($champs) != 3) {continue;}
				list($date_db, $ip_db, $nb_db) = $champs;
				if (!(preg_match("/^[0-9]{6}$/", $date_db))) {continue;}
				if ($date_db < $date_peremtion) {continue;}
				$nb_visites = (int) $nb_db;
				if (($nb_visites < 1) || ($nb_visites > 98)) {continue;}
				if ((!(strcmp($date_db, $date_courante))) && (!(strcmp($ip_db, $ip)))) {$nb_visites += 1;$db_a_jour = true;}
				@gzputs($tmp, $date_db."|".$ip_db."|".$nb_visites."\n");
			}
			@gzclose($fichier);
		}
		if (!($db_a_jour)) {@gzputs($tmp, $date_courante."|".$ip."|1\n");}
		// @flock($tmp, LOCK_UN);
		@gzclose($tmp);
		@rename(_DB_VISITES_TEMPORAIRE, $nom_db);
		@chmod($nom_db, 0700);
	}

	private static function Get_adresse_ip() {
		$adresse_ip = null;
		if (isset($_SERVER['HTTP_CLIENT_IP'])) {$adresse_ip = $_SERVER['HTTP_CLIENT_IP'];}
		else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {$adresse_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];}
		else if(isset($_SERVER['HTTP_X_FORWARDED'])) {$adresse_ip = $_SERVER['HTTP_X_FORWARDED'];}
		else if(isset($_SERVER['HTTP_FORWARDED_FOR'])) {$adresse_ip = $_SERVER['HTTP_FORWARDED_FOR'];}
		else if(isset($_SERVER['HTTP_FORWARDED'])) {$adresse_ip = $_SERVER['HTTP_FORWARDED'];}
		else if(isset($_SERVER['REMOTE_ADDR'])) {$adresse_ip = $_SERVER['REMOTE_ADDR'];}
		return $adresse_ip;
	}
	private static function Get_user_agent() {
        $agent = (isset($_SERVER['HTTP_USER_AGENT']))?strtolower($_SERVER['HTTP_USER_AGENT']):null;
		return $agent;
	}
	private static function Get_referer() {
        $referer = (isset($_SERVER['HTTP_REFERER']))?strtolower($_SERVER['HTTP_REFERER']):null;
		return filter_var($referer, FILTER_SANITIZE_URL);
	}
	private static function Beautify_referer($referer) {
		if (!(strcmp($referer, ""))) {return _DB_VISITES_REFERER_DIRECT;}
        $self = strtolower((isset($_SERVER['HTTP_HOST']))?strtolower($_SERVER['HTTP_HOST']):null);
		$host = parse_url($referer, PHP_URL_HOST);
		return ((strcmp($self, $host)))?$host:_DB_VISITES_REFERER_SELF;
	}
	private static function Is_spam($referer) {
		if (strlen($referer) == 0) {return false;}
		if (filter_var($referer, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {return true;}
		return (preg_match('~(buttons\-for\-website\.|darodar\.|econom\.co|humanorightswatch\.|ilovevital\.|o\-o\-6\-o\-o\.|priceg\.|semalt\.)~', $referer));
	}
	private static function Is_bot($agent) {
		return ((strlen($agent) > 0) && (preg_match('~(agent|amzn|amazon|archive|bot|catch|collect|copier|crawler|download|extract|facebook|feed|google|grabber|hack|holmes|htdig|larbin|msn|preview|search|seek|slurp|snagger|snif|spider|spyder|strip|suck|swipe|tineye|walker|worm|yahoo|yacy|yeti)~', $agent)));
	}
	private static function Is_mobile($agent) { // Source : http://detectmobilebrowsers.com/
		return (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$agent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($agent,0,4)));
	}
}
<?php
require_once("models/Survey.inc.php");
require_once("models/Response.inc.php");

class Database {

	private $connection;

	/**
	 * Ouvre la base de données. Si la base n'existe pas elle
	 * est créée à l'aide de la méthode createDataBase().
	 */
	public function __construct() {


		$this->connection = new PDO("sqlite:database.sqlite");
		if (!$this->connection) die("impossible d'ouvrir la base de données");

		$q = $this->connection->query('SELECT name FROM sqlite_master WHERE type="table"');

		if (count($q->fetchAll())==0) {
			$this->createDataBase();
		}
	}


	/**
	 * Crée la base de données ouverte dans la variable $connection.
	 * Elle contient trois tables :
	 * - une table users(nickname char(20), password char(50));
	 * - une table surveys(id integer primary key autoincrement,
	 *						owner char(20), question char(255));
	 * - une table responses(id integer primary key autoincrement,
	 *		id_survey integer,
	 *		title char(255),
	 *		count integer);
	 */
	private function createDataBase() {
		/* TODO  */
	}

	/**
	 * Vérifie si un pseudonyme est valide, c'est-à-dire,
	 * s'il contient entre 3 et 10 caractères et uniquement des lettres.
	 *
	 * @param string $nickname Pseudonyme à vérifier.
	 * @return boolean True si le pseudonyme est valide, false sinon.
	 */
	private function checkNicknameValidity($nickname) {
		/* TODO  */
		return true;
	}

	/**
	 * Vérifie si un mot de passe est valide, c'est-à-dire,
	 * s'il contient entre 3 et 10 caractères.
	 *
	 * @param string $password Mot de passe à vérifier.
	 * @return boolean True si le mot de passe est valide, false sinon.
	 */
	private function checkPasswordValidity($password) {
		/* TODO  */
		return true;
	}

	/**
	 * Vérifie la disponibilité d'un pseudonyme.
	 *
	 * @param string $nickname Pseudonyme à vérifier.
	 * @return boolean True si le pseudonyme est disponible, false sinon.
	 */
	private function checkNicknameAvailability($nickname) {
		/* TODO  */
		return false;
	}

	/**
	 * Vérifie qu'un couple (pseudonyme, mot de passe) est correct.
	 *
	 * @param string $nickname Pseudonyme.
	 * @param string $password Mot de passe.
	 * @return boolean True si le couple est correct, false sinon.
	 */
	public function checkPassword($nickname, $password) {
	    $nickname = $this->connection->quote($nickname);
		$query = "SELECT password FROM users WHERE nickname=$nickname";
		$stmt = $this->connection->query($query);
		
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		
		return !empty($result) && password_verify($password, $result['password']);
	}

	/**
	 * Ajoute un nouveau compte utilisateur si le pseudonyme est valide et disponible et
	 * si le mot de passe est valide. La méthode peut retourner un des messages d'erreur qui suivent :
	 * - "Le pseudo doit contenir entre 3 et 10 lettres.";
	 * - "Le mot de passe doit contenir entre 3 et 10 caractères.";
	 * - "Le pseudo existe déjà.".
	 *
	 * @param string $nickname Pseudonyme.
	 * @param string $password Mot de passe.
	 * @return boolean|string True si le couple a été ajouté avec succès, un message d'erreur sinon.
	 */
	public function addUser($nickname, $password) {
		/* TODO  */
	    if(!preg_match('~^[a-zA-Z]{3,10}$~', $nickname)){
	        return "Le pseudo doit contenir entre 3 et 10 lettres.";
	    }
	    if (strlen($password)>10 || strlen($password)<3){
	        return "Le mot de passe doit contenir entre 3 et 10 caract�res.";
	    }
	    //
	    $query =  "SELECT count(*) FROM users WHERE nickname = ? ";
	    $stmt = $this->connection->prepare($query);
	    $stmt->bindParam(1, $nickname, PDO::PARAM_STR, 13);
	    $stmt->execute();
	    //$stmt->bindColumn(1, $nom);	// $stmt->bindColumn('name', $nom);
	    $userExists = $stmt->fetch(PDO::FETCH_ASSOC)['count(*)'];		// $tabRows = $stmt->fetchAll();
	    $stmt->closeCursor();
	    
	    if ($userExists) return 'Username already taken';
	    
	    //add to database
	    $password = password_hash($password, PASSWORD_BCRYPT);
	    
	    $query =  "insert into users (nickname, password) values ( ? , ?)";
	    $stmt = $this->connection->prepare($query);
	    $stmt->bindParam(1, $nickname, PDO::PARAM_STR, 13);
	    $stmt->bindParam(2, $password, PDO::PARAM_STR, 13);
	    $stmt->execute();
	    return $stmt->rowCount() ? true :  'Erreur dans la base de donn�e';
	    
	}

	/**
	 * Change le mot de passe d'un utilisateur.
	 * La fonction vérifie si le mot de passe est valide. S'il ne l'est pas,
	 * la fonction retourne le texte 'Le mot de passe doit contenir entre 3 et 10 caractères.'.
	 * Sinon, le mot de passe est modifié en base de données et la fonction retourne true.
	 *
	 * @param string $nickname Pseudonyme de l'utilisateur.
	 * @param string $password Nouveau mot de passe.
	 * @return boolean|string True si le mot de passe a été modifié, un message d'erreur sinon.
	 */
	public function updateUser($nickname, $password) {
		/* TODO  */
		return true;
	}

	/**
	 * Sauvegarde un sondage dans la base de donnée et met à jour les indentifiants
	 * du sondage et des réponses.
	 *
	 * @param Survey $survey Sondage à sauvegarder.
	 * @return boolean True si la sauvegarde a été réalisée avec succès, false sinon.
	 */
	public function saveSurvey(&$survey) {
		/* TODO  */
		return true;
	}

	/**
	 * Sauvegarde une réponse dans la base de donnée et met à jour son indentifiant.
	 *
	 * @param Survey $response Réponse à sauvegarder.
	 * @return boolean True si la sauvegarde a été réalisée avec succès, false sinon.
	 */
	private function saveResponse(&$response) {
		/* TODO  */
		return true;
	}

	/**
	 * Charge l'ensemble des sondages créés par un utilisateur.
	 *
	 * @param string $owner Pseudonyme de l'utilisateur.
	 * @return array(Survey)|boolean Sondages trouvés par la fonction ou false si une erreur s'est produite.
	 */
	public function loadSurveysByOwner($owner) {
		/* TODO  */
	}

	/**
	 * Charge l'ensemble des sondages dont la question contient un mot clé.
	 *
	 * @param string $keyword Mot clé à chercher.
	 * @return array(Survey)|boolean Sondages trouvés par la fonction ou false si une erreur s'est produite.
	 */
	public function loadSurveysByKeyword($keyword) {
		/* TODO  */
	}


	/**
	 * Enregistre le vote d'un utilisateur pour la réponse d'indentifiant $id.
	 *
	 * @param int $id Identifiant de la réponse.
	 * @return boolean True si le vote a été enregistré, false sinon.
	 */
	public function vote($id) {
		/* TODO  */
	}

	/**
	 * Construit un tableau de sondages à partir d'un tableau de ligne de la table 'surveys'.
	 * Ce tableau a été obtenu à l'aide de la méthode fetchAll() de PDO.
	 *
	 * @param array $arraySurveys Tableau de lignes.
	 * @return array(Survey)|boolean Le tableau de sondages ou false si une erreur s'est produite.
	 */
	private function loadSurveys($arraySurveys) {
		$surveys = array();
		/* TODO  */
		return $surveys;
	}

	/**
	 * Construit un tableau de réponses à partir d'un tableau de ligne de la table 'responses'.
	 * Ce tableau a été obtenu à l'aide de la méthode fetchAll() de PDO.
	 *
	 * @param array $arraySurveys Tableau de lignes.
	 * @return array(Response)|boolean Le tableau de réponses ou false si une erreur s'est produite.
	 */
	private function loadResponses(&$survey, $arrayResponses) {
		/* TODO  */
	}

}

?>

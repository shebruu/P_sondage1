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
		if(!preg_match('~^[a-zA-Z]{3,10}$~', $nickname)){
	        return false;
	    }
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
		if (strlen($password)>10 || strlen($password)<3){
	        return false;
	    }
		return true;
	}

	/**
	 * Vérifie la disponibilité d'un pseudonyme.
	 *
	 * @param string $nickname Pseudonyme à vérifier.
	 * @return boolean True si le pseudonyme est disponible, false sinon.
	 */
	private function checkNicknameAvailability($nickname) {
		$query =  "SELECT count(*) FROM users WHERE nickname = ? ";
	    $stmt = $this->connection->prepare($query);
	    $stmt->bindParam(1, $nickname, PDO::PARAM_STR, 13);
	    $stmt->execute();
	    $userExists = $stmt->fetch(PDO::FETCH_ASSOC)['count(*)'];		
	    $stmt->closeCursor();
	    return  !$userExists;
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
	    if(!$this->checkNicknameValidity($nickname)){
	        return "Le pseudo doit contenir entre 3 et 10 lettres.";
	    }
	    if (!$this->checkPasswordValidity($password)){
	        return "Le mot de passe doit contenir entre 3 et 10 caract�res.";
	    }
	    if (!$this->checkNicknameAvailability($nickname)){
	        return 'Username already taken';
	    }
	    
	    //add to database
	    $password = password_hash($password, PASSWORD_BCRYPT);
	    
	    $query =  "insert into users (nickname, password) values ( ? , ?)";
	    $stmt = $this->connection->prepare($query);
	    $stmt->bindParam(1, $nickname, PDO::PARAM_STR, 10);
	    $stmt->bindParam(2, $password, PDO::PARAM_STR, 10);
	    $result = $stmt->execute();
	    return ($result && $stmt->rowCount()) ? true :  'Erreur dans la base de donn�e';
	    
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
	    if (!$this->checkPasswordValidity($password)){
	        return "Le mot de passe doit contenir entre 3 et 10 caract�res.";
	    }
	    $password = password_hash($password, PASSWORD_BCRYPT);
	    $query =  "UPDATE users set password = ? WHERE nickname = ? ";
	    $stmt = $this->connection->prepare($query);
	    $stmt->bindParam(1, $password, PDO::PARAM_STR);
	    $stmt->bindParam(2, $nickname, PDO::PARAM_STR);
	    
	    $result = $stmt->execute();
	    
	    if (!$result || $stmt->rowCount() != 1 ){
	        return "Erreur dans la base de donn�es";
	    }
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
	    $this->connection->beginTransaction();
	    //ajout de la question
	    $query =  "insert into surveys (owner, question) values ( ? ,  ? ) ";
	    $stmt = $this->connection->prepare($query);
	    $owner = $survey->getOwner();
	    $question = $survey->getQuestion();
	    $stmt->bindParam(1, $owner, PDO::PARAM_STR);
	    $stmt->bindParam(2, $question, PDO::PARAM_STR);
	    
	    $result = $stmt->execute();
	    
	    if (!$result || $stmt->rowCount() != 1 ){
	        //erreur
	        $this->connection->rollback();
	        return false;
	    }
	    //r�cup�ration de l'id du nouveau sondage
	    $survey->setId($this->connection->lastInsertId());
	    
	    //insertion des r�ponses
	    foreach ($survey->getResponses() as $response){
	        if ( !$this->saveResponse($response) ){
	            //erreur
	            $this->connection->rollback();
	            return false;	            
	        }
	    }
	    
	    $this->connection->commit();
		return true;
	}

	/**
	 * Sauvegarde une réponse dans la base de donnée et met à jour son indentifiant.
	 *
	 * @param Survey $response Réponse à sauvegarder.
	 * @return boolean True si la sauvegarde a été réalisée avec succès, false sinon.
	 */
	private function saveResponse(&$response) {
		//pr�paration de la requete d'insertion des r�ponses
	    $surveyId = $response->getSurvey()->getId();
	    $title = $this->connection->quote($response->getTitle());
	    $query =  "insert into responses (id_survey, title, count) values ($surveyId, $title, 0) ";
	    //insertion
	    $stmt = $this->connection->query($query);
	    
	    if ($stmt !== false && $stmt->rowCount() == 1) {
	       $response->setId($this->connection->lastInsertId());
	       return true;
	    } else {
	       return false;
	    }
	}

	/**
	 * Charge l'ensemble des sondages créés par un utilisateur.
	 *
	 * @param string $owner Pseudonyme de l'utilisateur.
	 * @return array(Survey)|boolean Sondages trouvés par la fonction ou false si une erreur s'est produite.
	 */
	public function loadSurveysByOwner($owner) {
	    $owner = $this->connection->quote(strtolower($owner));
		$query =  "SELECT * FROM surveys where OWNER = $owner";
	    $stmt = $this->connection->query($query);
	    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	    $stmt->closeCursor();
	    
	    return $this->loadSurveys($rows);
	}

	/**
	 * Charge l'ensemble des sondages dont la question contient un mot clé.
	 *
	 * @param string $keyword Mot clé à chercher.
	 * @return array(Survey)|boolean Sondages trouvés par la fonction ou false si une erreur s'est produite.
	 */
	public function loadSurveysByKeyword($keyword) {
	    
	    $keyword = $this->connection->quote(strtolower($keyword));
		$query =  "SELECT * FROM surveys WHERE INSTR(lower(question), $keyword)>0 ";
	    $stmt = $this->connection->query($query);
	    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	    $stmt->closeCursor();
	    
	    return $this->loadSurveys($rows);
	}


	/**
	 * Enregistre le vote d'un utilisateur pour la réponse d'indentifiant $id.
	 *
	 * @param int $id Identifiant de la réponse.
	 * @return boolean True si le vote a été enregistré, false sinon.
	 */
	public function vote($id) {
	    $query =  "UPDATE responses set count = count + 1 WHERE id = ? ";
	    $stmt = $this->connection->prepare($query);
	    $stmt->bindParam(1, $id, PDO::PARAM_INT);
	    
	    $result = $stmt->execute();
	    return ($result && $stmt->rowCount() == 1 );
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
		
		if(!is_array($arraySurveys)) return false;
		
		foreach ($arraySurveys as $row){
		    //create survey from data in row
		    $survey = new Survey($row['owner'], $row['question']);
		    $survey->setId($row['id']);
		    //get responses for this survey and add them to survey
		    $query =  "SELECT * FROM responses WHERE id_survey = ? ";
		    $stmt = $this->connection->prepare($query);
		    $stmt->bindParam(1, $row['id'], PDO::PARAM_INT);
		    $stmt->execute();
		    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		    $stmt->closeCursor();
		    
		    $this->loadResponses($survey, $rows);
		    //add this survey to this list of surveys
		    $surveys[] = $survey;
		}
		
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
	    if(!is_array($arrayResponses)) return false;
	    
	    foreach($arrayResponses as $row){
	        //create response from data in row
	        $response = new Response($survey, $row['title'], $row['count']);
	        $response->setId($row['id']);
	        //add response to this survey
	        $responses[] = $response;
	        $survey->addResponse($response);
	    }
	    $survey->computePercentages();
	    return $responses;
	}
	
	/**
	 * Construit un tableau de $nb de surveys
	 * 
	 * @param int $nb Nombre de surveys voulus
	 * @return array(Survey)|boolean Sondages trouvés par la fonction ou false si une erreur s'est produite.
	 */
	public function getRandomSurveys($nb){
	    $query =  "SELECT * FROM surveys ORDER BY RANDOM() LIMIT $nb ";
	    $stmt = $this->connection->query($query);
	    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	    $stmt->closeCursor();
	    
	    return $this->loadSurveys($rows);
	}

}

?>

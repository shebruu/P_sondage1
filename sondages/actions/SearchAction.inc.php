<?php

require_once("models/SurveysModel.inc.php");
require_once("actions/Action.inc.php");

class SearchAction extends Action {

	/**
	 * Construit la liste des sondages dont la question contient le mot clÃ©
	 * contenu dans la variable $_POST["keyword"]. Cette liste est stockÃ©e dans un modÃ¨le
	 * de type "SurveysModel". L'utilisateur est ensuite dirigÃ© vers la vue "ServeysView"
	 * permettant d'afficher les sondages.
	 *
	 * Si la variable $_POST["keyword"] est "vide", le message "Vous devez entrer un mot clÃ©
	 * avant de lancer la recherche." est affichÃ© Ã  l'utilisateur.
	 *
	 * @see Action::run()
	 */
	public function run() {
	    $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
	    
	    if (empty($keyword)){
	        $model = new MessageModel();
	        $model->setMessage('Vous devez entrer un mot clé avant de lancer la recherche.');  
	        $this->setView(getViewByName('Message'));
	        return;
	    }
	    
	    //récupérer les sondages dont la question contient le mot clé
	    $surveys = $this->database->loadSurveysByKeyword($keyword);
	    if ($surveys ===false){
	        $model = new MessageModel();
	        $model->setMessage('Erreur dans la recherche');
	        $this->setView(getViewByName('Message'));
	        return;
	    }
	    
	    $model = new SurveysModel();
	    $model->setSurveys($surveys);
	    $model->setLogin($this->getSessionLogin());
	    $this->setModel($model);
	    $this->setView(getViewByName('Surveys'));
	}

}

?>

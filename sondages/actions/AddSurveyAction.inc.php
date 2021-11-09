<?php

require_once("models/MessageModel.inc.php");
require_once("models/Survey.inc.php");
require_once("models/Response.inc.php");
require_once("actions/Action.inc.php");

class AddSurveyAction extends Action {

	/**
	 * Traite les données envoyées par le formulaire d'ajout de sondage.
	 *
	 * Si l'utilisateur n'est pas connecté, un message lui demandant de se connecter est affiché.
	 *
	 * Sinon, la fonction ajoute le sondage à la base de données. Elle transforme
	 * les réponses et la question à l'aide de la fonction PHP 'htmlentities' pour éviter
	 * que du code exécutable ne soit inséré dans la base de données et affiché par la suite.
	 *
	 * Un des messages suivants doivent être affichés à l'utilisateur :
	 * - "La question est obligatoire.";
	 * - "Il faut saisir au moins 2 réponses.";
	 * - "Merci, nous avons ajouté votre sondage.".
	 *
	 * Le visiteur est finalement envoyé vers le formulaire d'ajout de sondage pour lui
	 * permettre d'ajouter un nouveau sondage s'il le désire.
	 * 
	 * @see Action::run()
	 */
	public function run() {
	    
	    if ($this->getSessionLogin()===null) {
	        $this->setMessageView("Vous devez �tre authentifi�.");
	        return;
	    }
	   
	    $this->setModel(new MessageModel());
	    
	    if (empty($_POST['questionSurvey'])){
	        $this->getModel()->setMessage('La question est obligatoire');
	        
	    } else {
	        $survey = new Survey( $this->getSessionLogin(), htmlentities($_POST['questionSurvey']));
	        for ($i = 1; $i < 5; $i++){
	            if (!empty($_POST["responseSurvey$i"])){
	                $response = new Response($survey, htmlentities($_POST["responseSurvey$i"]));
	                $survey->addResponse($response);
	            }
	        }
	        if (count($survey->getResponses())<2){
	            $this->getModel()->setMessage('Il faut saisir au moins 2 r�ponses.');
	        } else {
	            //ajout dans la base de donn�es
	            $response = $this->database->saveSurvey($survey);
	            if ($response !== false) {
	                $this->getModel()->setMessage("Merci, nous avons ajout� votre sondage.");
	            } else {
	                $this->getModel()->setMessage("Une erreur s'est produite");
	            }
	        }
	    }
		$this->getModel()->setLogin($this->getSessionLogin());
		$this->setView(getViewByName("AddSurveyForm"));
	}

}

?>

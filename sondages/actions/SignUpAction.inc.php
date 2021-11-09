<?php

require_once("models/MessageModel.inc.php");
require_once("actions/Action.inc.php");

class SignUpAction extends Action {

	/**
	 * Traite les données envoyées par le formulaire d'inscription
	 * ($_POST['signUpLogin'], $_POST['signUpPassword'], $_POST['signUpPassword2']).
	 *
	 * Le compte est crée à l'aide de la méthode 'addUser' de la classe Database.
	 *
	 * Si la fonction 'addUser' retourne une erreur ou si le mot de passe et sa confirmation
	 * sont différents, on envoie l'utilisateur vers la vue 'SignUpForm' avec une instance
	 * de la classe 'MessageModel' contenant le message retourné par 'addUser' ou la chaîne
	 * "Le mot de passe et sa confirmation sont différents.";
	 *
	 * Si l'inscription est validée, le visiteur est envoyé vers la vue 'MessageView' avec
	 * un message confirmant son inscription.
	 *
	 * @see Action::run()
	 */
	public function run() {
	    $login = isset($_POST['signUpLogin']) ? $_POST['signUpLogin'] : '';
	    $pwd = isset($_POST['signUpPassword']) ? $_POST['signUpPassword'] : '';
	    $pwd_conf = isset($_POST['signUpPassword2']) ? $_POST['signUpPassword2'] : '';
	
		if ($pwd !== $pwd_conf){
		    //mots de passe diff�rents
		    $this->createSignUpFormView('Le mot de passe et sa confirmation sont diff�rents.');
		    return;
		}
		
		$result = $this->database->addUser($login, $pwd);
		if($result!==true){
		    //erreur dans la base de donn�es
		    $this->createSignUpFormView($result);
		    return;
		}
		$this->setModel(new MessageModel());
		$this->getModel()->setMessage('Inscription valid�e');
		$this->setView(getViewByName('Message'));
	}

	private function createSignUpFormView($message) {
		$this->setModel(new MessageModel());
		$this->getModel()->setMessage($message);
		$this->getModel()->setLogin($this->getSessionLogin());
		$this->setView(getViewByName("SignUpForm"));
	}

}


?>

<?php

require_once("models/Model.inc.php");
require_once("actions/Action.inc.php");

class LoginAction extends Action {

	/**
	 * Traite les données envoyées par le visiteur via le formulaire de connexion
	 * (variables $_POST['nickname'] et $_POST['password']).
	 * Le mot de passe est vérifié en utilisant les méthodes de la classe Database.
	 * Si le mot de passe n'est pas correct, on affecte la chaîne "erreur"
	 * à la variable $loginError du modèle. Si la vérification est réussie,
	 * le pseudo est affecté à la variable de session et au modèle.
	 *
	 * @see Action::run()
	 */
	public function run() {
	    $model = new Model();
	    
	    $nickname = $_POST['nickname'];
	    $password = $_POST['password'];
	    if ($this->database->checkPassword($nickname, $password)){
	        $this->setSessionLogin($nickname);
	        $model->setLogin($nickname);
	    } else {
	        $model->setLoginError('Erreur');  
	    }
	    $this->setModel($model);
	    $this->setView(getViewByName('Default'));
		
	}

}

?>

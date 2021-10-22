<?php
require_once("models/Model.inc.php");
require_once("actions/Action.inc.php");

class DefaultAction extends Action {

	/**
	 * Traite l'action par défaut. 
	 * Elle dirige l'utilisateur vers une page avec un contenu vide.
	 *
	 * @see Action::run()
	 */
	public function run() {
		$this->setModel(new Model());
		$this->getModel()->setLogin($this->getSessionLogin());
		$this->setView(getViewByName("Default"));
	}

}
?>

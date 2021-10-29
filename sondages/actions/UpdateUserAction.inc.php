<?php

require_once("models/MessageModel.inc.php");
require_once("actions/Action.inc.php");

class UpdateUserAction extends Action {

	/**
	 * Met à jour le mot de passe de l'utilisateur en procédant de la façon suivante :
	 *
	 * Si toutes les données du formulaire de modification de profil ont été postées
	 * ($_POST['updatePassword'] et $_POST['updatePassword2']), on vérifie que
	 * le mot de passe et la confirmation sont identiques.
	 * S'ils le sont, on modifie le compte avec les méthodes de la classe 'Database'.
	 *
	 * Si une erreur se produit, le formulaire de modification de mot de passe
	 * est affiché à nouveau avec un message d'erreur.
	 *
	 * Si aucune erreur n'est détectée, le message 'Modification enregistrée.'
	 * est affiché à l'utilisateur.
	 *
	 * @see Action::run()
	 */
	public function run() {
	    $model = new MessageModel();
	    
	    if(!empty($_POST['updatePassword']) || !empty($_POST['updatePassword2'])){
	        $model->setMessage('Veuillez compl�ter les deux champs.');
	        $this->setModel($model);
	        $this->setView(getViewByName('UpdateUserForm'));
	        return;
	    }
	        
	    $pwd = $_POST['updatePassword'];
	    $pwd_conf = $_POST['updatePassword2'];
	    
	    //mot de passe et comfirmation diff�rents
	    if($pwd != $pwd_conf){
	        $model->setMessage('Les mots de passe entr�s sont diff�rents');
	        $this->setModel($model);
	        $this->setView(getViewByName('UpdateUserForm'));
	        return;
	    }
	    
	    //sauver dans la base de donn�es
	    $response = $this->database->updateUser($this->getSessionLogin(), $pwd);
	    if ( $response === true){
	        $model->setMessage('Modification enregistr�e');
	        $this->setModel($model);
	        $this->setView(getViewByName('Default'));
	    } else {
	        $model->setMessage($response);
	        $this->setModel($model);
	        $this->setView(getViewByName('UpdateUserForm'));
	    }
	}

	private function createUpdateUserFormView($message) {
		$this->setModel(new MessageModel());
		$this->getModel()->setMessage($message);
		$this->getModel()->setLogin($this->getSessionLogin());
		$this->setView(getViewByName("UpdateUserForm"));
	}

}

?>

<?php
require_once("views/View.inc.php");

class RandomThreeView extends View {

	/**
	 * Affiche la liste de 3 sondages présents dans le modèle passée en paramètre.
	 * 
	 * Le modèle passé en paramètre est une instance de la classe 'SurveysModel'.
	 *
	 * @see View::displayBody()
	 */
	public function displayBody($model) {

		if (count($model->getSurveys())===0) {
			echo "Aucun sondage ne correspond Ã  votre recherche.";
			return;
		}

		foreach ($model->getSurveys() as $survey) {
			$survey->computePercentages();
			require("templates/survey.inc.php");
		}
	}
	
	/**
	 * Génère une page spécifique à l'affichage de 3 sondages aléatoires
	 * 
	 * @param Model $model représente les données à afficher
	 * 
	 * {@inheritDoc}
	 * @see View::run()
	 */
	public function run($model){
	    $login = $model->getLogin();
	    $surveys = $model->getSurveys();
	    require 'templates/randomThree.php';
	}

}
?>

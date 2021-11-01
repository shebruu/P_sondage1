<div class="survey">

<div class="question">
<?php echo $survey->getQuestion() ?>
</div>

<?php foreach ($survey->getResponses() as $response) { ?>
    <div class="response">
    	<span class="responseTitle"><?= $response->getTitle() ?></span>
    	<span><progress max="100" value="<?= $response->getPercentage() ?>" class="chart"></progress></span>
    	<span class="count"><?= $response->getPercentage() ?> %</span>
    	<form class="voteForm"><button class="submit">Voter</button></form>
    </div>
<?php }  ?>


</div>


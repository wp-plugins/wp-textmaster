<?php
function texmaster_admin_actions() {

	add_options_page("TextMaster", "TextMaster", 1, "Textmaster", "texmaster_admin");
}



function texmaster_admin() {

	if($_POST['textmaster_hidden'] == 'Y') {
		$textmaster_api_key = $_POST['textmaster_api_key'];
		update_option('textmaster_api_key', $textmaster_api_key);
		$textmaster_api_secret = $_POST['textmaster_api_secret'];
		update_option('textmaster_api_secret', $textmaster_api_secret);


		if ($_POST['use_traduction'] == 'Y')
			$textmaster_useTraduction = $_POST['use_traduction'];
		else
			$textmaster_useTraduction = 'N';
		update_option('textmaster_useTraduction', $textmaster_useTraduction);
		if ($_POST['use_readproof'] == 'Y')
			$textmaster_useReadproof = $_POST['use_readproof'];
		else
			$textmaster_useReadproof = 'N';
		update_option('textmaster_useReadproof', $textmaster_useReadproof);
		if ($_POST['textmaster_useRedaction'] == 'Y')
			$textmaster_useRedaction = $_POST['textmaster_useRedaction'];
		else
			$textmaster_useRedaction = 'N';
		update_option('textmaster_useRedaction', $textmaster_useRedaction);

		// params de redaction
		$textmaster_redactionLanguageLevel = $_POST['textmaster_redactionLanguageLevel'];
		update_option('textmaster_redactionLanguageLevel', $textmaster_redactionLanguageLevel);
		$textmaster_redactionLanguage = $_POST['textmaster_redactionLanguage'];
		update_option('textmaster_redactionLanguage', $textmaster_redactionLanguage);
		$textmaster_redactionCategorie = $_POST['textmaster_redactionCategorie'];
		update_option('textmaster_redactionCategorie', $textmaster_redactionCategorie);
		$textmaster_vocabularyType = $_POST['textmaster_vocabularyType'];
		update_option('textmaster_vocabularyType', $textmaster_vocabularyType);
		$textmaster_grammaticalPerson = $_POST['textmaster_grammaticalPerson'];
		update_option('textmaster_grammaticalPerson', $textmaster_grammaticalPerson);
		$textmaster_targetReaderGroup = $_POST['textmaster_targetReaderGroup'];
		update_option('textmaster_targetReaderGroup', $textmaster_targetReaderGroup);
		$textmaster_Template = $_POST['textmaster_Template'];
		update_option('textmaster_Template', $textmaster_Template);
		$textmaster_author = $_POST['textmaster_author'];
		update_option('textmaster_author', $textmaster_author);

		// params de relecture
		$textmaster_readproofLanguageLevel = $_POST['textmaster_readproofLanguageLevel'];
		update_option('textmaster_readproofLanguageLevel', $textmaster_readproofLanguageLevel);
		$textmaster_readproofLanguage = $_POST['textmaster_readproofLanguage'];
		update_option('textmaster_readproofLanguage', $textmaster_readproofLanguage);
		$textmaster_readproofCategorie = $_POST['textmaster_readproofCategorie'];
		update_option('textmaster_readproofCategorie', $textmaster_readproofCategorie);
		$textmaster_readproofBriefing = $_POST['textmaster_readproofBriefing'];
		update_option('textmaster_readproofBriefing', $textmaster_readproofBriefing);



		// params de traduction
		$textmaster_traductionLanguageLevel = $_POST['textmaster_traductionLanguageLevel'];
		update_option('textmaster_traductionLanguageLevel', $textmaster_traductionLanguageLevel);
		$textmaster_traductionLanguageSource = $_POST['textmaster_traductionLanguageSource'];
		update_option('textmaster_traductionLanguageSource', $textmaster_traductionLanguageSource);
		$textmaster_traductionLanguageDestination = $_POST['textmaster_traductionLanguageDestination'];
		update_option('textmaster_traductionLanguageDestination', $textmaster_traductionLanguageDestination);
		$textmaster_traductionCategorie = $_POST['textmaster_traductionCategorie'];
		update_option('textmaster_traductionCategorie', $textmaster_traductionCategorie);
		$textmaster_traductionBriefing = $_POST['textmaster_traductionBriefing'];
		update_option('textmaster_traductionBriefing', $textmaster_traductionBriefing);


		echo '<div class="updated" style="padding:10px;">'.__('Les paramètres ont été sauvegaradés.','textmaster').'</div>';
	} else {
		$textmaster_api_key = get_option('textmaster_api_key');
		$textmaster_api_secret = get_option('textmaster_api_secret');

		$textmaster_useRedaction = get_option('textmaster_useRedaction');
		$textmaster_useTraduction = get_option('textmaster_useTraduction');
		$textmaster_useReadproof = get_option('textmaster_useReadproof');

		// params de redaction
		$textmaster_redactionLanguageLevel = get_option('textmaster_redactionLanguageLevel');
		$textmaster_redactionLanguage = get_option('textmaster_redactionLanguage');
		$textmaster_redactionCategorie = get_option('textmaster_redactionCategorie');
		$textmaster_vocabularyType = get_option('textmaster_vocabularyType');
		$textmaster_grammaticalPerson = get_option('textmaster_grammaticalPerson');
		$textmaster_targetReaderGroup = get_option('textmaster_targetReaderGroup');
		$textmaster_Template = get_option('textmaster_Template');
		$textmaster_author = get_option('textmaster_author');

		// params de relecture
		$textmaster_readproofLanguageLevel = get_option('textmaster_readproofLanguageLevel');
		$textmaster_readproofLanguage = get_option('textmaster_readproofLanguage');
		$textmaster_readproofCategorie = get_option('textmaster_readproofCategorie');
		$textmaster_readproofBriefing = get_option('textmaster_readproofBriefing');
		if ($textmaster_readproofBriefing == '')
			$textmaster_readproofBriefing = __("Bonjour,\nMerci de procéder à la correction du texte. Il est impératif de conserver le style et le type de vocabulaire.\nMerci");

		// params de traduction
		$textmaster_traductionLanguageLevel = get_option('textmaster_traductionLanguageLevel');
		$textmaster_traductionLanguageSource = get_option('textmaster_traductionLanguageSource');
		$textmaster_traductionLanguageDestination = get_option('textmaster_traductionLanguageDestination');
		$textmaster_traductionCategorie = get_option('textmaster_traductionCategorie');
		$textmaster_traductionBriefing = get_option('textmaster_traductionBriefing');
		if ($textmaster_traductionBriefing == '')
			$textmaster_traductionBriefing = __("Bonjour,\nMerci de procéder à la traduction du texte. Il est impératif de conserver le style et le type de vocabulaire.\nMerci");
	}

	$tApi = new textmaster_api();
	$tApi->secretapi = get_option('textmaster_api_secret');
	$tApi->keyapi =  get_option('textmaster_api_key');

	$languageLevels['basic_language_level'] = __('Basic','textmaster');
	$languageLevels['standard_language_level'] = __('Standard','textmaster');
	$languageLevels['expert_language_level'] = __('Expert','textmaster');
	$languages = $tApi->getLanguages();
	$categories = $tApi->getCategories();

	?>
	<link href="<?php echo plugins_url('wp-textmaster/textmaster.css') ?>" rel="stylesheet" type="text/css" />
	<div class="wrap" id="textmaster_settings">
    <?php    echo "<h2>" . __( 'Paramétrage TextMaster' ,'textmaster') . "</h2>"; ?>
    <br/>
    <br/>
    <form name="textmaster_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="textmaster_hidden" value="Y">
        <ul class="tabs">
		<li class="t1"><a class="t1 tab" title="<?php _e('Général','textmaster'); ?>"><? _e('Général','textmaster')?></a></li>
		<li class="t2"><a class="t2 tab" title="<?php _e('Général','textmaster'); ?>"><? _e('Rédaction','textmaster')?></a></li>
		<li class="t3"><a class="t3 tab" title="<?php _e('Relecture','textmaster'); ?>"><? _e('Relecture','textmaster')?></a></li>
		<li class="t4"><a class="t4 tab" title="<?php _e('Traduction','textmaster'); ?>"><? _e('Traduction','textmaster')?></a></li>
		</ul>

		<!-- l'onglet général-->
		<div class="t1">
	    <table class="form-table">
        <tr valign="top">
        <td colspan="2">
        <h3><?php _e('Accès API','textmaster') ?></h3>
		</td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e("Api key: ", 'textmaster' ); ?></th>
        <td><input type="text" name="textmaster_api_key" value="<?php echo $textmaster_api_key; ?>" size="20"></td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e("Api secret: ",'textmaster' ); ?></th>
        <td><input type="text" name="textmaster_api_secret" value="<?php echo $textmaster_api_secret; ?>" size="20"></td>
        </tr>
         <tr valign="top">
        <td colspan="2">
         <?php _e('Vous trouverez les api_key et api_secret sur le site <a href="http://fr.textmaster.com/?pid=4f1db74529e1673829000009" target="_blank">TextMaster</a>, dans la rubrique plugins.','textmaster'); ?>
		</td>
        </tr>
        <tr valign="top">
        <td colspan="2">
        <h3><?php _e('Pour quelles activités ','textmaster') ?></h3>
		</td>
        </tr>
       <tr valign="top">
        <td colspan="2">
        <input type="checkbox" id="textmaster_useRedaction" name="textmaster_useRedaction" value="Y" <?php if($textmaster_useRedaction != 'N'){ echo "checked=\"checked\"";} ?>/>
        <?php _e("Rédaction ",'textmaster' ); ?>
		</td>
        </tr>
   		<tr valign="top">
        <td colspan="2">
        <input type="checkbox" id="use_readproof" name="use_readproof" value="Y" <?php if($textmaster_useReadproof != 'N'){ echo "checked=\"checked\"";} ?>/>
        <?php _e("Relecture ",'textmaster' ); ?>
		</td>
        </tr>
        <tr valign="top">
        <td colspan="2">
        <input type="checkbox" id="use_traduction" name="use_traduction" value="Y" <?php if($textmaster_useTraduction != 'N'){ echo "checked=\"checked\"";} ?>/>
        <?php _e("Traduction: ",'textmaster' ); ?>
		</td>
        </tr>
        </table>
        <br/>
		</div>

     	<!-- l'onglet rédaction -->
		<div class="t2">
		<table class="form-table">
			<tr valign="top">
	        <td colspan="2">
	        <h3><?php _e('Paramètres de rédaction par défaut ','textmaster') ?></h3>
			</td>
	        </tr>
	        <tr valign="top">
	        <th scope="row"><?php _e("Niveau de service: ",'textmaster' ); ?></th>
	        <td>
			<select name="textmaster_redactionLanguageLevel" style="width:235px;">
			<?php
	foreach($languageLevels as $key => $languageLevel)
	{
		if ($textmaster_redactionLanguageLevel == $key)
			echo '<option value="'.$key.'" selected="selected">'.$languageLevel.'</option>';
		else
			echo '<option value="'.$key.'">'.$languageLevel.'</option>';
	}
	?>
			</select>
			</td>
	        </tr>
	        <tr valign="top">
	        <th scope="row"><?php _e("Langue: ",'textmaster' ); ?></th>
	        <td>
	        <?
	if ($languages['message'] != '') {
		_e('Merci de v&eacute;rifier les api_key et api_secret de TextMaster','textmaster');
	}
	else
	{
		?>
				<select name="textmaster_redactionLanguage" style="width:235px;">
				<?php
		foreach($languages as $key => $language)
		{
			if ($textmaster_redactionLanguage == $key)
				echo '<option value="'.$key.'" selected="selected">'.$language.'</option>';
			else
				echo '<option value="'.$key.'">'.$language.'</option>';
		}
		?>
				</select>
				<?
	}
	?>
			</td>
	     	</tr>
	 		<tr valign="top">
	        <th scope="row"><?php _e("Categorie: ",'textmaster' ); ?></th>
	        <td>
		 	<?
	if ($categories['message'] != '') {
		_e('Merci de v&eacute;rifier les api_key et api_secret de TextMaster','textmaster');
	}
	else
	{
		?>
					<select name="textmaster_redactionCategorie" style="width:235px;">
					<?php
		foreach($categories as $key => $categorie)
		{
			if ($textmaster_redactionCategorie == $key)
				echo '<option value="'.$key.'" selected="selected">'.$categorie.'</option>';
			else
				echo '<option value="'.$key.'">'.$categorie.'</option>';
		}
		?>
				</select>
				<?
	}
	?>
        </td>
        </tr>
 		<tr valign="top">
        <th scope="row"><?php _e("Type de Vocabulaire:",'textmaster' ); ?></th>
        <td>
	 	<?
		if ($categories['message'] != '') {
			_e('Merci de v&eacute;rifier les api_key et api_secret de TextMaster','textmaster');
		}
		else
		{
			?>
			<select name="textmaster_vocabularyType" style="width:235px;">
			<?php
			$vocabulary_types['not_specified'] = __('Non spécifié','textmaster');
			$vocabulary_types['popular'] = __('Populaire','textmaster');
			$vocabulary_types['technical'] = __('Technique','textmaster');
			$vocabulary_types['fictional'] = __('Romancé','textmaster');

			foreach($vocabulary_types as $key => $vocabulary_type)
			{
				if ($textmaster_vocabularyType == $key)
					echo '<option value="'.$key.'" selected="selected">'.$vocabulary_type.'</option>';
				else
					echo '<option value="'.$key.'">'.$vocabulary_type.'</option>';
			}
			?>
					</select>
					<?
		}
		?>
        </td>
        </tr>
 		<tr valign="top">
        <th scope="row"><?php _e("Personne grammaticale:",'textmaster' ); ?></th>
        <td>
	 	<?
		if ($categories['message'] != '') {
			_e('Merci de v&eacute;rifier les api_key et api_secret de TextMaster','textmaster');
		}
		else
		{
			?>
				<select name="textmaster_grammaticalPerson" style="width:235px;">
				<?php
			$grammatical_persons['not_specified'] = __('Non spécifié','textmaster');
			$grammatical_persons['first_person_singular'] = __('Je > 1ère personne - Singulier','textmaster');
			$grammatical_persons['second_person_singular'] = __('Tu > 2ème Personne - Singulier','textmaster');
			$grammatical_persons['third_person_singular_masculine'] = __('Il > 3ème personne - Singulier Masculin','textmaster');
			$grammatical_persons['third_person_singular_feminine'] = __('Elle > 3ème personne - Singulier Féminin','textmaster');
			$grammatical_persons['third_person_singular_neuter'] = __('On > 3ème personne - Singulier Neutre','textmaster');
			$grammatical_persons['first_person_plural'] = __('Nous > 1ère personne - Pluriel','textmaster');
			$grammatical_persons['second_person_plural'] = __('Vous > 2ème Personne - Pluriel','textmaster');
			$grammatical_persons['third_person_plural'] = __('Ils/elles > 3ème Personne - Pluriel','textmaster');

			foreach($grammatical_persons as $key => $grammatical_person)
			{
				if ($textmaster_grammaticalPerson == $key)
					echo '<option value="'.$key.'" selected="selected">'.$grammatical_person.'</option>';
				else
					echo '<option value="'.$key.'">'.$grammatical_person.'</option>';
			}
			?>
						</select>
						<?
		}
		?>
        </td>
        </tr>
		<tr valign="top">
        <th scope="row"><?php _e("Public Ciblé:",'textmaster' ); ?></th>
        <td>
	 	<?
		if ($categories['message'] != '') {
			_e('Merci de v&eacute;rifier les api_key et api_secret de TextMaster','textmaster');
		}
		else
		{
			?>
					<select name="textmaster_targetReaderGroup" style="width:235px;">
					<?php
			$target_reader_groups['not_specified'] = __('Non spécifié','textmaster');
			$target_reader_groups['children'] = __('Enfants > 13 ans et moins','textmaster');
			$target_reader_groups['teenager'] = __('Adolescent > entre 14 et 18 ans','textmaster');
			$target_reader_groups['young_adults'] = __('Jeunes adultes > entre 19 et 29 ans','textmaster');
			$target_reader_groups['adults'] = __('Adultes > entre 30 et 59 ans','textmaster');
			$target_reader_groups['old_adults'] = __('Séniors > 60 ans et plus','textmaster');


			foreach($target_reader_groups as $key => $target_reader_group)
			{
				if ($textmaster_targetReaderGroup == $key)
					echo '<option value="'.$key.'" selected="selected">'.$target_reader_group.'</option>';
				else
					echo '<option value="'.$key.'">'.$target_reader_group.'</option>';
			}
			?>
							</select>
							<?
		}
		?>
        </td>
        </tr>
		<tr valign="top">
        <th scope="row"><?php _e("Auteur:",'textmaster' ); ?></th>
        <td>
	 	<?
		if ($categories['message'] != '') {
			_e('Merci de v&eacute;rifier les api_key et api_secret de TextMaster','textmaster');
		}
		else
		{
			?>
						<select name="textmaster_author" style="width:235px;">
						<?php
		 	$auteurs = $tApi->getAuteurs();


			foreach($auteurs as $auteur)
			{
				$auteurDesc = '';
				if ($auteur['description'] != '')
					$auteurDesc = ' - '.$auteur['description'];

				if ($textmaster_author == $auteur['id'])
					echo '<option value="'.$auteur['id'].'" selected="selected">'.$auteur['author_ref'].$auteurDesc.'</option>';
				else
					echo '<option value="'.$auteur['id'].'">'.$auteur['author_ref'].$auteurDesc.'</option>';
			}
			?>
								</select>
								<?
		}
		?>
        </td>
        </tr>
		<tr valign="top">
        <th scope="row"><?php _e("Mise en page",'textmaster' ); ?>:</th>
        <td>
	 	<?
		$templates = $tApi->getTemplates();
		if (count($templates) == 0) {
			_e('Merci de v&eacute;rifier les api_key et api_secret de TextMaster','textmaster');
		}
		else
		{
			if (get_post_meta($post->ID, 'textmasterTemplate', true) != '')
				$templateSelected = get_post_meta($post->ID, 'textmasterTemplate', true);
			else
				$templateSelected = 'Libre';


			echo '<ul style="display:inline-block;">';
			foreach($templates as $key => $template)
			{
				echo '<li style="width:150px;display:inline-block;min-height:340px;vertical-align:top;margin:10px;">';
				if ($textmaster_Template == $template['name'])
					$checked = 'checked="checked"';
				else
					$checked = '';
				echo '<input type="radio" name="textmaster_Template" id="textmaster_Template" value="'.$template['name'].'" '.$checked.'>';
				echo '<img src="'.$template['image_preview_path'].'" />';
				echo $template['description'];
				echo '</li>';
			}
			echo '</ul>';
		}
		?>
        </td>
        </tr>
		</table>
		<br/>
		</div>

		<!-- l'onglet relecture -->
		<div class="t3">
		<table class="form-table">
		<tr valign="top">
        <td colspan="2">
        <h3><?php _e('Paramètres de relecture par défaut ','textmaster') ?></h3>
		</td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e("Niveau de service: ",'textmaster' ); ?></th>
        <td>
		<select name="textmaster_readproofLanguageLevel" style="width:235px;">
		<?php
	foreach($languageLevels as $key => $languageLevel)
	{
		if ($textmaster_readproofLanguageLevel == $key)
			echo '<option value="'.$key.'" selected="selected">'.$languageLevel.'</option>';
		else
			echo '<option value="'.$key.'">'.$languageLevel.'</option>';
	}
	?>
		</select>
		</td>
        </tr>
        <tr valign="top">
        <th scope="row"><?php _e("Langue: ",'textmaster' ); ?></th>
        <td>
        <?
	if ($languages['message'] != '') {
		_e('Merci de v&eacute;rifier les api_key et api_secret de TextMaster','textmaster');
	}
	else
	{
		?>
		<select name="textmaster_readproofLanguage" style="width:235px;">
		<?php
		foreach($languages as $key => $language)
		{
			if ($textmaster_readproofLanguage == $key)
				echo '<option value="'.$key.'" selected="selected">'.$language.'</option>';
			else
				echo '<option value="'.$key.'">'.$language.'</option>';
		}
		?>
		</select>
		<?
	}
	?>
		</td>
     	</tr>
 		<tr valign="top">
        <th scope="row"><?php _e("Categorie: ",'textmaster' ); ?></th>
        <td>
	 	<?
	if ($categories['message'] != '') {
		_e('Merci de v&eacute;rifier les api_key et api_secret de TextMaster','textmaster');
	}
	else
	{
		?>
			<select name="textmaster_readproofCategorie" style="width:235px;">
			<?php
		foreach($categories as $key => $categorie)
		{
			if ($textmaster_readproofCategorie == $key)
				echo '<option value="'.$key.'" selected="selected">'.$categorie.'</option>';
			else
				echo '<option value="'.$key.'">'.$categorie.'</option>';
		}
		?>
			</select>
			<?
	}
	?>
        </td>
        </tr>
     	<tr valign="top">
        <td colspan="2">
        <?php _e('Message de briefing: ','textmaster') ?>
        <textarea class="large-text" name="textmaster_readproofBriefing" rows="3"><?php echo $textmaster_readproofBriefing; ?></textarea>
		</td>
        </tr>
		</table>
		<br/>
		</div>

    	<!-- l'onglet traduction -->
		<div class="t4">
        <table class="form-table">
     	<tr valign="top">
        <td colspan="2">
        <h3><?php _e('Paramètres de traduction par défaut ','textmaster') ?></h3>
		</td>
        </tr>
		 <th scope="row"><?php _e("Niveau de service: ",'textmaster' ); ?></th>
        <td>
		<select name="textmaster_traductionLanguageLevel" style="width:235px;">
		<?php
	foreach($languageLevels as $key => $languageLevel)
	{
		if ($textmaster_traductionLanguageLevel == $key)
			echo '<option value="'.$key.'" selected="selected">'.$languageLevel.'</option>';
		else
			echo '<option value="'.$key.'">'.$languageLevel.'</option>';
	}
	?>
		</select>
		</td>
        </tr>
       	<tr valign="top">
        <th scope="row"><?php _e("Langue source: ",'textmaster' ); ?></th>
        <td>
        <?
	if ($languages['message'] != '') {
		_e('Merci de v&eacute;rifier les api_key et api_secret de TextMaster','textmaster');
	}
	else
	{
		?>
			<select name="textmaster_traductionLanguageSource" style="width:235px;">
			<?php
		foreach($languages as $key => $language)
		{
			if ($textmaster_traductionLanguageSource == $key)
				echo '<option value="'.$key.'" selected="selected">'.$language.'</option>';
			else
				echo '<option value="'.$key.'">'.$language.'</option>';
		}
		?>
			</select>
			<?
	}
	?>
		</td>
     	</tr>
   		<tr valign="top">
        <th scope="row"><?php _e("Langue destination: ",'textmaster' ); ?></th>
        <td>
        <?
	if ($languages['message'] != '') {
		_e('Merci de v&eacute;rifier les api_key et api_secret de TextMaster','textmaster');
	}
	else
	{
		?>
			<select name="textmaster_traductionLanguageDestination" style="width:235px;">
			<?php
		foreach($languages as $key => $language)
		{
			if ($textmaster_traductionLanguageDestination == $key)
				echo '<option value="'.$key.'" selected="selected">'.$language.'</option>';
			else
				echo '<option value="'.$key.'">'.$language.'</option>';
		}
		?>
			</select>
			<?
	}
	?>
		</td>
     	</tr>
		<tr valign="top">
        <th scope="row"><?php _e("Categorie: ",'textmaster' ); ?></th>
        <td>
	 	<?
	if ($categories['message'] != '') {
		_e('Merci de v&eacute;rifier les api_key et api_secret de TextMaster','textmaster');
	}
	else
	{
		?>
				<select name="textmaster_traductionCategorie" style="width:235px;">
				<?php
		foreach($categories as $key => $categorie)
		{
			if ($textmaster_traductionCategorie == $key)
				echo '<option value="'.$key.'" selected="selected">'.$categorie.'</option>';
			else
				echo '<option value="'.$key.'">'.$categorie.'</option>';
		}
		?>
				</select>
				<?
	}
	?>
        </td>
        </tr>
        <tr valign="top">
        <td colspan="2">
        <?php _e('Message de briefing: ','textmaster') ?>
        <textarea class="large-text" name="textmaster_traductionBriefing" rows="3"><?php echo $textmaster_traductionBriefing; ?></textarea>
		</td>
        </tr>
        </table>
        <br/>
		</div>
		<div style="clear:both;"></div>
		<br/>
		<br/>
        <p class="submit">
        <input type="submit" name="Submit" class="button-primary" value="<?php _e('Sauvegarder les paramtètres','textmaster' ) ?>" />
        </p>
    </form>
    </div>
	<?
}

?>
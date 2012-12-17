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
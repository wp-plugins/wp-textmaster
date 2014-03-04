	jQuery(document).ready(function($) {


		// pour la metabox Readproof
		jQuery('#showOptionsReadproof').click(function(){
			jQuery('#optionsReadproof').toggle();
		});
		jQuery('#optionsReadproof').hide();
		jQuery('#showAuthorsReadproof').click(function(){
			jQuery('#authorsReadproof').toggle();
		});
		jQuery('#authorsReadproof').hide();

		// pour la metabox Traduction
		jQuery('#showOptionsTraduction').click(function(){
			jQuery('#optionsTraduction').toggle();
		});
		jQuery('#optionsTraduction').hide();
		jQuery('#showAuthorsTraduction').click(function(){
			jQuery('#authorsTraduction').toggle();
		});
		jQuery('#authorsTraduction').hide();

		// pour la metabox redaction
		jQuery('#showOptionsRedaction').click(function(){
			jQuery('#optionsRedaction').toggle();
		});
		jQuery('#optionsRedaction').hide();
		jQuery('#showAuthorsRedaction').click(function(){
			jQuery('#authorsRedaction').toggle();
		});
		jQuery('#authorsRedaction').hide();


		$('#textmaster_settings div').hide();
		$('div.t1').show();
		$('#textmaster_settings ul.tabs li.t1 a').addClass('tab-current');

		$('#textmaster_settings ul li a').click(function(){
			var thisClass = this.className.slice(0,2);
			$('#textmaster_settings div').hide();
			$('div.' + thisClass).show();
			$('#textmaster_settings ul.tabs li a').removeClass('tab-current');
			$(this).addClass('tab-current');
		});

		if ($('#post_type').val() == 'textmaster_redaction') {
			$('#ed_toolbar').hide();
		}
		if ($('.post_type_page').val() == 'textmaster_redaction') {
			$('.subsubsub .publish').hide();
		}


/*		jQuery(".check_textmasterAuthor").click(function (){
			autoChkAuteurs(this);
		});
*/

		jQuery("#redaction").click(function(){
			//jQuery("#resultTextmaster").html('Merci de patienter');
			jQuery(".ajax-loading-tmRedaction").show();

			categorie = jQuery("#select_textmasterCat option:selected").val();
			languageLevel = jQuery("#select_textmasterLanguageLevel option:selected").val();
			language = jQuery("#select_textmasterLang option:selected").val();
			wordCountRule = jQuery("#select_textmasteWordCountRule option:selected").val();
			wordCount = jQuery("#text_textmasterWordCount").val();
			keywords = jQuery("#text_textmasterKeywords").val();
			var authors = new Array();
			jQuery("#authorsRedaction .check_textmasterAuthor:checked").each(function() {
 				 authors.push(jQuery(this).val());
			});

			quality = jQuery("#radio_textmasterQuality:checked").val();
			expertise = jQuery("#radio_textmasterExpertise:checked").val();
			priority = jQuery("#radio_textmasterPriority:checked").val();

			keywordsRepeatCount = jQuery("#text_textmasterKeywordsRepeatCount").val();
			vocabularyType = jQuery("#select_textmasterVocabularyType option:selected").val();
			grammaticalPerson = jQuery("#select_textmasterGrammaticalPerson option:selected").val();
			targetReaderGroup = jQuery("#select_textmasterTargetReaderGroup option:selected").val();

			templateTM = jQuery("#radio_textmasterTemplate:checked").val()

			postID = jQuery("#post_ID").val();

			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'textmaster',
					categorie: categorie,
					languageLevel: languageLevel,
					language: language,
					wordCountRule: wordCountRule,
					wordCount: wordCount,

					quality: quality,
					expertise: expertise,
					priority: priority,

					keywords: keywords,
					keywordsRepeatCount: keywordsRepeatCount,
					vocabularyType: vocabularyType,
					grammaticalPerson: grammaticalPerson,
					targetReaderGroup: targetReaderGroup,
					authors: authors,

					templateTM: templateTM,

					postID: postID
				},
				success: function(data, textStatus, XMLHttpRequest){
					jQuery("#resultTextmaster").html('');
					jQuery("#resultTextmaster").append(data);
					jQuery(".ajax-loading-tmRedaction").hide();
				},
				error: function(MLHttpRequest, textStatus, errorThrown){
					jQuery(".ajax-loading-tmRedaction").hide();
					alert(textStatus +" " +errorThrown);
				}
			});
		});


		jQuery("#readproof").click(function(){
		//	jQuery("#resultTextmaster").html('Merci de patienter');
			jQuery(".ajax-loading-tmReadProof").show();

			categorie = jQuery("#select_textmasterCat option:selected").val();
			languageLevel = jQuery("#select_textmasterReadProofLanguageLevel option:selected").val();
			language = jQuery("#select_textmasterReadProofLang option:selected").val();
			var authors = new Array();
			jQuery("#authorsReadproof .check_textmasterAuthor:checked").each(function() {
 				 authors.push(jQuery(this).val());
			});

			quality = jQuery("#radio_textmasterQualityReadproof:checked").val();
			expertise = jQuery("#radio_textmasterExpertiseReadproof:checked").val();
			priority = jQuery("#radio_textmasterPriorityReadproof:checked").val();

			keywords = jQuery("#text_textmasterKeywords_readproof").val();
			keywordsRepeatCount = jQuery("#text_textmasterKeywordsRepeatCount_readproof").val();
			vocabularyType = jQuery("#select_textmasterVocabularyType_readproof option:selected").val();
			grammaticalPerson = jQuery("#select_textmasterGrammaticalPerson_readproof option:selected").val();
			targetReaderGroup = jQuery("#select_textmasterTargetReaderGroup_readproof option:selected").val();

			briefing = jQuery("#text_textmasterBriefing_readproof").val();

			postID = jQuery("#post_ID").val();

			jQuery.ajax({
		 		type: 'POST',
	 			url: ajaxurl,
	  			data: {
	  				action: 'textmaster',
					typeTxtMstr: 'proofread',
	  				categorie: categorie,
	  				languageLevel: languageLevel,
	  				language: language,
	  				authors: authors,

	  				quality: quality,
					expertise: expertise,
					priority: priority,

					keywords: keywords,
					keywordsRepeatCount: keywordsRepeatCount,
					vocabularyType: vocabularyType,
					grammaticalPerson: grammaticalPerson,
					targetReaderGroup: targetReaderGroup,

					briefing: briefing,

					postID: postID
		  		},
	  			success: function(data, textStatus, XMLHttpRequest){
	  			jQuery("#resultTextmaster").html('');
	  			jQuery("#resultTextmaster").append(data);
	  			jQuery(".ajax-loading-tmReadProof").hide();
	  		},
	  		error: function(MLHttpRequest, textStatus, errorThrown){
	  			jQuery(".ajax-loading-tmReadProof").hide();
				alert(errorThrown);
	  		}
	 	 });
	  	});

		jQuery("#traduction").click(function(){
		//	jQuery("#resultTextmasterTrad").html('Merci de patienter');
			jQuery(".ajax-loading-tmTrad").show();

			categorie = jQuery("#select_textmasterCatTrad option:selected").val();
			languageLevel = jQuery("#select_textmasterTradLanguageLevel option:selected").val();
			langOrigine = jQuery("#select_textmasterLangOrigine option:selected").val();
			langDestination = jQuery("#select_textmasterLangDestination option:selected").val();
			var authors = new Array();
			jQuery("#authorsTraduction .check_textmasterAuthor:checked").each(function() {
 				 authors.push(jQuery(this).val());
			});

			quality = jQuery("#radio_textmasterQualityTraduction:checked").val();
			expertise = jQuery("#radio_textmasterExpertiseTraduction:checked").val();
			priority = jQuery("#radio_textmasterPriorityTraduction:checked").val();

			keywords = jQuery("#text_textmasterKeywords_traduction").val();
			keywordsRepeatCount = jQuery("#text_textmasterKeywordsRepeatCount_traduction").val();
			vocabularyType = jQuery("#select_textmasterVocabularyType_traduction option:selected").val();
			grammaticalPerson = jQuery("#select_textmasterGrammaticalPerson_traduction option:selected").val();
			targetReaderGroup = jQuery("#select_textmasterTargetReaderGroup_traduction option:selected").val();

			briefing = jQuery("#text_textmasterBriefing_traduction").val();

			postID = jQuery("#post_ID").val();
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'textmaster',
					typeTxtMstr: 'traduction',
					categorie: categorie,
					languageLevel: languageLevel,
					langOrigine: langOrigine,
					langDestination: langDestination,
	  				authors: authors,

					quality: quality,
					expertise: expertise,
					priority: priority,

					keywords: keywords,
					keywordsRepeatCount: keywordsRepeatCount,
					vocabularyType: vocabularyType,
					grammaticalPerson: grammaticalPerson,
					targetReaderGroup: targetReaderGroup,

					briefing: briefing,

					postID: postID
				},
				success: function(data, textStatus, XMLHttpRequest){
					jQuery("#resultTextmasterTrad").html('');
					jQuery("#resultTextmasterTrad").append(data);
					jQuery(".ajax-loading-tmTrad").hide();
				},
				error: function(MLHttpRequest, textStatus, errorThrown){
					jQuery(".ajax-loading-tmTrad").hide();
					alert(errorThrown);
				}
			});
		});

		// validation du document
		jQuery("#useDocTextmaster").click(function(){

			jQuery("#ajax-loading-validate").show();
			jQuery("#waitMsgTm").show();

			docId = jQuery("#docId").val();
			projectId = jQuery("#projectId").val();
			textmaster_type = jQuery("#textmaster_type").val();

			titre = jQuery("#titreTm").html();
			//jQuery("#titreTm")
			work = jQuery("#textmasterWork").clone();
			work.find('#titreTm').remove();
			text = work.html();
			jQuery.ajax({
			 		type: 'GET',
		 			url: window.urlPlugin + '/approuve_doc.php?valide=1&docId='+docId+'&projectId='+projectId+'&type='+textmaster_type,

		  			success: function(data, textStatus, XMLHttpRequest){
		  				if (textmaster_type == 'redaction') {
		  					if (data.indexOf('Error') != -1) {
		  						jQuery("#resultTM").html('<div class="error">'+data+'</div>');
		  					}
		  					else
		  						window.top.location.href = window.urlAdmin +"/post.php?post="+data+"&action=edit";
		  				//	jQuery("#content").val("");
		  				//	tinyMCE.activeEditor.setContent(text);
		  				}
		  				else {
		  					if (data.indexOf('Error') != -1) {
		  						jQuery("#resultTM").html('<div class="error">'+data+'</div>');
		  					}
		  					else{
			  					jQuery("#content", window.top.document).val("");
			  					jQuery("#title", window.top.document).val("");
			  					//	tinyMCE.execCommand('mceReplaceContent', false, text);
			  					//tinymce.get("content_parent").focus();
			  					try {
			  					//	jQuery("#content_parent").tinymce().focus();
			  						window.parent.tinyMCE.activeEditor.setContent(text);
			  						jQuery("#title", window.top.document).val(titre);
			  					}
			  					catch (e) {
			  						jQuery("#content", window.top.document).val(text);
			  						jQuery("#title", window.top.document).val(titre);
			  					}
			  					window.parent.tb_remove();
			  				}
		  				}
		  			},
		  			error: function(MLHttpRequest, textStatus, errorThrown){
		  				alert(errorThrown);
		  			}
		 	 });
	  	});

		jQuery("#useDocTextmasterPlus").click(function(){

			jQuery("#ajax-loading-validate").show();
			jQuery("#waitMsgTm").show();

			docId = jQuery("#docId").val();
			projectId = jQuery("#projectId").val();
			textmaster_type = jQuery("#textmaster_type").val();
			text = jQuery("#textmasterWork").html();
			jQuery.ajax({
			 		type: 'GET',
		 			url: window.urlPlugin +'/approuve_doc.php?valide=1&docId='+docId+'&projectId='+projectId+'&type='+textmaster_type+'&new_article=1',

		  			success: function(data, textStatus, XMLHttpRequest){
		  				if (data.indexOf('Error') != -1) {
		  					jQuery("#resultTM").html('<div class="error">'+data+'</div>');
		  				}
		  				else
		  					window.top.location.href= window.urlAdmin +"/post.php?post="+data+"&action=edit";
		  			},
		  			error: function(MLHttpRequest, textStatus, errorThrown){
		  				alert(errorThrown);
		  			}
		 	 });
	  	});

		jQuery("#useDocTextmasterPlusPage").click(function(){

			jQuery("#ajax-loading-validate").show();
			jQuery("#waitMsgTm").show();

			docId = jQuery("#docId").val();
			projectId = jQuery("#projectId").val();
			textmaster_type = jQuery("#textmaster_type").val();
			text = jQuery("#textmasterWork").html();
			jQuery.ajax({
			 		type: 'GET',
		 			url: window.urlPlugin +'/approuve_doc.php?valide=1&docId='+docId+'&projectId='+projectId+'&type='+textmaster_type+'&new_article=2',

		  			success: function(data, textStatus, XMLHttpRequest){
		  				if (data.indexOf('Error') != -1) {
		  					jQuery("#resultTM").html('<div class="error">'+data+'</div>');
		  				}
		  				else
		  					window.top.location.href= window.urlAdmin +"/post.php?post="+data+"&action=edit";
		  			},
		  			error: function(MLHttpRequest, textStatus, errorThrown){
		  				alert(errorThrown);
		  			}
		 	 });
	  	});

	  	jQuery( "#content" ).change(function() {
		  	getPrice('readproof');
		  	getPrice('traduction');
	  	});
	  	jQuery('#content').keyup(function(){
	  	  	getPrice('readproof');
		  	getPrice('traduction');
	  	});

	  	jQuery( "#select_textmasterReadProofLanguageLevel" ).change(function() {
		  	getPrice('readproof');
		  	getAuthors('readproof');
		  	setOptions('select_textmasterReadProofLanguageLevel');
	  	});

	  	jQuery( "#select_textmasterTradLanguageLevel" ).change(function() {
		  	getPrice('traduction');
		  	getAuthors('traduction');
		  	setOptions();
	  	});

	  	jQuery( "#select_textmasterLanguageLevel" ).change(function() {
		  	getPrice('redaction');
		  	getAuthors('redaction');
		  	setOptions();
	  	});

	  	jQuery( "#text_textmasterWordCount" ).change(function() {
		  	getPrice('redaction');
		  	getAuthors('redaction');
	  	});

		jQuery( ".radio_textmasterQuality" ).change(function() {
		  	getPrice('redaction');
		  	getAuthors('redaction');
	  	});
		jQuery( ".radio_textmasterQualityReadproof" ).change(function() {
		  	getPrice('readproof');
		  	getAuthors('readproof');
	  	});
	  	jQuery( ".radio_textmasterQualityTraduction" ).change(function() {
		  	getPrice('traduction');
		  	getAuthors('traduction');
	  	});
		jQuery( ".radio_textmasterExpertise" ).change(function() {
		  	getPrice('redaction');
		  	getAuthors('redaction');
	  	});
		jQuery( ".radio_textmasterExpertiseReadproof" ).change(function() {
		  	getPrice('readproof');
		  	getAuthors('readproof');
	  	});
		jQuery( ".radio_textmasterExpertiseTraduction" ).change(function() {
		  	getPrice('traduction');
		  	getAuthors('traduction');
	  	});
	  	jQuery( ".radio_textmasterPriority" ).change(function() {
		  	getPrice('redaction');
		  	getAuthors('redaction');
	  	});
	  	jQuery( ".radio_textmasterPriorityReadproof" ).change(function() {
		  	getPrice('readproof');
		  	getAuthors('readproof');
	  	});
	  	jQuery( ".radio_textmasterPriorityTraduction" ).change(function() {
		  	getPrice('traduction');
		  	getAuthors('traduction');
	  	});

	  	jQuery( "#select_textmasterLang" ).change(function() {
		  	getAuthors('redaction');
	  	});
	  	jQuery( "#select_textmasterCat" ).change(function() {
	  		if (jQuery( "#post_type").val() == 'textmaster_redaction')
		  		getAuthors('redaction');
		  	else
		  		getAuthors('readproof');
	  	});
	  	jQuery( "#select_textmasterReadProofLang" ).change(function() {
		  	getAuthors('readproof');
	  	});
	  	jQuery( "#select_textmasterLangOrigine" ).change(function() {
		  	getAuthors('traduction');
	  	});
	  	jQuery( "#select_textmasterLangDestination" ).change(function() {
		  	getAuthors('traduction');
	  	});
	  	jQuery( "#select_textmasterCatTrad" ).change(function() {
		  	getAuthors('traduction');
	  	});
	  	 setOptions();
	});

/**
 *
 * @access public
 * @return void
 **/
function getPrice(type, wordsCount){
	var objAffichage = '';
	var objAffichageQuality = '';
	var objAffichageExpertise = '';
	var objAffichagePriority = '';
	var objAffichageBase = '';

	var wordsCount = typeof wordsCount !== 'undefined' ? wordsCount : '';

	var languageLevel = '';
	var quality = '';
	var priority = '';




	if (type == 'redaction') {
		if (jQuery('#forceWordsCount').val() != undefined)
			wordsCount = jQuery('#forceWordsCount').val();
		else
			wordsCount = jQuery('#text_textmasterWordCount').val();

		if (wordsCount == '')
			wordsCount = 0;


		languageLevel = jQuery("#select_textmasterLanguageLevel option:selected").val();
		quality = jQuery('.radio_textmasterQuality:checked').val();
		expertise = jQuery('.radio_textmasterExpertise:checked').val();
		priority = jQuery('.radio_textmasterPriority:checked').val();

		objAffichage = 'priceTextmaster';
		objAffichageQuality = 'priceTextmasterQuality';
		objAffichageExpertise = 'priceTextmasterExpertise';
		objAffichagePriority = 'priceTextmasterPriority';

		objAffichageBase = 'priceTextmasterBase';

		sendPriceRequest(type, wordsCount, languageLevel, quality, expertise, priority, objAffichageQuality, objAffichageExpertise, objAffichagePriority, objAffichage, objAffichageBase);

	}
	else {

		if (type == 'readproof')
		{
			languageLevel = jQuery("#select_textmasterReadProofLanguageLevel option:selected").val();
			quality = jQuery('.radio_textmasterQualityReadproof:checked').val();
			expertise = jQuery('.radio_textmasterExpertiseReadproof:checked').val();
			priority = jQuery('.radio_textmasterPriorityReadproof:checked').val();

			objAffichage = 'priceTextmasterReadProof';
			objAffichageQuality = 'priceTextmasterQualityReadProof';
			objAffichageExpertise = 'priceTextmasterExpertiseReadproof';
			objAffichagePriority = 'priceTextmasterPriorityReadproof';

			objAffichageBase = 'priceTextmasterBaseReadproof';
		}
		else if (type == 'traduction')
		{
			languageLevel = jQuery("#select_textmasterTradLanguageLevel option:selected").val();
			quality = jQuery('.radio_textmasterQualityTraduction:checked').val();
			expertise = jQuery('.radio_textmasterExpertiseTraduction:checked').val();
			priority = jQuery('.radio_textmasterPriorityTraduction:checked').val();

			objAffichage = 'priceTextmasterTrad';
			objAffichageQuality = 'priceTextmasterQualityTrad';
			objAffichageExpertise = 'priceTextmasterExpertiseTrad';
			objAffichagePriority = 'priceTextmasterPriorityTrad';

			objAffichageBase = 'priceTextmasterBaseTrad';
		}

		if (wordsCount == '')
		{
			if (jQuery('#forceWordsCount').val() != undefined){
				wordsCount = jQuery('#forceWordsCount').val();
				sendPriceRequest(type, wordsCount, languageLevel, quality, expertise, priority, objAffichageQuality, objAffichageExpertise, objAffichagePriority, objAffichage, objAffichageBase);
			}else{
				word_count(jQuery('#title').val() + ' ' + jQuery('#content').val(), function(wordsCount){
					sendPriceRequest(type, wordsCount, languageLevel, quality, expertise, priority, objAffichageQuality, objAffichageExpertise, objAffichagePriority, objAffichage, objAffichageBase);
				});
			}
		}
	}
}


/**
 *
 * @access public
 * @return void
 **/
function getAuthors(type, wordsCount){

	var wordsCount = typeof wordsCount !== 'undefined' ? wordsCount : '';

	var language_from = '';
	var language_to = '';
	var category = '';
	var languageLevel = '';
	var quality = '';
	var priority = '';

	var objAffichageAuteurs = '';
	var nameChkAuteurs = '';

	var postID = jQuery("#post_ID").val();

	if (type == 'redaction') {
		if (jQuery('#forceWordsCount').val() != undefined)
			wordsCount = jQuery('#forceWordsCount').val();
		else
			wordsCount = jQuery('#text_textmasterWordCount').val();
		if (wordsCount == '')
			wordsCount = 0;

		language_from = jQuery("#select_textmasterLang option:selected").val();
		language_to = jQuery("#select_textmasterLang option:selected").val();
		category = jQuery("#select_textmasterCat option:selected").val();
		languageLevel = jQuery("#select_textmasterLanguageLevel option:selected").val();
		quality = jQuery('.radio_textmasterQuality:checked').val();
		expertise = jQuery('.radio_textmasterExpertise:checked').val();
		priority = jQuery('.radio_textmasterPriority:checked').val();

		objAffichageAuteurs = 'authorsRedaction';
		nameChkAuteurs = 'check_textmasterAuthor';

		sendAuthorRequest(postID, type, language_from, language_to, category, wordsCount, languageLevel, quality, expertise, priority, objAffichageAuteurs, nameChkAuteurs);

	}
	else {


		if (type == 'readproof')
		{
			language_from = jQuery("#select_textmasterReadProofLang option:selected").val();
			language_to = jQuery("#select_textmasterReadProofLang option:selected").val();
			category = jQuery("#select_textmasterCat option:selected").val();
			languageLevel = jQuery("#select_textmasterReadProofLanguageLevel option:selected").val();
			quality = jQuery('.radio_textmasterQualityReadproof:checked').val();
			expertise = jQuery('.radio_textmasterExpertiseReadproof:checked').val();
			priority = jQuery('.radio_textmasterPriorityReadproof:checked').val();

			objAffichageAuteurs = 'authorsReadproof';
			nameChkAuteurs = 'check_textmasterAuthorReadproof';
		}
		else if (type == 'traduction')
		{
			language_from = jQuery("#select_textmasterLangOrigine option:selected").val();
			language_to = jQuery("#select_textmasterLangDestination option:selected").val();
			category = jQuery("#select_textmasterCatTrad option:selected").val();
			languageLevel = jQuery("#select_textmasterTradLanguageLevel option:selected").val();
			quality = jQuery('.radio_textmasterQualityTraduction:checked').val();
			expertise = jQuery('.radio_textmasterExpertiseTraduction:checked').val();
			priority = jQuery('.radio_textmasterPriorityTraduction:checked').val();

			objAffichageAuteurs = 'authorsTraduction';
			nameChkAuteurs = 'check_textmasterAuthorTraduction';
		}

		if (wordsCount != '')
		{
			if (jQuery('#forceWordsCount').val() != undefined){
				wordsCount = jQuery('#forceWordsCount').val();
				sendAuthorRequest(postID, type, language_from, language_to, category, wordsCount, languageLevel, quality, expertise, priority, objAffichageAuteurs, nameChkAuteurs);
			}else{
				word_count(jQuery('#title').val() + ' ' + jQuery('#content').val(), function(output){
					wordsCount = output;
					sendAuthorRequest(postID, type, language_from, language_to, category, wordsCount, languageLevel, quality, expertise, priority, objAffichageAuteurs, nameChkAuteurs);
				});
			}
		}

	}

}
/**
 *
 * @access public
 * @return void
 **/
function sendPriceRequest(type, wordsCount, languageLevel, quality, expertise, priority, objAffichageQuality, objAffichageExpertise, objAffichagePriority, objAffichage, objAffichageBase){

	jQuery('#'+objAffichageQuality).html('Processing...');
	jQuery('#'+objAffichageExpertise).html('Processing...');
	jQuery('#'+objAffichagePriority).html('Processing...');
	jQuery('#'+objAffichage).html('Processing...');
	jQuery('#'+objAffichageBase).html('Processing...');

	jQuery('.nbMots').html(wordsCount);

	jQuery.ajax({
		type: "POST",
		dataType: "json",
		url: window.urlPlugin +"/ajax_getPrice.php",
		data: { type: type, wordsCount: wordsCount, languageLevel: languageLevel, quality: quality, expertise: expertise, priority: priority}
	}).done(function(data ) {
		jQuery('#'+objAffichageQuality).html(data.quality);
		jQuery('#'+objAffichageExpertise).html(data.expertise);
	//	alert(objAffichageExpertise + ' / ' +data.expertise);
		jQuery('#'+objAffichagePriority).html(data.priority);
		jQuery('#'+objAffichage).html(data.price);
		jQuery('#'+objAffichageBase).html(data.priceBase);

		if (jQuery('.walletTextmaster:first').html()  < data.price) {
	//		alert(jQuery('.walletTextmaster:first').html() + ' < ' +data.price);
			if (jQuery("input#bulk_readproof").length > 0)
				jQuery('input#bulk_readproof').attr('disabled', 'disabled');
			if (jQuery("input#traduction").length > 0)
				jQuery('input#traduction').attr('disabled', 'disabled');
			if (jQuery("input#readproof").length > 0)
				jQuery('input#readproof').attr('disabled', 'disabled');
		}
		else if (jQuery("#post_ID").val() != '') {
	//		alert(jQuery('.walletTextmaster:first').html() + ' > ' +data.price);
			if (jQuery("input#bulk_readproof").length > 0)
				jQuery('input#bulk_readproof').removeAttr("disabled");
			if (jQuery("input#traduction").length > 0)
				jQuery('input#traduction').removeAttr("disabled");
			if (jQuery("input#readproof").length > 0)
				jQuery('input#readproof').removeAttr("disabled");
		}


	});
}

function sendAuthorRequest(postID, type, language_from, language_to, category, wordsCount, languageLevel, quality, expertise, priority, objAffichageAuteurs, nameChkAuteurs){
	jQuery("#"+objAffichageAuteurs+" ul").html('<li>Processing...</li>');

	jQuery.ajax({
		type: "POST",
		dataType: "json",
		url: window.urlPlugin +"/ajax_getAuthors.php",
		data: { postID: postID, type: type, language_from: language_from, language_to: language_to, category:category, wordsCount: wordsCount, languageLevel: languageLevel, quality: quality, expertise: expertise, priority: priority}
	}).done(function(data) {
		 jQuery("#"+objAffichageAuteurs+" ul").html('');
		 jQuery.each(data, function(i, item) {
		 	 var desc = '';
			 if (typeof(item.description) != 'undefined')
			 	desc = ' - ' +item.description

			 // l'auteur est déjà séléctionné pour le projet ou dans les paramétrages
			 if (item.checked == 'true')
			 	checked = 'checked="checked"';
			 else
			 	checked = '';

			//auteur ou un mesage d'info
			if (item.noCheckBox == 'false')
		 		html = '<li><input type="checkbox" name="'+nameChkAuteurs+'[]" class="check_textmasterAuthor" value="'+item.author_id+'" '+checked+'> '+item.author_ref+ desc+'</li>';
		 	else
		 		html = '<li>'+item.author_ref+'</li>';

			jQuery("#"+objAffichageAuteurs+" ul").append(html);
			jQuery("#"+objAffichageAuteurs+" .check_textmasterAuthor").click(function (){
				autoChkAuteurs(objAffichageAuteurs, this);
			});
		});

	});
}

/**
 *
 * @access public
 * @return void
 **/
function autoChkAuteurs(objAffichageAuteurs, objchkbox){
	var authorSelected = false;

	jQuery("#"+objAffichageAuteurs+" .check_textmasterAuthor:checked").each(function() {
			if (jQuery(objchkbox).val() != '' && jQuery(objchkbox).val() != 'undefined') {
				authorSelected = true;
			 }
	});

	if (authorSelected)
	 	jQuery("#"+objAffichageAuteurs+" .check_textmasterAuthor:checkbox:first").removeAttr('checked');
	else
		jQuery("#"+objAffichageAuteurs+" .check_textmasterAuthor:checkbox:first").attr('checked','checked');
}
/**
 *
 * @access public
 * @return void
 **/
function setOptions(){
	if (jQuery( "#select_textmasterReadProofLanguageLevel" ).val() != undefined)
	{
		languageLevel = jQuery( "#select_textmasterReadProofLanguageLevel" ).val();
		if (languageLevel == 'regular')
		{
			jQuery('.radio_textmasterQualityReadproof').attr('disabled','disabled');
			jQuery('.radio_textmasterQualityReadproof').filter('[value=false]').prop('checked', true);
			jQuery('.radio_textmasterQualityReadproof').trigger('change');
		}
		else
		{
			jQuery('.radio_textmasterQualityReadproof').removeAttr('disabled','disabled');
			jQuery('.radio_textmasterQualityReadproof').filter('[value=true]').prop('checked', true);
			jQuery('.radio_textmasterQualityReadproof').trigger('change');
		}
	}

	if (jQuery( "#select_textmasterTradLanguageLevel" ).val() != undefined)
	{
		languageLevel = jQuery( "#select_textmasterTradLanguageLevel" ).val();
		if (languageLevel == 'regular')
		{
			jQuery('.radio_textmasterQualityTraduction').attr('disabled','disabled');
			jQuery('.radio_textmasterQualityTraduction').filter('[value=false]').prop('checked', true);
			jQuery('.radio_textmasterQualityTraduction').trigger('change');
		}
		else
		{
			jQuery('.radio_textmasterQualityTraduction').removeAttr('disabled','disabled');
			jQuery('.radio_textmasterQualityTraduction').filter('[value=true]').prop('checked', true);
			jQuery('.radio_textmasterQualityTraduction').trigger('change');
		}
	}

	if (jQuery( "#select_textmasterLanguageLevel" ).val() != undefined)
	{
		languageLevel = jQuery( "#select_textmasterLanguageLevel" ).val();
		if (languageLevel == 'regular')
		{
			jQuery('.radio_textmasterQuality').attr('disabled','disabled');
			jQuery('.radio_textmasterQuality').filter('[value=false]').prop('checked', true);
			jQuery('.radio_textmasterQuality').trigger('change');
		}
		else
		{
			jQuery('.radio_textmasterQuality').removeAttr('disabled','disabled');
			jQuery('.radio_textmasterQuality').filter('[value=true]').prop('checked', true);
			jQuery('.radio_textmasterQuality').trigger('change');
		}
	}




}

function word_count (dataTxt, handleData) {
    var number = 0;
  //  var wordCounts = 0;


    jQuery.post( window.urlPlugin +"/ajax_countWords.php", { contenu: dataTxt }, function( data ) {
  	//	$( ".result" ).html( data );
  	//	alert('data='+data);
  	//	window.wordCounts=data;
  	//	alert('window.wordCounts=' +window.wordCounts);
  		handleData(data);
	});
  /*  var strip =/<[a-zA-Z\/][^<>]*>/g; // strip HTML tags
    var stripShortCode = /\[[a-zA-Z\/][^\[\]]*\]>/g; // strip shortCode tags
 //   var stripShortCode = /\[.+\]/g; // strip shortCode tags
	var clean =/[.,;:!?%#$¿()'"]+/g; // regexp to remove punctuation, etc.
    var clean =/[.(),;:!?%#$¿'"_+=\\/-]+/g; // regexp to remove punctuation, etc.


    var matches = /\S\s+/g;
    var tc = 0;

    data = data.replace(strip, '' ).replace( /&nbsp;|&#160;/gi, ' ' );
    alert(data);
    data = data.replace(stripShortCode, '');
	alert(data);
	data = data.replace(clean, '' );
	data = data.replace('!\s+!',' ');


	alert(data);
    data.replace( matches, function(){tc++;} );
*/
//	alert(window.wordCounts);
  //  return window.wordCounts;
}
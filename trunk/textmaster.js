	jQuery(document).ready(function($) {

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


		jQuery("#redaction").click(function(){
			jQuery("#resultTextmaster").html('<img src="images/wpspin_light.gif" class="ajax-loading" id="draft-ajax-loading" alt=""> Merci de patienter');

			categorie = jQuery("#select_textmasterCat option:selected").val();
			languageLevel = jQuery("#select_textmasterLanguageLevel option:selected").val();
			language = jQuery("#select_textmasterLang option:selected").val();
			wordCountRule = jQuery("#select_textmasteWordCountRule option:selected").val();
			wordCount = jQuery("#text_textmasterWordCount").val();
		//	author = jQuery("#select_textmasterAuthor").val();
			var authors = new Array();
			$(".check_textmasterAuthor:checked").each(function() {
 				 authors.push($(this).val());
			});

			keywords = jQuery("#text_textmasterKeywords").val();
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
				},
				error: function(MLHttpRequest, textStatus, errorThrown){
					alert(textStatus +" " +errorThrown);
				}
			});
		});


		jQuery("#readproof").click(function(){
			jQuery("#resultTextmaster").html('<img src="images/wpspin_light.gif" class="ajax-loading" id="draft-ajax-loading" alt=""> Merci de patienter');

			categorie = jQuery("#select_textmasterCat option:selected").val();
			languageLevel = jQuery("#select_textmasterReadProofLanguageLevel option:selected").val();
			language = jQuery("#select_textmasterReadProofLang option:selected").val();
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
	  			postID: postID
	  		},
	  			success: function(data, textStatus, XMLHttpRequest){
	  			jQuery("#resultTextmaster").html('');
	  			jQuery("#resultTextmaster").append(data);
	  		},
	  		error: function(MLHttpRequest, textStatus, errorThrown){
	  		alert(errorThrown);
	  		}
	 	 });
	  	});

		jQuery("#traduction").click(function(){
			jQuery("#resultTextmasterTrad").html('<img src="images/wpspin_light.gif" class="ajax-loading" id="draft-ajax-loading" alt=""> Merci de patienter');

			categorie = jQuery("#select_textmasterCatTrad option:selected").val();
			languageLevel = jQuery("#select_textmasterTradLanguageLevel option:selected").val();
			langOrigine = jQuery("#select_textmasterLangOrigine option:selected").val();
			langDestination = jQuery("#select_textmasterLangDestination option:selected").val();

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
					postID: postID
				},
				success: function(data, textStatus, XMLHttpRequest){
					jQuery("#resultTextmasterTrad").html('');
					jQuery("#resultTextmasterTrad").append(data);
				},
				error: function(MLHttpRequest, textStatus, errorThrown){
					alert(errorThrown);
				}
			});
		});

		jQuery("#useDocTextmaster").click(function(){
			docId = jQuery("#docId").val();
			projectId = jQuery("#projectId").val();
			textmaster_type = jQuery("#textmaster_type").val();
			text = jQuery("#textmasterWork").html();
			jQuery.ajax({
			 		type: 'GET',
		 			url: '/wp-content/plugins/wp-textmaster/approuve_doc.php?valide=1&docId='+docId+'&projectId='+projectId+'&type='+textmaster_type,

		  			success: function(data, textStatus, XMLHttpRequest){
		  				if (textmaster_type == 'redaction') {
		  					document.location.href="post.php?post="+data+"&action=edit";
		  				//	jQuery("#content").val("");
		  				//	tinyMCE.activeEditor.setContent(text);
		  				}
		  				else {
		  					jQuery("#content").val("");
		  					//	tinyMCE.execCommand('mceReplaceContent', false, text);
		  					tinyMCE.activeEditor.setContent(text);
		  					tb_remove();
		  				}
		  			},
		  			error: function(MLHttpRequest, textStatus, errorThrown){
		  				alert(errorThrown);
		  			}
		 	 });
	  	});
	});
jQuery(document).ready(function(){
    
    
    jQuery("body.post-type-jswprediction_round .wrap .page-title-action").on("click",function(e){
        e.preventDefault();
        jQuery("<div></div>").attr('id','jswLeagueSelect').appendTo('body');  
        var addnew = jQuery(this);
        var data = {
        'action': 'prediction_leaguemodal',
        };

        jQuery.post(ajaxurl, data, function(response) {

           jQuery( "#jswLeagueSelect" ).html(response);

        });
        jQuery( "#jswLeagueSelect" ).dialog({modal: true,height: 300,width:450,
            buttons: {
              Next: function() {
                if(jQuery('#jsw_round_league').val()){
                    jQuery( this ).dialog( "close" );
                    
                    location.href = addnew.attr('href') + '&leagueid='+jQuery('#jsw_round_league').val() + '&roundtype='+jQuery('input[name="jsp_round_type"]:checked').val();
                }   
                
              }
            }


        });
        
    });
    jQuery('#jsprediction_matches_selectall').click(function(){
        jQuery("#round_matches_filter option").each(function(){
            jQuery(this).attr('selected', true);
        });
        jQuery( "#round_matches_filter" ).trigger("chosen:updated");
    });
    jQuery('#JSPRED_participiants_ADD').click(function(){
        var vals = [];
        jQuery('input[name="matches_in_round[]"]').each(function(){
            vals.push(jQuery(this).val());
        });
        

        jQuery("#round_matches_filter option:selected").each(function(){

            if(jQuery.inArray( jQuery(this).val(), vals ) == -1){
                
                //jQuery('#JSACHV_results_TBL tbody').append('<tr><td><a href="javascript:void(0);" onclick="javascript:(this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode));"><i class="fa fa-trash" aria-hidden="true"></i></a><input type="hidden" name="partic_id[]" value="'+jQuery(this).val()+'"></td><td>'+jQuery(this).text()+'</td></tr>');
                var tr = jQuery('<tr />');
                
                var td = '<td><a href="javascript:void(0);" onclick="javascript:(this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode));"><i class="fa fa-trash" aria-hidden="true"></i></a><input type="hidden" name="matches_in_round[]" value="'+jQuery(this).val()+'"></td>';

                var td2 = '<td>'+jQuery(this).text()+'</td>';

                tr.append(td);
                tr.append(td2);
                
                jQuery('#jspred_round_matches tbody').append(tr);
            }
            jQuery(this).attr('selected', false);
        });
        jQuery( "#round_matches_filter" ).trigger("chosen:updated");
    });
    
});   


function JSPRED_filteredMatches(level,leagueid){
    var season_id = jQuery("#jspred_fltr_season_id").val();
    var matchday_id = jQuery("#jspred_fltr_matchday_id").val();
    //console.log(JSON.stringify(fltArr));
    var data = {
        'action': 'jspred_round_filters',
        'season_id': season_id,
        'matchday_id': matchday_id,
        'leagueid': leagueid
    };
    jQuery('#modalAj').show();
    jQuery.post(ajaxurl, data, function(response) {
        
        var obj = JSON.parse(response);
        
        if(level == 0){
            
            jQuery( "#jspred_fltr_matchday_id" ).html(obj.matchdays);
            jQuery( "#jspred_fltr_matchday_id" ).trigger("chosen:updated");
        }

       jQuery( "#round_matches_filter" ).html(obj.matches);
       jQuery( "#round_matches_filter" ).trigger("chosen:updated");
       jQuery('#modalAj').hide();
    });
}

jQuery(document).ready(function(){  
    jQuery('body').on('click','.jsportPopUl input',function(){
        var id = jQuery(this).attr("id");
        jQuery('.jsportPopUl textarea').hide();
        jQuery('#'+id+'_text').show();
    });
    
    jQuery('body').on('click','#jsportPopSkip',function(event){
        event.preventDefault();
        if(jQuery('#jsDeactivateOpt1').is(':checked')){
           var disb = 1; 
        }else{
            disb = 0;
        }
        var data = {
            'action': 'jspred-updoption',
            'option': disb,
        };
        var href = jQuery(this).attr('href');
        jQuery.post(ajaxurl, data, function(response) {
            window.location = href;
        });
    });
    jQuery('body').on('click','#jsportPopSend',function(event){
        event.preventDefault();
        var ch_type = jQuery('input[name="jsDeactivateReason"]:checked').val();
        if(ch_type){
            var ch_text = jQuery('#jsDeactivateReason'+ch_type+'_text').val();
            
             if(jQuery('#jsDeactivateOpt1').is(':checked')){
                var disb = 1; 
             }else{
                 disb = 0;
             }
             var data = {
                 'action': 'jspred-updoption',
                 'option': disb,
             };
             jQuery.post(ajaxurl, data, function(response) {
             });
             
             var href = jQuery(this).attr('href');
             var data = {
                    'action': 'jspred-senddeactivation',
                    'ch_type': ch_type,
                    'ch_text': ch_text,
                };
             jQuery.post(ajaxurl, data, function(response) {
                 window.location = href;

             });
            
            
        }
    });
});
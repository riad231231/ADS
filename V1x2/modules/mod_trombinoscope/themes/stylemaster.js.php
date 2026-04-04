<?php 
/**
* @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

$security = 0;
if (isset($_GET["$security"])) {
	$security = $_GET['security'];
}

define('_JEXEC', $security);

// No direct access to this file
defined('_JEXEC') or die;

// Explicitly declare the type of content
header("Content-type: text/javascript; charset=UTF-8");

// Grab module id from the request
$suffix = $_GET['suffix']; 

// parameters

$card_width = 100; // %
if (isset($_GET['card_w'])) {
	$card_width = (int)$_GET['card_w'];
}

$min_width = 100; // px
if (isset($_GET['min_w'])) {
	$min_width = (int)$_GET['min_w'];
} 

$max_width = -1; // px
if (isset($_GET['max_w'])) {
	$max_width = (int)$_GET['max_w'];
}

$margin_min_width = 3; // px

$margin_error = 1; // px

$jquery_var = 'jQuery';
?>

<?php echo $jquery_var ?>(document).ready(function ($) {

    var person = $('.te_<?php echo $suffix ?> .person');
    var personlist = $('.te_<?php echo $suffix ?> .personlist');
    
	if (person != null) {
        resize_te_cards();
    }

	$(window).resize(function() {
        if (person != null) {
            resize_te_cards();
        }
    });

    function resize_te_cards() {

        var container_width = personlist.width();
        
        var cards_per_row = 1;
	    	
    	var card_width = Math.floor(container_width * <?php echo $card_width ?> / 100);
         
	    if (card_width < <?php echo $min_width ?>) {
	    
	    	if (container_width < <?php echo $min_width ?>) {
	    		card_width = container_width;
	    	} else {
	    		card_width = <?php echo $min_width ?>;
	    	}
	    	
        } else if (<?php echo $max_width ?> > 0 && card_width > <?php echo $max_width ?>) {
        	card_width = <?php echo $max_width ?>;
        }
        
        if (<?php echo $card_width ?> <= 50) {
	        cards_per_row = Math.floor(container_width / card_width);   
	        
	        if (cards_per_row == 1) {
	        	if (<?php echo $max_width ?> < 0) {  
	        		card_width = container_width;
	        	} else {
	        		if (container_width > <?php echo $max_width ?>) {   
	        			card_width = <?php echo $max_width ?>;		        		
	        		} else { 
		        		card_width = container_width;
	        		}
	        	}
	        } else {
	        	card_width = Math.floor(container_width / cards_per_row) - (<?php echo $margin_min_width ?> * cards_per_row);
	        	if (<?php echo $max_width ?> > 0 && card_width > <?php echo $max_width ?>) {
	        		card_width = <?php echo $max_width ?>;
	        	}
	        }    
	    } else { // we can never have 2 cards on the same row
	    	
	    	if (<?php echo $max_width ?> < 0) {  
        		card_width = container_width;
        	} else {
        		if (container_width > <?php echo $max_width ?>) {   
        			card_width = <?php echo $max_width ?>;	        		
        		} else { 
	        		card_width = container_width;
        		}
        	}
	    }
        
        var left_for_margins = container_width - (cards_per_row * card_width);
		var margin_width = Math.floor(left_for_margins / (cards_per_row * 2)) - <?php echo $margin_error ?>;        
        
        person.each(function() {
            $(this).width(card_width + 'px');
            $(this).css('margin-left', margin_width +'px');
	        $(this).css('margin-right', margin_width +'px');
	        
	        if (card_width >= <?php echo $min_width ?>) {
	        
	        	if ($(this).hasClass('pl')) {
        			$(this).addClass('picture_left'); 
        			$(this).removeClass('pl');
        		} else if ($(this).hasClass('pr')) {
        			$(this).addClass('picture_right'); 
        			$(this).removeClass('pr');
        		}
        		
				if ($(this).hasClass('picture_left') || $(this).hasClass('picture_right')) {
					person.removeClass('picture_top');
				}
			} else if (container_width < <?php echo $min_width ?>) {
			
				if ($(this).hasClass('picture_left')) {
        			$(this).addClass('pl'); 
        			$(this).removeClass('picture_left');
        		} else if ($(this).hasClass('picture_right')) {
        			$(this).addClass('pr'); 
        			$(this).removeClass('picture_right');
        		}			
			
	    		if ($(this).hasClass('pl') || $(this).hasClass('pr')) {
	    			person.addClass('picture_top'); 
	    		}
	    	}
	        
        });
	}

});

<?php 
/**
* @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
* @license		GNU General Public License version 3 or later; see LICENSE.txt
*/

// No direct access to this file
defined('_JEXEC') or die;

// Explicitly declare the type of content
header("Content-type: text/css; charset=UTF-8");
?>

.te_<?php echo $suffix; ?> {
	overflow: hidden;
	position: relative;
	width: 100%;
}

.te_<?php echo $suffix; ?> .personlist {
	overflow: hidden;
	text-align: center;
	font-size: 0; /* trick to eliminate whitespace between inline-block elements */
}

	.te_<?php echo $suffix; ?> .groupheader {
		display: inline-block;
	    width: 100%;
	    font-size: <?php echo $font_size; ?>px;
		text-align: left;
	}
	
	.te_<?php echo $suffix; ?> .groupheader .header {
		border-bottom: 1px solid #DDDDDD;
	}
	
	.te_<?php echo $suffix; ?> .groupheader .subheader {
		border-bottom: 1px solid #DDDDDD;
		margin-left: 20px;
	}
	
	.te_<?php echo $suffix; ?> .contactgroup {
		overflow: hidden;
		text-align: center;
		font-size: 0; /* trick to eliminate whitespace between inline-block elements */
	}
	
	.te_<?php echo $suffix; ?> .contactgroup .person {
		white-space: normal;
	}

	.te_<?php echo $suffix; ?> .person {
		overflow: hidden;
		position: relative; 
		margin-top: 5px;
		margin-bottom: 5px;			
		font-size: <?php echo $font_size; ?>px;
		display: inline-block;		
		vertical-align: top;
		text-align: left;
		width: <?php echo $card_width; ?><?php echo $card_width_unit; ?>;		
		<?php if ($card_width_unit == '%') : ?>
			margin-left: <?php echo $margin_in_perc; ?>%; 
			margin-right: <?php echo $margin_in_perc; ?>%;
		<?php else : ?>
			margin-left: 3px;
			margin-right: 3px;
		<?php endif; ?>
	}
	
	.te_<?php echo $suffix; ?> .outerperson {
		position: relative;
		overflow: hidden;
	}
	
		.te_<?php echo $suffix; ?> .iconlinks {
			display: block;
			position: absolute;
			z-index: 50;
			overflow: hidden;
		}
		
			.te_<?php echo $suffix; ?> .iconlinks ul {
				list-style: none;
				margin: 0;	
				padding: 0;
				text-align: center;
			}
			
				.te_<?php echo $suffix; ?> .iconlinks ul li {
					list-style: none;
					display: inline-block;
					margin: 0;
					padding: 0; /* to avoid template overrides */
				}
				
					.te_<?php echo $suffix; ?> .iconlinks a {
						display: inline-block;
					}
					
						.te_<?php echo $suffix; ?> .iconlinks .icon {
							font-size: <?php echo $iconfont_size; ?>em;
							line-height: 1em;
							color: <?php echo $iconfont_color; ?>;
							width: 1em;
							display: inline-block;
							vertical-align: middle;
						}
						
						.te_<?php echo $suffix; ?> .iconlinks span {
							display: none;
						}
	
		.te_<?php echo $suffix; ?> .vcard {
			display: block;
			position: absolute;
			z-index: 50;
		}		
		
			.te_<?php echo $suffix; ?> .vcard a {
				display: inline-block;
			}	
			
				.te_<?php echo $suffix; ?> .vcard .icon {
				    font-size: <?php echo $iconfont_size; ?>em;
					color: <?php echo $iconfont_color; ?>;
					display: inline-block;
    				overflow: hidden;
    				vertical-align: middle;
    			}				

		.te_<?php echo $suffix; ?> .innerperson {
			overflow: hidden;
			position: relative;
			<?php if ($card_height > 0) : ?>
				height: <?php echo $card_height; ?>px;
			<?php endif; ?>
		}		
		
			.te_<?php echo $suffix; ?> .featured .feature {
				display: none;
				line-height: 1em;
			}
			
			.te_<?php echo $suffix; ?> .featured.picture_left .picture_veil .feature,
			.te_<?php echo $suffix; ?> .featured.picture_right .picture_veil .feature,
			.te_<?php echo $suffix; ?> .featured.picture_top .picture_veil .feature,
			.te_<?php echo $suffix; ?> .featured.text_only .text_veil .feature {
				display: inline-block;
			}		
			
				.te_<?php echo $suffix; ?> .featured .feature .icon {
				    font-size: <?php echo $iconfont_size; ?>em;
					color: <?php echo $iconfont_color; ?>;
					display: inline-block;
    				overflow: hidden;
    				padding: 0.2em;	
    				vertical-align: middle;
    			}	
	
			.te_<?php echo $suffix; ?> .personpicture {
    			overflow: hidden;
    			position: relative;
				z-index: 25;
			}
			
			.te_<?php echo $suffix; ?> .picture_left .personpicture {
				float: left;
    			height: 100%;
			}
			
			.te_<?php echo $suffix; ?> .picture_right .personpicture {
				float: right;
    			height: 100%;
			}
			
			.te_<?php echo $suffix; ?> .picture_top .personpicture  {
				margin-left: auto;
				margin-right: auto;
			}
			
			.te_<?php echo $suffix; ?> .text_only .personpicture  {
				display: none;
			}
			
				.te_<?php echo $suffix; ?> .nopicture {
					display: inline-block;
					height: 100%;
					width: 100%;
				}
				
				.te_<?php echo $suffix; ?> .picture {
	    			text-align: center;
	    			position: relative;
	    			width: <?php echo $picture_width; ?>px;
					height: <?php echo $picture_height; ?>px;
				}
			
					.te_<?php echo $suffix; ?> .picture_veil {
		    			z-index: 200;
					}
					
					.te_<?php echo $suffix; ?> .picture a {
		    			display: inline-block;
		    			height: 100%;
		    			width: 100%;
					}
			
					.te_<?php echo $suffix; ?> .picture a:hover {}
					
					.te_<?php echo $suffix; ?> .picture img {
						max-width: <?php echo $picture_width; ?>px;
						max-height: <?php echo $picture_height; ?>px;
					}
			
			.te_<?php echo $suffix; ?> .personinfo {
    			position: relative;
				float: none;
				<?php if (!$overflow) : ?>
					overflow: hidden;
				<?php endif; ?>
			}
			
			.te_<?php echo $suffix; ?> .picture_left .personinfo {
				clear: right;
			}
			
			.te_<?php echo $suffix; ?> .picture_right .personinfo {
				clear: left;
			}
			
			.te_<?php echo $suffix; ?> .text_only .personinfo {}
			
				.te_<?php echo $suffix; ?> .text_veil {
	    			z-index: 200;
				}
			
				.te_<?php echo $suffix; ?> .picture_top .personinfo .fieldname,
				.te_<?php echo $suffix; ?> .picture_top .personinfo .category,
				.te_<?php echo $suffix; ?> .picture_top .personinfo .tags,
				.te_<?php echo $suffix; ?> .ghost_picture_top .personinfo .fieldname,
				.te_<?php echo $suffix; ?> .ghost_picture_top .personinfo .category,
				.te_<?php echo $suffix; ?> .ghost_picture_top .personinfo .tags {
					text-align: center;
				}
				
				.te_<?php echo $suffix; ?> .personinfo .category {
					overflow: hidden;
					text-overflow: ellipsis;
					white-space: nowrap;
				}
			
				.te_<?php echo $suffix; ?> .personinfo .tags {
					/*overflow: hidden;
					text-overflow: ellipsis;
					white-space: nowrap;*/
				}
				
				.te_<?php echo $suffix; ?> .personinfo .personlinks {
					margin-top: 10px;
					overflow: hidden;
				}
				
					.te_<?php echo $suffix; ?> .personlink {
						display: inline;
						margin-right: 3px;
					}				
				
						.te_<?php echo $suffix; ?> .personlink a {
							display: inline-block;
						}
				
				.te_<?php echo $suffix; ?> .personfield {
					overflow: hidden; /* so it does not bleed over the picture */
					text-overflow: ellipsis;
					white-space: nowrap;
				}
			
				.te_<?php echo $suffix; ?> .personfield.fieldname {
					<?php if (!$force_one_line) : ?>
						white-space: normal;
					<?php endif; ?>
				}
				
				.te_<?php echo $suffix; ?> .personfield.fieldmisc {
					overflow: inherit;
					text-overflow: inherit;
					white-space: normal;
				}
				
					.te_<?php echo $suffix; ?> .personinfo .icon {
						font-size: <?php echo $iconfont_size; ?>em;
						line-height: 1em;
						color: <?php echo $iconfont_color; ?>;
						width: 1em;
						display: inline-block;
						margin: 1px 6px 1px 0;
			    		vertical-align: text-top;
					}
					
					.te_<?php echo $suffix; ?> .personinfo .noicon {
						font-size: <?php echo $iconfont_size; ?>em;
						line-height: 1.1em;
						width: 1em;
						display: inline-block;
						margin: 1px 6px 1px 0;
					}
				
					.te_<?php echo $suffix; ?> .fieldlabel {
						/*float: left;*/
						margin-right: 0.2em;
						vertical-align: baseline;
						display: inline-block;
						<?php if ($label_width > 0) : ?>
							width: <?php echo $label_width; ?>px;
						<?php endif; ?>
					}
					
					.te_<?php echo $suffix; ?> .fieldaddress .fieldlabel,
					.te_<?php echo $suffix; ?> .fieldformattedaddress .fieldlabel,
					.te_<?php echo $suffix; ?> .fieldmisc .fieldlabel, 
					.te_<?php echo $suffix; ?> .fieldaddress .icon,
					.te_<?php echo $suffix; ?> .fieldformattedaddress .icon,
					.te_<?php echo $suffix; ?> .fieldmisc .icon {
						vertical-align: top;
					}
					
						.te_<?php echo $suffix; ?> address {
							display: inline-block;
							font-style: normal;
							margin: 0;
						}
						
						.te_<?php echo $suffix; ?> .fieldmisc .fieldvalue {	
							display: inline;				
						}
					
							.te_<?php echo $suffix; ?> .fieldmisc .fieldvalue p {	
								margin: 0;				
							}
				
/* messages */

.te_<?php echo $suffix; ?> .extension-message {
	width: 100%;
}

.te_<?php echo $suffix; ?> .person .extension-message {
	z-index: 100;
	position: absolute;
	bottom: 0;
}

	.te_<?php echo $suffix; ?> .extension-message dl {
		border: 1px solid #EED3D7;
		-webkit-border-radius: 4px;
		-moz-border-radius: 4px;
		border-radius: 4px;
		background-color: #F2DEDE;
		color: #B94A48;
	}
	
	.te_<?php echo $suffix; ?> .person .extension-message dl {
		margin-bottom: 0;
	}

	.te_<?php echo $suffix; ?> .extension-message.error dl {
		border: 1px solid #EED3D7;
		background-color: #F2DEDE;
		color: #B94A48;
	}
	
	.te_<?php echo $suffix; ?> .extension-message.warning dl {
		border: 1px solid #FBEED5;
		background-color: #FCF8E3;
		color: #C09853;
	}

		.te_<?php echo $suffix; ?> .extension-message dt {
			border-bottom: 1px solid #EED3D7;
			padding-left: 5px;
		}
		
		.te_<?php echo $suffix; ?> .extension-message dd {
			word-wrap: break-word;
			margin-bottom: 3px;
			margin-top: 3px;
			margin-left: 5px;
		}
		
/* carousel */

.te_<?php echo $suffix; ?> .carousel_wrapper { 
	cursor: default !important; 
	width: 20px; /* fix for flikering */
	height: 20px; /* fix for flikering */
	overflow: hidden; /* fix for flikering */
}

.te_<?php echo $suffix; ?>.above_navigation .carousel_wrapper,
.te_<?php echo $suffix; ?>.under_navigation .carousel_wrapper {
	margin-left: auto !important;
	margin-right: auto !important;
}

.te_<?php echo $suffix; ?>.side_navigation .carousel_wrapper { 
	/*display: table-cell;*/
}

.te_<?php echo $suffix; ?> .carousel_wrapper ul.weblink_items li { 		    
	display: block !important; 
	float:left;
}

.te_<?php echo $suffix; ?> .items_pagination {
	font-size: <?php echo $arrow_size; ?>em;
	text-align: center;
}

.te_<?php echo $suffix; ?>.side_navigation .items_pagination {
	/*display: table-cell;*/
	position: absolute;
	top: <?php echo $arrow_offset; ?>px;
	z-index: 100;
}

.te_<?php echo $suffix; ?>.side_navigation .items_pagination.bottom {
	right: 0;
}

.te_<?php echo $suffix; ?>.above_navigation .items_pagination {
	display: block;
}

.te_<?php echo $suffix; ?>.above_navigation .items_pagination.top {
	margin-bottom: <?php echo $arrow_offset; ?>px;
}

.te_<?php echo $suffix; ?>.above_navigation .items_pagination.bottom {
	margin-top: <?php echo $arrow_offset; ?>px;
}

.te_<?php echo $suffix; ?>.under_navigation .items_pagination {
	margin-top: <?php echo $arrow_offset; ?>px;
}

.te_<?php echo $suffix; ?> .items_pagination a:hover,
.te_<?php echo $suffix; ?> .items_pagination a:focus {
	text-decoration: none;
}

/* social icons */

.te_<?php echo $suffix; ?> .social .SYWicon-twitter {
	color: #02B0E8 !important;
}

.te_<?php echo $suffix; ?> .social .SYWicon-facebook {
	color: #3B5998 !important;
}

.te_<?php echo $suffix; ?> .social .SYWicon-linkedin {
	color: #0077B6 !important;
}

.te_<?php echo $suffix; ?> .social .SYWicon-googleplus {
	color: #BE2933 !important;
}

.te_<?php echo $suffix; ?> .social .SYWicon-instagram {
	color: #BBAA7C !important;
}

.te_<?php echo $suffix; ?> .social .SYWicon-tumblr {
	color: #2C4762 !important;
}

.te_<?php echo $suffix; ?> .social .SYWicon-pinterest {
	color: #EB5655 !important;
}

.te_<?php echo $suffix; ?> .social .SYWicon-youtube {
	color: #C4302A !important;
}

.te_<?php echo $suffix; ?> .social .SYWicon-vimeo {
	color: #46B5FE !important;
}

.te_<?php echo $suffix; ?> .social .SYWicon-skype {
	color: #28B0EE !important;
}

.te_<?php echo $suffix; ?> .social .SYWicon-wordpress {
	color: #737C81 !important;
}

.te_<?php echo $suffix; ?> .social .SYWicon-blogspot {
	color: #FC9C38 !important;
}

/* CSS3 animations */

/* Grow */
.te_<?php echo $suffix; ?> .grow {
	-webkit-transition-duration: 0.3s;
	transition-duration: 0.3s;
	-webkit-transition-property: -webkit-transform;
	transition-property: transform;
	-webkit-tap-highlight-color: rgba(0, 0, 0, 0);
	-webkit-transform: translateZ(0);
	-ms-transform: translateZ(0);
	transform: translateZ(0);
	box-shadow: 0 0 1px rgba(0, 0, 0, 0);
}
/*
.te_<?php echo $suffix; ?>.ie8 .grow {
	-ms-zoom: 1;
}
*/
.te_<?php echo $suffix; ?> .grow:hover,
.te_<?php echo $suffix; ?> .grow:focus,
.te_<?php echo $suffix; ?> .grow:active {
	-webkit-transform: scale(1.1);
	-ms-transform: scale(1.1);
	transform: scale(1.1);
}
/*
.te_<?php echo $suffix; ?>.ie8 .grow:hover,
.te_<?php echo $suffix; ?>.ie8 .grow:focus,
.te_<?php echo $suffix; ?>.ie8 .grow:active {
	-ms-zoom: 1.1;
}
*/
/* Shrink */
.te_<?php echo $suffix; ?> .shrink {
	-webkit-transition-duration: 0.3s;
	transition-duration: 0.3s;
	-webkit-transition-property: transform;
	transition-property: transform;
	-webkit-transform: translateZ(0);
	transform: translateZ(0);
	box-shadow: 0 0 1px rgba(0, 0, 0, 0);
}
/*
.te_<?php echo $suffix; ?>.ie8 .shrink {
	-ms-zoom: 1;
}
*/
.te_<?php echo $suffix; ?> .shrink:hover, 
.te_<?php echo $suffix; ?> .shrink:focus, 
.te_<?php echo $suffix; ?> .shrink:active {
	-webkit-transform: scale(0.9);
	-ms-transform: scale(0.9);
	transform: scale(0.9);
}
/*
.te_<?php echo $suffix; ?>.ie8 .shrink:hover,
.te_<?php echo $suffix; ?>.ie8 .shrink:focus,
.te_<?php echo $suffix; ?>.ie8 .shrink:active {
	-ms-zoom: 0.9;
}
*/

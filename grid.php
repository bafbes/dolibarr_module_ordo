<?php
/* Copyright (C) 2014 Alexis Algoud        <support@atm-conuslting.fr>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file       /ordo/scrum.php
 *	\ingroup    projet
 *	\brief      Project card
 */

 
	require('config.php');

	/*$TWorkstation = array(
	
		0=>array('nb_ressource'=>1, 'velocity'=>1, 'background'=>'linear-gradient(to right,white, #ccc)', 'name'=>'Non ordonnancé') // base de 7h par jour
		,1=>array('nb_ressource'=>2, 'velocity'=>(5/7), 'background'=>'linear-gradient(to right,white, #660000)', 'name'=>'Stagiaire') // base de 7h par jour
		,2=>array('nb_ressource'=>2, 'velocity'=>(5.5/7), 'background'=>'linear-gradient(to right,white, #cccc00)', 'name'=>'devconfirme')
		,3=>array('nb_ressource'=>1, 'velocity'=>1, 'background'=>'linear-gradient(to right,white,#00cc00)', 'name'=>'DSI')
	);*/
	
	$TWorkstation = array(
        0=>array('nb_ressource'=>1, 'velocity'=>1, 'background'=>'#FFEBD9', 'name'=>'Non ordonnancé','id'=>0) // base de 7h par jour
    );
	
    if($conf->workstation->enabled) {
        define('INC_FROM_DOLIBARR',true);
        dol_include_once('/workstation/config.php');
        $ATMdb=new TPDOdb;
        $TWorkstation = TWorkstation::getWorstations($ATMdb,true,false,$TWorkstation, true);
    }
    else {
        setEventMessage($langs->trans("moduleWorkstationNeeded").' : <a href="https://github.com/ATM-Consulting/dolibarr_module_workstation" target="_blank">'.$langs->trans('DownloadModule').'</a>','errors');
    }

	$number_of_columns = 0 ;
	foreach($TWorkstation as $w_param) {
		$number_of_columns+=$w_param['nb_ressource'];
	}

    $hh =  GETPOST('hour_height');
    if(!empty($hh)) $_SESSION['hour_height'] = (int)$hh;
    
    $cw =  GETPOST('column_width');
    if(!empty($cw)) $_SESSION['column_width'] = (int)$cw;
    
	$tm = GETPOST('tilemode');
	if($tm!=='') $_SESSION['tile_mode'] = (int)$tm;
	
	$hour_height = empty($_SESSION['hour_height']) ? (!empty($conf->global->SCRUM_DEFAULT_HOUR_HEIGHT) ? $conf->global->SCRUM_DEFAULT_HOUR_HEIGHT : 50) : $_SESSION['hour_height'];
    $column_width = empty($_SESSION['column_width']) ? -1 : $_SESSION['column_width'];
    $tile_mode = !isset($_SESSION['tile_mode']) ? 1 : $_SESSION['tile_mode'];
	$day_height =  $hour_height * 7;

	llxHeader('', $langs->trans('GridTasks') , '','',0,0, array('/ordo/js/scrum.js.php'));

	$form = new Form($db);

?>
	<link rel="stylesheet" type="text/css" title="default" href="<?php echo dol_buildpath('/ordo/css/scrum.css',1) ?>">
<?php
    if($hour_height<=10 || ($column_width<=100 && $column_width>0)) {
            
        ?><link rel="stylesheet" type="text/css" title="default" href="<?php echo dol_buildpath('/ordo/css/scrum-small.css',1) ?>"><?php
    }

	if(!empty($tile_mode)) {
		?><link rel="stylesheet" type="text/css" title="default" href="<?php echo dol_buildpath('/ordo/css/scrum-tile.css',1) ?>"><?php
	}

?>
		<div class="content">
	
			<table id="scrum">
				<tr>
					<td style="position:relative;">
						<div class="loading-ordo"><img src="./img/loading.gif" /></div>
					    <!-- <?php echo $langs->trans('WorkStation') ?> - <?php echo ($number_of_columns-1).' '.$langs->trans('NumberOfQueue'); ?>
					    <br /> -->
                        <?php echo $langs->trans('HourHeight') ?> : 
                        <a class="columnHeader columnHeaderMini <?php echo ($hour_height==5 ? 'columnSelectedValue' : '') ?>" href="?hour_height=5"><?php echo $langs->trans('TooSmall') ?></a> 
                        <a class="columnHeader  columnHeaderMini <?php echo ($hour_height==10 ? 'columnSelectedValue' : '') ?>" href="?hour_height=10"><?php echo $langs->trans('Small') ?></a> 
                        <a  class="columnHeader columnHeaderMini <?php echo ($hour_height==50 ? 'columnSelectedValue' : '') ?>" href="?hour_height=50"><?php echo $langs->trans('Middle') ?></a> 
                        <a  class="columnHeader columnHeaderMini <?php echo ($hour_height==100 ? 'columnSelectedValue' : '') ?>" href="?hour_height=100"><?php echo $langs->trans('High') ?></a>
                        -
                        <a  class="columnHeader columnHeaderMini <?php echo ($tile_mode==1 ? 'columnSelectedValue' : '') ?>" href="?tilemode=<?php echo ($tile_mode) ? 0 : 1;  ?>"><?php echo img_picto($langs->trans('TileModeSwitch'), 'tile@ordo') ?></a>
                        <br />
                        <?php echo $langs->trans('ColumnWidth') ?> : 
                        <a class="columnHeader columnHeaderMini <?php echo ($column_width==-1 ? 'columnSelectedValue' : '') ?>" href="?column_width=-1"><?php echo $langs->trans('Auto') ?></a> 
                        <a class="columnHeader columnHeaderMini <?php echo ($column_width==50 ? 'columnSelectedValue' : '') ?>" href="?column_width=50"><?php echo $langs->trans('TooSmall') ?></a> 
                        <a class="columnHeader columnHeaderMini <?php echo ($column_width==100 ? 'columnSelectedValue' : '') ?>" href="?column_width=100"><?php echo $langs->trans('Small') ?></a> 
                        <a  class="columnHeader columnHeaderMini <?php echo ($column_width==200 ? 'columnSelectedValue' : '') ?>" href="?column_width=200"><?php echo $langs->trans('Middle') ?></a> 
                        <a  class="columnHeader columnHeaderMini <?php echo ($column_width==400 ? 'columnSelectedValue' : '') ?>" href="?column_width=400"><?php echo $langs->trans('High') ?></a>
                        <div id="ws-list-top">
					    <?php
					    echo $langs->trans('Workstations').' : ';
                        
					    foreach($TWorkstation as $w_id=>$w_param) {
					        
                            ?><span class="columnHeader columnHeaderMini" id="columm-header1-<?php echo $w_id;  
                            ?>"><a href="javascript:toggleWorkStation(<?php echo $w_id; ?>)"><?php 
                            	echo $w_param['name'].($w_param['velocity']!=1 ? ' '.round($w_param['velocity']*100).'%' : ''); ?></a>
                                <a title="Juste cette colonne" href="javascript:toggleWorkStation(<?php echo $w_id; ?>, true)">(+)</a>
                                <a href="javascript:printWorkStation(<?php echo $w_id; ?>);"><?php echo img_printer(); ?></a>
                            </span><?php
                        }
                     /*   ?><a href="javascript:OrdoReorderAll();" class="columnHeader"><?php echo $langs->trans('Refresh'); ?></a><?php */
					    ?>
                        </div>
					    
					</td>
				</tr>
				<tr>
					<td class="gridster" id="tasks" style="position:relative;">
						<div id="theGrid">							
						<?php
						
						_draw_grid($TWorkstation, $column_width);
						
						if(empty($conf->global->SCRUM_HIDE_PROJECT_LIST_ON_THE_RIGHT)) {
						
						?>
						<div class="projects" style="float:left;">
						    <ul style="position:relative;width:200px; top:38px; overflow:visible;" id="list-projects" class="task-list needToResize" >
                        
                            </ul>
						</div>
						
						<?php 
						}
						?>
					</td>
				</tr>
			</table>
<?php

_js_grid($TWorkstation, $day_height, $column_width);
function _order_by_name(&$a, &$b) {
    
    $r = strcmp($a['name'],$b['name']);
    if($r<0) return -1;
    elseif($r>0) return 1;
    else return 0;
    
}
function _js_grid(&$TWorkstation, $day_height, $column_width) {
    global $conf;
    
     $TWSVisible=array();
   	 if(!empty($_COOKIE['WSTogle'])) {
   	 	foreach($_COOKIE['WSTogle'] as $wsid=>$visible) {
   	 		$TWorkstation[$wsid]['visible'] = $visible;
   	 	}
   	 }
    
   	 $nb_ressource_total = 0;
    foreach($TWorkstation as &$ws) { 
    	if(!isset($ws['visible']) || !empty($ws['visible'])) {
    		$nb_ressource_total+=(!empty($ws['nb_ressource']) ? $ws['nb_ressource'] : 1 );
    	}
    }
    
		?>		
		        <script type="text/javascript">
		            var http = "<?php echo DOL_URL_ROOT; ?>";
		            var w_column = <?php echo $column_width; ?>;
		            var h_day = <?php echo $day_height; ?>;
		            var TDayOff = new Array( <?php echo $conf->global->TIMESHEET_DAYOFF; ?> );
		        </script>
		        <script type="text/javascript" src="./js/ordo.js.php"></script>
	            <script type="text/javascript" src="./js/makefixed.js"></script>
	            <script type="text/javascript" src="./js/svg.js"></script>
	            
        	        <script type="text/javascript">
				var TVelocity = [];
				
				document.ordo = {};

				if(w_column == -1) {
					w_column = parseInt(($( window ).width() - $('#id-left').width() - 50) / <?php echo $nb_ressource_total + (empty($conf->global->SCRUM_HIDE_PROJECT_LIST_ON_THE_RIGHT) ? 2 : 0); ?>);
					$('div.columnordo').each(function(i,item) {
						$item = $(item);
						var nb_r = $item.attr('ws-nb-ressource'); 
						$item.css('width', w_column*nb_r);
						
						$item.find('ul').css('width', w_column*nb_r);	
					});
					
					
				}
				$(document).ready(function(){
  					$('#ws-list-top').width($( window ).width());

				     document.ordo = new TOrdonnancement();
					 
					 <?php
					 	foreach($TWorkstation as $w_id=>$w_param) {
					 	    ?>
					 		
					 		var w = new TWorkstation();
                            w.nb_ressource = <?php echo $w_param['nb_ressource']; ?>;
                            w.velocity = <?php echo $w_param['velocity']; ?>;
                            w.id = "<?php echo $w_id; ?>";
					 		
					 		document.ordo.addWorkstation(w);
	
					 		<?php
						}

                        if(!empty($_COOKIE['WSTogle'])) {
                            foreach($_COOKIE['WSTogle'] as $wsid=>$visible) {
                                
                                if(empty($visible)) {
                                    ?>
                                    toggleWorkStation(<?php echo (int)$wsid; ?>);
                                    <?php
                                    
                                }
                                
                            }
                            
                        }


					 ?>
					  
					document.ordo.init(w_column, h_day,0.08); 		  
					
				});
				</script><?php	
	
}

function _draw_grid(&$TWorkstation, $column_width) {
	
	$width_table = 0;
	foreach($TWorkstation as $w_id=>&$w_param) {
	    
		$back = empty($w_param['background']) ? '' : 'background:'.$w_param['background'].';';
		$w_column = $column_width*$w_param['nb_ressource'];
		
		$width_table+=$w_column;	
		?><div class="columnordo" id="columm-ws-<?php echo $w_id; ?>" valign="top" style="float:left;margin-right: 5px; width:<?php echo round($w_column); ?>px; <?php echo $back; ?> border-right:2px solid #ddd;z-index:1;"  ws-nb-ressource="<?php echo $w_param['nb_ressource']; ?>">
		        <div style="width:<?php echo $column_width ?>px; z-index:1;">
		        	<span class="fixedHeader columnHeader">
		        		<a href="javascript:toggleWorkStation(<?php echo $w_id; ?>)" ws-id="<?php echo $w_id; ?>"><?php echo $w_param['name'].($w_param['velocity'] != 1 ? ' '.round($w_param['velocity']*100).'%' : ''); ?></a>
		        	</span>
		        </div>
				<ul style="position:relative;min-height: 500px;min-width:<?php echo round($w_column); ?>z-index:10;" id="list-task-<?php echo $w_id; ?>" ws-id="<?php echo $w_id; ?>" class="task-list droppable connectedSortable needToResize" rel="all-task" ws-nb-ressource="<?php echo $w_param['nb_ressource']; ?>">
						
				</ul>

		</div><?php 
		
	}
		
	?>
	<script type="text/javascript">
		$('table#scrum').css('min-width', <?php echo $width_table+50 ?>);
		
		
	</script>
	<?php
							
}
	
	
	?>
<!--  <div>
	<span style="background-color:red;">&nbsp;&nbsp;&nbsp;&nbsp;</span> <?php echo $langs->trans('TaskWontfinishInTime'); ?><br />
	<span style="background-color:orange;">&nbsp;&nbsp;&nbsp;&nbsp;</span> <?php echo $langs->trans('TaskMightNotfinishInTime'); ?><br />
	<span style="background-color:#CCCCCC;">&nbsp;&nbsp;&nbsp;&nbsp;</span> <?php echo $langs->trans('BarProgressionHelp'); ?>
	
</div> -->

		
		</div>
		
		<div style="display:none">
			
			<ul>
			<li id="task-blank">
				<header>|||</header>
				<div rel="content">
    				<span rel="project" style="display:none;"></span> <span rel="task-link">[<a href="#" rel="ref"> </a>] <span rel="label" class="classfortooltip" title="">label</span></span>
    				<div rel="divers"></div>
                    <div rel="time-projection" <?php echo empty($conf->global->SCRUM_SHOW_SHOW_ESTIMATED_START_END) ? 'style="display:none"': ''; ?>></div>
                    <div rel="time-rest"></div>
                    <div rel="users"></div>
    				<div rel="time-end"></div>
    				<a href="javascript:;" class="button split" title="<?php echo $langs->trans('SplitTask'); ?>">x</a>
				</div> 
				<div class="loading"></div>
			</li>
			</ul>
			
		</div>
		
<?php

	llxFooter();

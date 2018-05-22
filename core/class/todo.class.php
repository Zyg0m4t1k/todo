<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../../../core/php/core.inc.php';

class todo extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */
	

    public static $_widgetPossibility = array('custom' => true);
	
	public static function changeTodo($action, $idcmd,$id) {
		
		switch ($action) {	
			case 'del': 
			    $cmd = cmd::byId($idcmd);
				 if (is_object($cmd)) {
					 $todo = $cmd->getEqLogic();
					 $cmd->remove();
					 $todo->refreshWidget();
					 $todo->allTodo();					 
				 }	
				break;
			case 'check':
				$cmd = cmd::byId($idcmd);
				if (is_object($cmd)) {
					$todo = $cmd->getEqLogic();
					$cmd->setIsVisible(0);
					$cmd->save();
					$todo->refreshWidget();
					$todo->allTodo();	
				}
				break;			
			case 'new': 
				$todo = todo::byId($id);
				$cmd = $todo->getCmd(null, str_replace(" ", "_",$idcmd));
				if (!is_object($cmd)) {
					$cmd = new todoCmd();
					$cmd->setName(__(str_replace("'", " ",$idcmd), __FILE__));
					$cmd->setLogicalId(str_replace(' ','_',$idcmd));
					$cmd->setEqLogic_id($id);
					$cmd->setType('info');
					$cmd->setSubType('string');
					$cmd->save();
				} else {
					$cmd->setIsVisible(1);
					$cmd->setLogicalId(str_replace(' ','_',$idcmd));
					$cmd->save();
					
				}
				$todo->refreshWidget();	
				$todo->allTodo();
				break;			
		}
	}
	
	public static function editCmd($id, $nom,$info,$datetodo,$timestamp) {
		$cmd= cmd::byId($id);
		$cmd->setName($nom);
		$cmd->setConfiguration('info', $info);
		$cmd->setConfiguration('cron_todo', $datetodo);
		$cmd->setConfiguration('timestamp', $timestamp);
		$cmd->setSubType('string');
		$cmd->save();
		$todo = $cmd->getEqLogic();
		$todo->allTodo();		
		
		
		
	}

 	public  function allTodo() {
	  $cmds = $this->getCmd();
	  $count = count($cmds);
	  $list = '';
	  $i = 1;
	  $except = array('new','getlist','list');
	  foreach ($cmds as $cmd) {
		  if (!in_array( $cmd->getLogicalId(), $except) && $cmd->getIsVisible() == 1) {
			  if ($i == $count ) {
				  $list .=  ' ' . $cmd->getName();
			  } else {
				  $list .=  ' ' . $cmd->getName() . ',';
			  }
		  $i++;
		  }
		  
	  }
	  $this->checkAndUpdateCmd('list', $list);		
	}
	 
    public static function getTodos() {
		 $return = array();
		 $i = 0;
		 $j = 0;
		 foreach (todo::byType('todo') as $todo) {
			 $id = $todo->getId();
				foreach (cmd::byEqLogicId($todo->getId()) as $cmd_todo){
					if ($cmd_todo->getName() != 'Creer') {
					$return[$i]['nom'] = $todo->getName();
					$return[$i]['id'] = $id;	
					$return[$i]['nom_cmd'][$j] = $cmd_todo->getName();
					$return[$i]['id_cmd'][$j] = $cmd_todo->getId();
					$return[$i]['timestamp'][$j] = $cmd_todo->getConfiguration('timestamp');
					if($cmd_todo->getIsVisible() == 1){
						$return[$i]['active'][$j] = 1;
					} else {
						$return[$i]['active'][$j] = 0;
					}
					$j++;
					}
				}
			$i++;
		 }
		 return $return;
	}

	public function removeall() {
		$cmds = $this->getCmd();
		foreach ($cmds as $cmd) {
			if($cmd->getConfiguration('type')) {
				continue;
			}
			
			$cmd->remove();
		}
		$this->refreshWidget();
	}
    public static function createTodo($nom,$todo) {
		$eqLogics = todo::byType('todo');
		$exist = false;
	    foreach($eqLogics as $eqLogic) {
            if ($eqLogic->getName() == $nom) {
				$exist = true;
				$id = $eqLogic->id;
				break;
			} 
		};
		
		if (!$exist) {
			$eqLogic = new eqLogic();
			$eqLogic->setEqType_name('todo');
			$eqLogic->setIsEnable(1);
			$eqLogic->setName($nom);
			$eqLogic->save();
			$id =  $eqLogic->getId();	
		}

		$todoCmd = new todoCmd();
		$todoCmd->setName(__(str_replace("'", " ",$todo), __FILE__));
		$todoCmd->setEqLogic_id($id);
		$todoCmd->setType('info');
		$todoCmd->setSubType('string');
		$todoCmd->setLogicalId(str_replace(' ','_',$todo));
		$todoCmd->save();
		$todo = todo::byId($id);
		$todo->allTodo();
		$todo->refreshWidget();
	}
			


    /*     * *********************Méthodes d'instance************************* */
	
	

    public function postSave() {
		$new = $this->getCmd(null, 'new');
		if (!is_object($new)) {
			$new = new todoCmd();
			$new->setLogicalId('new');
							
		}
		$new->setName(__('New todo', __FILE__));
		$new->setEqLogic_id($this->getId());
		$new->setType('action');
		$new->setSubType('message');
		$new->setConfiguration('type',true);
		$new->save(); 
		
		$list = $this->getCmd(null, 'list');
		if (!is_object($list)) {
			$list = new todoCmd();
			$list->setLogicalId('list');
							
		}
		$list->setName(__('Liste', __FILE__));
		$list->setEqLogic_id($this->getId());
		$list->setType('info');
		$list->setConfiguration('type',true);
		$list->setSubType('string');
		$list->save();
		
		$removeall = $this->getCmd(null, 'removeall');
		if (!is_object($removeall)) {
			$removeall = new todoCmd();
			$removeall->setLogicalId('removeall');
							
		}
		$removeall->setName(__('Remove all', __FILE__));
		$removeall->setEqLogic_id($this->getId());
		$removeall->setConfiguration('type',true);
		$removeall->setType('action');
		$removeall->setSubType('other');
		$removeall->save(); 	
			
		$this->allTodo();
		
	}
	
	

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }

	
	public function toHtml($_version = 'dashboard') {
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		$_version = jeedom::versionAlias($_version);
		$configuration = $this->getconfiguration();
		$id = $this->getId();
		$li = null;
		$test = array();
		$test = cmd::byEqLogicId($id);
		$except = array('new','getlist','list');
		if ($_version != 'mobile') {
			foreach (cmd::byEqLogicId($id) as $cmd_todo){
				$array = array();
				$array = $cmd_todo ;
				$timestamp = $cmd_todo->getConfiguration('timestamp');
				if ($timestamp != '') {
					$now = time();
					if($now > $timestamp && $now < ($timestamp + 86400))  {
						$class = 'today';
					} elseif(($timestamp + 86400) > $now)  {
						$class = 'green';
					} else {
						$class = 'red';
					}
					
				} else {
					$class = '';
				}
				$cmd_id = $cmd_todo->getID();
				$name_event = $cmd_todo->getName();
				if (!in_array( $cmd_todo->getLogicalId(), $except)) {
					if($cmd_todo->getIsVisible() == 1){
					$li .= '<li id="'.$cmd_id.'" class="list-group-item list_edit" style="background-color:transparent;font-size : 0.9em;"><span><input value="'.$cmd_id.'" type="checkbox" >
		</span><span class="name_event_'.$cmd_id.'" name="'.$id.'" >'.$name_event.'</span> <div class="actions"><a  name="'.$id.'"  class="'.$class.'" alt="'.$cmd_id.'">info</a><a  name="'.$id.'"  class="edit" alt="'.$cmd_id.'">Edit</a><img src="plugins/todo/img/delete.png" href="" name="'.$id.'"  class="delete" alt="'.$cmd_id.'"></div> </li>';
					} else {
					$li .= '<li id="'.$cmd_id.'" class="list-group-item list_edit" style="text-decoration:line-through;background-color:transparent;font-size : 0.9em;"><span><input value="'.$cmd_id.'" type="checkbox" checked>
		</span><span class="name_event_'.$cmd_id.'" name="'.$id.'" >'.$name_event.' </span><div class="actions"><a  name="'.$id.'"  class="'.$class.'" alt="'.$cmd_id.'">info</a><a  name="'.$id.'"  class="edit" alt="'.$cmd_id.'">edit</a><img src="plugins/todo/img/delete.png" href="" name="'.$id.'"  class="delete" alt="'.$cmd_id.'"></div> </li>';
					}
				}
			}
		} else {
			foreach (cmd::byEqLogicId($id) as $cmd_todo){
				$conf_todo = $cmd_todo->getConfiguration();
				$cmd_id = $cmd_todo->getID();
				$timestamp = $cmd_todo->getConfiguration('timestamp');
				if ($timestamp != '') {
					$now = time();
					if($now > $timestamp && $now < ($timestamp + 86400))  {
						$class = 'today';
					} elseif(($timestamp + 86400) > $now)  {
						$class = 'green';
					} elseif ($now < $timestamp ) {
						$class = 'red';
					} else {
						$class = '';
					}
					
				}				
				$name_event = $cmd_todo->getName();
				if (!in_array( $cmd_todo->getLogicalId(), $except)) {
					if($cmd_todo->getIsVisible() == 1){
					$li .= '<li id="'.$cmd_id.'" class="list-group-item list_edit" style="background-color:transparent;font-size : 0.9em;"><span><input value="'.$cmd_id.'" type="checkbox" >
		</span><span class="name_mobile name_event_'.$cmd_id.'" name="'.$id.'" >'.$name_event.'</span> <div class="actions"><a  name="'.$id.'"  class="'.$class.'" alt="'.$cmd_id.'">info</a><img src="plugins/todo/img/delete.png" href="" name="'.$id.'"  class="delete" alt="'.$cmd_id.'"></div> </li>';
					} else {
					$li .= '<li id="'.$cmd_id.'" class="list-group-item list_edit" style="text-decoration:line-through;background-color:transparent;font-size : 0.9em;"><span><input value="'.$cmd_id.'" type="checkbox" checked>
		</span><span class="name_mobile name_event_'.$cmd_id.'" name="'.$id.'" >'.$name_event.' </span><div class="actions"><a  name="'.$id.'"  class="'.$class.'" alt="'.$cmd_id.'">info</a><img src="plugins/todo/img/delete.png" href="" name="'.$id.'"  class="delete" alt="'.$cmd_id.'"></div> </li>';
					}
				}
			}
		}


			$replace['#li#'] = $li;
			$replace['#script#'] = "<script>
				loadData('" . $this->getId() . "');
				function changeTodo(_action,_idcmd, _id) {
					$.ajax({// fonction permettant de faire de l'ajax
						type: \"POST\", // methode de transmission des données au fichier php
						url: \"plugins/todo/core/ajax/todo.ajax.php\", // url du fichier php
						data: {
							action: \"changeTodo\",
							acte: _action,
							idcmd: _idcmd,
							id: _id
						},
						dataType: 'json',
						error: function(request, status, error) {
							handleAjaxError(request, status, error);
						},
						success: function(data) { // si l'appel a bien fonctionné
							if (data.state != 'ok') {
								$('#div_alert').showAlert({message:  data.result,level: 'danger'});
								return;
							}
							
							loadData(data.result);
							if (_action == 'new') {
								 $('#'+_id).val('');
							}
						
						}
					});			
				}	
		function loadData(_id) {
			console.log('loadData ' + _id);
			$.ajax({// fonction permettant de faire de l'ajax
				type: \"POST\", // methode de transmission des données au fichier php
				url: \"plugins/todo/core/ajax/todo.ajax.php\", // url du fichier php
				data: {
					action: \"loadData\",
					id: _id 
				},
				dataType: 'json',
				error: function(request, status, error) {
					handleAjaxError(request, status, error);
				},
				success: function(data) { // si l'appel a bien fonctionné
					if (data.state != 'ok') {
						$('#div_alert').showAlert({message:  data.result,level: 'danger'});
						return;
					}
					if (data.result.length != 0) {
						console.log(data.result);
						console.log(_id);
						
						 var html = '',
						 	autoComplete = [];
						 for (var k=0; k<data.result.length; k++) {
							 if(!data.result[k].configuration.type) {
								 if(data.result[k].isVisible == 1){
									 html += '<li id=\"'+data.result[k].id+'\" class=\"list-group-item list_edit\" style=\"background-color:transparent;font-size : 0.9em;\"><span><input value=\"'+data.result[k].id+'\" type=\"checkbox\" ></span><span class=\"name_mobile name_event_'+data.result[k].id+'\" name=\"'+data.result[k].eqLogic_id+'\" >'+data.result[k].name+'</span> <div class=\"actions\"><a  name=\"'+data.result[k].eqLogic_id+'\"  class=\"\" alt=\"'+data.result[k].id+'\">info</a><a  name=\"'+data.result[k].eqLogic_id+'\"  class=\"edit\" alt=\"'+data.result[k].id+'\">Edit</a><img src=\"plugins/todo/img/delete.png\" href=\"\" name=\"'+data.result[k].eqLogic_id+'\"  class=\"delete\" alt=\"'+data.result[k].id+'\"></div> </li>';
								 } else {
									 autoComplete.push(data.result[k].name);
								 }
							 }
						 }
						 
						$('.todo[data-eqLogic_id=\"' + _id + '\"] .list-group').empty().append(html);
						$('.todo[data-eqLogic_id=\"' + _id + '\"] .list-group :checkbox').unbind().change(function() {
							id = $(this).val();
							if(this.checked) {
								changeTodo('check', id ,_id)
							} 
						});	
						
						$('#'+_id).autocomplete({
							source: autoComplete
						});					
								
						$( '.todo[data-eqLogic_id=\"' + _id + '\"] .btn_add' ).unbind().on('click', function() {
							id = $(this).val();
							input = $('#'+id).val();
							if (input == '') {
								return
							}
							changeTodo('new',input,id);
							
						});
						
						$( '.todo[data-eqLogic_id=\"' + _id + '\"] .delete').on('click', function() {
							idcmd = $(this).attr('alt'); 
							id = $(this).attr('name');
							changeTodo('del',idcmd,id)
							
						});
						
						$( '.todo[data-eqLogic_id=\"' + _id + '\"] .edit' ).on('click', function() {
							idcmd = $(this).attr('alt'); 
							$('#md_modal').dialog({
								width : 400,
								height: 400,
								autoOpen: false,
								modal: true,
								
								title: \"Informations\"
							});
							$('#md_modal').load('index.php?v=d&plugin=todo&modal=editcmd&id='+ idcmd);		
							$('#md_modal').dialog('open');
						});							 
					}
				
				}
			});				
			
			
		}							
				
			
			
			
			
			
			
			
			</script>";

			
		return template_replace($replace, getTemplate('core', $_version, 'todo','todo'));
				
	}
	
}

class todoCmd extends cmd {

	
	public function preSave() {
		if ($this->getSubtype() == 'message') {
			$this->setDisplay('title_disable', 1);
		}
	}
	
    public function execute($_options = array()) {
		if ($this->getType() == 'info') {
			return;
		}		
		$todo = $this->getEqLogic();
		switch ($this->getLogicalId()) {
			case 'new': 
			todo::createTodo($todo->getName(),$_options['message']);
			break;	
			case 'removeall': 
			$todo->removeall();
			break;					
		}
    }
    /*     * **********************Getteur Setteur*************************** */
}

?>

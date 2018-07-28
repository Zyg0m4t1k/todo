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
					 $todo->allTodo();	
					 $todo->refreshWidget();
				 }	
				break;
			case 'check':
				$cmd = cmd::byId($idcmd);
				if (is_object($cmd)) {
					$todo = $cmd->getEqLogic();
					$cmd->setIsVisible(0);
					$cmd->save();
					$todo->allTodo();	
					$todo->refreshWidget();
				}
				break;			
			case 'new': 
				$todo = todo::byId($id);
				$cmd = $todo->getCmd(null, str_replace(" ", "_",$idcmd));
				if (!is_object($cmd)) {
					$cmd = new todoCmd();
					$cmd->setName($idcmd);
					$cmd->setLogicalId(str_replace(' ','_',todo::conversion($idcmd)));
					$cmd->setEqLogic_id($id);
					$cmd->setIsVisible(1);
					$cmd->setType('info');
					$cmd->setSubType('string');
					$cmd->save();
				} else {
					$cmd->setIsVisible(1);
					$cmd->setLogicalId(str_replace(' ','_',todo::conversion($idcmd)));
					$cmd->save();
					
				}
				$todo->allTodo();
				$todo->refreshWidget();
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
	  $except = array('new','getlist','list','refresh','removeall');
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
	
	public function refreshList() {
		$cmds = $this->getCmd();
		foreach ($cmds as $cmd) {
			if($cmd->getConfiguration('type')) {
				continue;
			}
			$cmd->setIsVisible(0);
			$cmd->save();
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
		$todoCmd->setName($todo);
		$todoCmd->setEqLogic_id($id);
		$todoCmd->setType('info');
		$todoCmd->setSubType('string');
		$todoCmd->setLogicalId(str_replace(' ','_',todo::conversion($todo)));
		$todoCmd->save();
		$todo = todo::byId($id);
		$todo->allTodo();
		$todo->refreshWidget();
	}
			


    /*     * *********************Méthodes d'instance************************* */
	
	
	public function conversion($string) {
		$caractere = array(">", "<",  ":", "*", "/", "|", "?", '"', '<', '>', "'","&","%");
		$string = str_replace($caractere, "", $string);
		$string = strtolower($string);
		$string = str_replace(
		array(
			'à', 'â', 'ä', 'á', 'ã', 'å',
			'î', 'ï', 'ì', 'í',
			'ô', 'ö', 'ò', 'ó', 'õ', 'ø',
			'ù', 'û', 'ü', 'ú',
			'é', 'è', 'ê', 'ë',
			'ç', 'ÿ', 'ñ','  '
			),
			array(
			'a', 'a', 'a', 'a', 'a', 'a',
			'i', 'i', 'i', 'i',
			'o', 'o', 'o', 'o', 'o', 'o',
			'u', 'u', 'u', 'u',
			'e', 'e', 'e', 'e',
			'c', 'y', 'n',' '
			),
			$string
		);
		
		return $string;
	}	
	
	
	
	public function preSave() {
		if($this->getDisplay('height') == 'auto') {
			$this->setDisplay('height','440px');
		}
		if($this->getDisplay('width') == 'auto') {
			$this->setDisplay('width','350px');
		}			
	}

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
		
		$refresh = $this->getCmd(null, 'refresh');
		if (!is_object($refresh)) {
			$refresh = new todoCmd();
			$refresh->setLogicalId('refresh');
							
		}
		$refresh->setName(__('Refresh', __FILE__));
		$refresh->setEqLogic_id($this->getId());
		$refresh->setConfiguration('type',true);
		$refresh->setType('action');
		$refresh->setSubType('other');
		$refresh->save(); 
					
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
		$version = jeedom::versionAlias($_version);
		if ($version != 'mobile') {
			$replace['#min-width#'] = $replace['#width#'];
			$replace['#min-height#'] = $replace['#height#'];
			$replace['#min-width-list#'] = $replace['#width#']-40;	
			$replace['#min-height-list#'] = $replace['#height#']-110;			
		} else {
			$replace['#min-width#'] = $this->getDisplay('width') + 30;
			$replace['#min-height#'] = $this->getDisplay('height') +50;
			$replace['#min-width-list#'] = $replace['#min-width#']-40;	
			$replace['#min-height-list#'] = $replace['#min-height#']-100;	
		}
		return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'todo', 'todo')));
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
			case 'refresh':
				$todo->refreshList();
			break;					
		}
    }
    /*     * **********************Getteur Setteur*************************** */
}

?>

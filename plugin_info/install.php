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

require_once __DIR__ . '/../../../core/php/core.inc.php';

function todo_install() {
    
}

function todo_update() {
	 foreach (todo::byType('todo') as $todo) {
		 $todo->save();
		 $cmds = $todo->getCmd();
		 foreach($cmds as $cmd) {
			if($cmd->getConfiguration('type')) {
				continue;
			}
			$cmd->setLogicalId(str_replace(' ','_',$cmd->getName()));
			$cmd->save();
		 }			 
	 }
}


function todo_remove() {
    
}

?>

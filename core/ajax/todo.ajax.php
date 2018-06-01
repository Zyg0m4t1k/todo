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

try {
    require_once __DIR__ . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

    if (init('action') == 'changeTodo') {
      	$todo = todo::changeTodo(init('acte'),init('idcmd'),init('id'),init('widget'));
		ajax::success(init('id'));
    } elseif (init('action') == 'editCmd') { 
		 $todo = todo::editCmd(init('id'),init('nom'),init('info'),init('datetodo'),init('timestamp'));
		 ajax::success();
	} elseif (init('action') == 'getAllTodo') { 
		 $return = todo::getTodos();
		 ajax::success($return);
	} elseif (init('action') == 'getTodo') {
		if (init('object_id') == '') {
			$object = object::byId($_SESSION['user']->getOptions('defaultDashboardObject'));
		} else {
			$object = object::byId(init('object_id'));
		}
		if (!is_object($object)) {
			$object = object::rootObject();
		}
		$return = array();
		$return['eqLogics'] = array();
		if (init('object_id') == '') {
			foreach (object::all() as $object) {
				foreach ($object->getEqLogic(true, false, 'todo') as $todo) {
					$return['eqLogics'][] = $todo->toHtml(init('version'));
				}
			}
		} else {
			foreach ($object->getEqLogic(true, false, 'todo') as $todo) {
				$return['eqLogics'][] = $todo->toHtml(init('version'));
			}
			foreach (object::buildTree($object) as $child) {
				$todos = $child->getEqLogic(true, false, 'todo');
				if (count($todos) > 0) {
					foreach ($todos as $todo) {
						$return['eqLogics'][] = $todo->toHtml(init('version'));
					}
				}
			}

		}
		ajax::success($return);
	} elseif (init('action') == 'loadData') {
		$todo = todo::byId(init('id'));
		if (!is_object($todo)) {
			throw new Exception(__('Todo inconnue : ', __FILE__) . init('id'), 9999);
		}
		$cmds = $todo->getCmd();
		usort($cmds, function($a, $b) {
			return $a->getId() - $b->getId();
		});		
		ajax::success(jeedom::toHumanReadable(utils::o2a($cmds)));		
	}
	
		
    throw new Exception(__('Aucune methode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayExeption($e), $e->getCode());
}
?>

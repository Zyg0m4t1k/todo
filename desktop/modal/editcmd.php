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

if (!isConnect('admin'))
{
    throw new Exception('{{401 - Accès non autorisé}}');
}

include_file('3rdparty', 'datetimepicker/jquery.datetimepicker', 'css', 'todo');
include_file('3rdparty', 'datetimepicker/jquery.datetimepicker', 'js', 'todo');

if (init('id') == '')
{
    throw new Exception('{{L\'id de l\'opération ne peut etre vide : }}' . init('id'));
}
$idcmd = init('id');
$cmd = cmd::byId($idcmd);
if (!is_object($cmd))
{
    throw new Exception('{{Aucun todo associé à l\'id : }}' . init('id'));
}

$human_cmd = jeedom::toHumanReadable($cmd);
sendVarToJS('cmdAttr', utils::o2a($human_cmd));
sendVarToJS('cmdId', init('id'));
?>
<div id="cmdEdit">
	<div class="input-group">
		<span class="input-group-addon" id="">Nom</span>
		<input type="text" class="form-control nameCmd todoAttr" data-l1key="name" id="basic-url" aria-describedby="basic-addon3" >
		<input type="hidden" class="form-control nameCmd todoAttr" data-l1key="id" id="basic-url">
	</div>
	<br/>
	<div class="input-group">
		<span class="input-group-addon" id="">Date</span>
		<input type="text" id="datepicker" class="todoAttr configuration form-control" data-l1key="configuration" data-l2key="date" readonly >
		<input type="hidden" id="timestamp" class="todoAttr configuration form-control" data-l1key="configuration" data-l2key="timestamp" value="" >
	</div>
	<br/>
	<div>
		<h4><span class="label label-info">Note</span></h2>
		<textarea class="form-control custom-control infocmd todoAttr" data-l1key="configuration" data-l2key="info" rows="3" style="resize:none"></textarea> 
	</div>
	<br/>
	<div class="alert alert-info">Gestion des coûts</div>
		<div class="input-group col-md-2">
			<span class="input-group-addon">Devise</span>
			<input type="text" class="todoAttr configuration form-control " data-l1key="configuration" data-l2key="devise">
		</div>	
		<br/>	
		<div class="input-group col-md-2">
			<span class="input-group-addon">Prix</span>
			<input type="number" class="todoAttr configuration form-control " data-l1key="configuration" data-l2key="price">
		</div>	
		<br/>
		<div class="input-group col-md-2">
			<span class="input-group-addon">Quantité</span>
			<input type="number" class="todoAttr configuration form-control" data-l1key="configuration" data-l2key="quantity">
		</div>
		<br/>
		<div class="input-group  col-md-2">
			<span class="input-group-addon" >Date</span>
			<input type="text" id="cost" readonly>
		</div>	
		<br/>
		<div class="input-group col-md-2">
			<button type="button" class="btn btn-success btn_add pull-left addPrice"><i class="fas fa-plus"></i></button>
		</div>
		<br/>
		<table id="table_buying" class="table table-bordered table-condensed input-group col-md-6">
			<thead>
				<tr>
					<th>{{date}}</th><th>{{Quantité}}</th><th>{{Prix Unité}}</th> <th>{{Coût}}</th>
				</tr>
			</thead>
			<tfoot>
			  <tr>
				  <td colspan="2"></td>
				<td >Coût total</td>
				<td><input type="text" class="todoAttr configuration form-control " data-l1key="configuration" data-l2key="totalCost"></td>
			  </tr>
			</tfoot>					
			<tbody>
			</tbody>
		</table>
	<br/>
	<div>
	<br/>
	<button type="button" class="btn btn-success btn_editCmd pull-right">Sauvegarder</button>
	</div>
</div>

<?php include_file('desktop', 'editcmd', 'js', 'todo');?>









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


$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
/*
 * Fonction pour l'ajout de commande, appellé automatiquement par plugin.template
 */
 
$('body').delegate('.cmd .cmdAttr[data-l1key=name]', 'focusout', function (event) {
	$(this).next().val($(this).val().replace(" ","_"));
});

function createTodo(action,name,id) {
	if (name == '') {
		return
	}
	changeTodo(action,name,id);
}

function changeTodo(_action,_idcmd, _id) {
	$.ajax({// fonction permettant de faire de l'ajax
		type: "POST", // methode de transmission des données au fichier php
		url: "plugins/todo/core/ajax/todo.ajax.php", // url du fichier php
		global:false,
		data: {
			action: "changeTodo",
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
	//console.log('loadData ' + _id);
	$.ajax({// fonction permettant de faire de l'ajax
		type: "POST", // methode de transmission des données au fichier php
		url: "plugins/todo/core/ajax/todo.ajax.php", // url du fichier php
		global:false,
		data: {
			action: "loadData",
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
				 var html = '',
					autoComplete = [];
				 for (var k=0; k<data.result.length; k++) {
					 
					 var time_class = ""
					  if(data.result[k].configuration.cron_todo && data.result[k].configuration.cron_todo != '') {
						  	
						 var date1 = data.result[k].configuration.cron_todo;
						  console.log(data.result[k].name)
						  console.log(date1);
						 var arrStartDate = date1.split("/");
						 var date1 = new Date(arrStartDate[2],arrStartDate[1] -1,arrStartDate[0]);
						   console.log('date1 ' + date1)
						 var today = new Date();
						 var date2 = new Date(today.getFullYear(),today.getMonth() ,today.getDate());
						  console.log('date2 ' + date2)
						 if (+date1 === +date2) {
							 console.log('today');
							 var time_class = 'darkorange';
						 } else if (+date1 > +date2) {
							 var time_class = 'green';
							  console.log('green');
							 
						 } else {
						 	var time_class = 'red';
							console.log('red');
						 }
						  
//						 var now = Math.floor(t / 1000);
//						   console.log(timestamp);
//						 switch (true) {
//							 case (timestamp < now):
//								var time_class = 'red';
//							 console.log('red');
//							 break;								 
//							 case (now > timestamp && now < Math.floor(timestamp + 86400)):
//								 
//								console.log('today');
//								var time_class = 'darkorange';
//							 break;
//							 case (Math.floor(timestamp + 86400) > now):
//								var time_class = 'green';
//							    console.log('green');
//							 break;
//							 default:
//								console.log('defaut');
//						 }										  
						  
					  }
					 
					 
					 if(!data.result[k].configuration.type) {
						 if(data.result[k].isVisible == 1){
							 html += '<form style="display:none;"><div><label></label></div></form>';											 
							 html += '<li id="'+data.result[k].id+'" class="list-group-item list_edit" style="background-color:transparent;font-size : 1.1em;padding:10px;"><span><input value="'+data.result[k].id+'" type="checkbox" ></span><span class="name_mobile name_event_'+data.result[k].id+'" name="'+data.result[k].eqLogic_id+'" >'+data.result[k].name+'</span> <a  class="delete" name="'+data.result[k].eqLogic_id+'"  alt="'+data.result[k].id+'"><i style="font-size : 0.9em;padding-top:4px;color:rgb(155, 75, 70)" class="fas fa-minus-circle pull-right"></i></a><a  class="edit" name="'+data.result[k].eqLogic_id+'"  alt="'+data.result[k].id+'"><i style="font-size : 0.9em;padding-top:4px;color:rgb(58,90,85)" class="fas fa-sticky-note pull-right"></i></a><a  class="time_edit" name="'+data.result[k].eqLogic_id+'"  alt="'+data.result[k].id+'"><i style="font-size : 0.9em;padding-top:4px;color:' + time_class + '" class="fas fa-info-circle pull-right"></i></a> </li>';
						 } else {
							 autoComplete.push(data.result[k].name);
						 }
					 }
				 }
				$('.todo[data-eqLogic_id="' + _id + '"] .list-group').empty().append(html);
				$('.todo[data-eqLogic_id="' + _id + '"] .list-group :checkbox').unbind().change(function() {
					id = $(this).val();
					if(this.checked) {
						changeTodo('check', id ,_id)
					} 
				});	
				$('#'+_id).autocomplete({
					source: autoComplete
				});					
				$( '.todo[data-eqLogic_id="' + _id + '"] .btn_add' ).unbind().on('click', function() {
					id = $(this).val();
					input = $('#'+id).val();
					if (input == '') {
						return
					}
					changeTodo('new',input,id);
					
				});
				$( '.todo[data-eqLogic_id="' + _id + '"] .delete').on('click', function() {
					idcmd = $(this).attr('alt'); 
					id = $(this).attr('name');
					changeTodo('del',idcmd,id)
					
				});
				$( '.todo[data-eqLogic_id="' + _id + '"] .edit' ).on('click', function() {
					idcmd = $(this).attr('alt'); 
					$('#md_modal').dialog({
						width : 400,
						height: 400,
						autoOpen: false,
						modal: true,
						title: "Informations"
					});
					$('#md_modal').load('index.php?v=d&plugin=todo&modal=editcmd&id='+ idcmd);		
					$('#md_modal').dialog('open');
				});							 
			}
		}
	});				
}	



function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
	console.log(_cmd.name)
	if (_cmd.logicalId == 'getlist' || _cmd.logicalId == 'new' || _cmd.logicalId == 'list' || _cmd.logicalId == 'removeall' || _cmd.logicalId == 'refresh') {
		console.log(_cmd.options)
		var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '" style="display : none;" >';
		tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="id" style="display : none;"><input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 90%;margin-left:auto;margin-right:auto;" placeholder="{{Nom}}" /></td>';
		tr += '<td><span><input type="checkbox" data-size="mini" data-label-text="{{Visible}}" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/></span>'; 
		tr += '<span class="type" type="info" style="display : none;">' + jeedom.cmd.availableType() + '</span>';
		tr += '<span class="subType" subType="' + init(_cmd.subType) + '" style="display : none;"></span>';
		tr += '</td>';
		tr += '<td class="col-lg-6 actionOptions">';
		tr += jeedom.cmd.displayActionOption(init(_cmd, ''), _cmd.options);
		tr += '</td>';			
		tr += '<td><i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
		tr += '</td>';	
		tr += '</tr>';		
		
	} else {
		var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '" >';
		tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="id" style="display : none;"><input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 90%;margin-left:auto;margin-right:auto;" placeholder="{{Nom}}" /><input class="cmdAttr form-control input-sm" data-l1key="logicalId" style="display:none;"  /></td>';
		tr += '<td><input id="ident' + init(_cmd.id) + '" class="delai cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="cron_todo" style="width :90%;margin-left:auto;margin-right:auto;" placeholder="{{delai}}" readonly/><input type="hidden" class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="timestamp" value=""  ></td>';
		tr += '<td><span><input type="checkbox" data-size="mini" data-label-text="{{Visible}}" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/></span>'; 
		tr += '<span class="type" type="info" style="display : none;">' + jeedom.cmd.availableType() + '</span>';
		tr += '<span class="subType" subType="' + init(_cmd.subType) + '" style="display : none;"></span>';
		tr += '</td>';
		tr += '<td><i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
		tr += '</td>';
		tr += '</tr>';		
		

	}


    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));

		
	$( "input[id^='ident']" ).datepicker({
		  dateFormat: 'dd/mm/yy',
		  minDate: 0,	  
		  onClose: function( dateString ){
			  var newdate = dateString.split("/").reverse().join("-");
			  var myDate = new Date( newdate ).getTime() / 1000 ;
		   
			  console.log( 'newdate :' + newdate );
		   
			  $( this ).next().val( myDate );
		   
			  console.log( 'val :' + $( this ).next().val() );
		   }
	});			
}



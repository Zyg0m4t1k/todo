		$("#table_buying").sortable({axis: "y", cursor: "move", items: ".buying", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

		if (cmdAttr != null && is_array(cmdAttr)) {
			$('#cmdEdit').setValues(cmdAttr, '.todoAttr');
			if (isset(cmdAttr.configuration.listbuying)) {
				for (var i in cmdAttr.configuration.listbuying) {
					addBuying(cmdAttr.configuration.listbuying[i].date,cmdAttr.configuration.listbuying[i].quantity,cmdAttr.configuration.listbuying[i].price,cmdAttr.configuration.listbuying[i].pricing);
				}
			}
			sumCost()
		}
	
		$( "#cost" ).datetimepicker({lang: 'fr',
			format: 'd/m/Y',
			timepicker:false
		});	
	
		$( "#datepicker" ).datetimepicker({lang: 'fr',
			format: 'd/m/Y',
			timepicker:false,
			minDate: 0,
			onClose: function(dateString) {
				var myDate = Math.floor(new Date(dateString).getTime()/1000) ;
				$('#timestamp').attr('value',myDate);
				
			}
		});
	
	    function sumCost() {
			var pricing = $('#table_buying').getValues('.buyingattr[data-l1key=pricing]')[0]['pricing'];
			var sum = 0;
			if(isset(pricing)) {
				 if(is_array(pricing)) {
					 for (i=0;i<pricing.length;i++){
						 sum += parseFloat(pricing[i]);
					 }
				 } else {
					sum = parseFloat(pricing);
				 }
			} 			
			$(".todoAttr[data-l2key=totalCost]").val(sum + ' ' + $(".todoAttr[data-l2key=devise]").val());	
		}
	
	    function addBuying(_date,_quantity,_price,_pricing) {
			var tr = '<tr class="buying">';
			tr += '<td><input class="buyingattr form-control input-sm" data-l1key="date" value="'+ _date +'"></td>';
			tr += '<td><input type="number" class="buyingattr form-control input-sm" data-l1key="quantity" value="'+ _quantity +'"></td>';
			tr += '<td><input type="number" class="buyingattr form-control input-sm" data-l1key="price" value="'+ _price +'" ></td>';
			tr += '<td><input type="number" class="buyingattr form-control input-sm" data-l1key="pricing" value="'+  Math.round(_pricing * 100) / 100 +'"></td>';
			tr += '<td>';
			tr += '<a class="remove pull-right"><i class="fas fa-minus-circle"></i></a>';
			tr += '</td>';			
			tr += '</tr>';
			$('#table_buying tbody').append(tr);
			sumCost()			
			
		}
	
		$('.addPrice').on('click', function () {
			addBuying($("#cost").val(),$(".todoAttr[data-l2key=quantity]").val(),$(".todoAttr[data-l2key=price]").val(),parseFloat($(".todoAttr[data-l2key=quantity]").val() * $(".todoAttr[data-l2key=price]").val()))
		});
	
		$('#table_buying').delegate('.remove', 'click', function () {
			$(this).closest('tr').remove();
			sumCost();
		});	
	
	
		$('.btn_editCmd').on('click', function () {
			var dataCmd = $('#cmdEdit').getValues('.todoAttr');
			dataCmd = dataCmd[0];
			dataCmd.configuration.listbuying = $('#table_buying .buying').getValues('.buyingattr');
			$.ajax({// fonction permettant de faire de l'ajax
				type: "POST", // methode de transmission des données au fichier php
				url: "plugins/todo/core/ajax/todo.ajax.php", // url du fichier php
				data: {
					action: "editCmd",
					dataCmd: json_encode(dataCmd)
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
					$('#md_modal').empty().load('index.php?v=d&plugin=todo&modal=editcmd&id=' + cmdId);
				}
			});			
		});	
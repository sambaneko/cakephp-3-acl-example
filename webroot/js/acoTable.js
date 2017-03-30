$(function(){	
	$('[name="acos"]').change(function(){
		if($(this).val() != ''){			
			var newRow = '<tr>'+
				'<td class="acoData" data-alias="'+$(this).val()+'">'+$(this).val()+'</span>'+'</td>'+
				'<td>'+
					'<input type="hidden" value="0" name="perms['+$(this).val()+'][create]"/>'+
					'<input type="checkbox" value="1" name="perms['+$(this).val()+'][create]"/>'+
				'</td>'+
				'<td>'+
					'<input type="hidden" value="0" name="perms['+$(this).val()+'][read]"/>'+
					'<input type="checkbox" value="1" name="perms['+$(this).val()+'][read]"/>'+
				'</td>'+
				'<td>'+
					'<input type="hidden" value="0" name="perms['+$(this).val()+'][update]"/>'+
					'<input type="checkbox" value="1" name="perms['+$(this).val()+'][update]"/>'+
				'</td>'+
				'<td>'+
					'<input type="hidden" value="0" name="perms['+$(this).val()+'][delete]"/>'+
					'<input type="checkbox" value="1" name="perms['+$(this).val()+'][delete]"/>'+
				'</td>'+		
				'<td><a href="#remove">Remove</a></td>'+				
				'</tr>';
			$('#acoTable tbody').find('.none').remove().end().append(newRow);
			$('option:selected',this).remove();
		}
	});
	
	$('#acoTable').on('click','[href="#remove"]',function(){
		var tdAco = $(this).parents('tr').find('.acoData');
		$('[name="acos"]').append(
			'<option value="'+tdAco.data('alias')+'">'+
				tdAco.data('alias')+'</option>');
		$(this).parents('tr').remove();
		return false;
	});
});
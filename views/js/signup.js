$(function() {
	// Avatar url field.
	var _urlField = $('<input/>').attr({
		type: 'text',
		name: 'data[avatar]',
		class: 'form-control  hidden',
		id: 'url_field'
	}).insertAfter('#radio_url');
	$("input[name='data[avatarType]']").on('change', function()
	{
		if (this.value == 'url') {
			$('#url_field').removeClass('hidden').addClass('show');
		}
		else {
			$('#url_field').removeClass('show').addClass('hidden');
		}
	});
});

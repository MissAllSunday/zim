$(function() {
	// Avatar url field.
	var _urlField = $('<input/>').attr({
		type: 'text',
		name: 'data[avatar]',
		class: 'hidden',
		id: 'url_field'
	}).insertAfter('#radio_url');
	$("input[name='data[avatarType]']:radio").on('change', function()
	{
		if (this.value == 'url') {
			$('#url_field').removeClass('hidden').addCalss('show');
		}
		else {
			$('#url_field').removeClass('show').addCalss('hidden');
		}
	});
});

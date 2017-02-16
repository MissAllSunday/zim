$(function() {
	function getClosest(el, divID)
	{
		if (typeof divID == 'undefined' || divID == false)
			return null;

		do
		{
			if (el.nodeName === 'TEXTAREA' || el.nodeName === 'INPUT')
				break;

			if (el.id === divID)
			{
				return el;
			}
		}
		while (el = el.parentNode);

		// not found :(
		return null;
	}

	function getSelectedText(divID)
	{
		var selection,
			text = '',
			found = 0,
			container = document.createElement("div");

		if (window.getSelection)
		{
			selection = window.getSelection();
			text = selection.toString();
		}
		else if (document.selection && document.selection.type != 'Control')
		{
			selection = document.selection.createRange();
			text = selection.text;
		}

		// Need to be sure the selected text does belong to the right div.
		for (var i = 0; i < selection.rangeCount; i++) {
				s = getClosest(selection.getRangeAt(i).startContainer, divID);
				e = getClosest(selection.getRangeAt(i).endContainer, divID);

				if (s !== null && e !== null)
				{
					found = 1;
					container.appendChild(selection.getRangeAt(i).cloneContents());
					text = container.innerHTML;
					break;
				}
			}

		delete container;
		return found === 1 ? text : false;
	}

	$('.body').mouseup(function(e) {
		var _this = $(this),
			msgID = _this.attr('id').replace('body', ''),
			text = getSelectedText(_this.attr('id'));

		if (text){
			$('#selectText'+ msgID).removeClass('hidden').addClass('show');
			window.selectHive[msgID] = window.selectHive[msgID] || [];
			window.selectHive[msgID].push(text);
		}
	});

	$('.selectText').on( 'click', function(e) {
		var thisButton = $(this),
			msgID = thisButton.attr('id').replace('selectText', '');

		if (window.selectHive[msgID]){
			var quote = $('<blockquote>'+ window.selectHive[msgID] +'</blockquote>');
			$('#summernote').summernote('insertNode', quote.get(0));
			window.selectHive[msgID] = [];
			delete quote;
		}

		thisButton.removeClass('show').addClass('hidden');
	});
});

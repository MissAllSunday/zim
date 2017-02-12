$(function() {

	// Tooltip
	 $('[data-toggle="tooltip"]').tooltip();

	// Tags.
	if (typeof randomColor !== "undefined")
	{
		var tags = [];
		$('.topic').each(function( index, value ) {
			_thisTopic = $(this);

			$.each(_thisTopic.data('tags'), function( index, value ) {
				_thisTopic.addClass('tag_' + value.replace(' ', ''));
			});
		});

		$('span.tag').each(function( index, value ) {
			_this = $(this);
			_this.randomColor = randomColor();
			_this.uniqueTag = _this.find('a').html().replace(' ', '');
			_this.css('background-color', _this.randomColor);
			tags[_this.uniqueTag] = _this.randomColor;
		});

		$('span.tag').on('click', function() {
			_this = $(this);
			_this.uniqueTag = _this.find('a').html().replace(' ', '');

			// Hide everything.
			$('.topic').hide('slow', function() {});

			// Show the topics that has this tag.
			$('.tag_'+ _this.uniqueTag).show('slow', function() {});
		});

		$('span.tag').on('mouseenter', function() {
			_this = $(this);
			_this.uniqueTag = _this.find('a').html().replace(' ', '');

			// Show the topics that has this tag.
			$('.tag_'+ _this.uniqueTag).css('border-left', '2px solid ' + tags[_this.uniqueTag]);
		});

		$('span.tag').on('mouseleave', function() {
			_this = $(this);
			_this.uniqueTag = _this.find('a').html().replace(' ', '');
			$('.tag_'+ _this.uniqueTag).css('border-left', 'none');
		});

		// Reset
		$('#resetTags').on('click', function(){
			$('.topic').show('slow', function() {});
		});
	}

	// highlight.js
	hljs.configure({
		tabReplace: '    ',
		languages: ["PHP", "CoffeeScript", "CSS", "Diff", "JavaScript", "JSON"]
	});
	hljs.initHighlighting();
	$('pre').each(function(i, block) {
		hljs.highlightBlock(block);
	});

	// Pagination
	var next = $('ul.pagination li.active').nextAll().slice(0,5).last(),
		nextHide = next.nextUntil($('ul.pagination li.last')),
		prev = $('ul.pagination li.active').prevAll().slice(0,5).last(),
		prevHide = prev.prevUntil($('ul.pagination li.first'));

		prevHide.hide();
		nextHide.hide();

	// Summernote.
	if (typeof $.summernote !== 'undefined'){
		$.extend($.summernote.options,{
			microDataImg:{
				icon:'<i class="note-icon-pencil"/>',
				removeEmpty:true
			}
		});
		$.extend($.summernote.plugins,{
			'microDataImg' : function (context) {
				var self = this,
					ui = $.summernote.ui,
					$note = context.layoutInfo.note,
					$editor = context.layoutInfo.editor,
					$editable = context.layoutInfo.editable,
					options = context.options,
					lang = options.langInfo;

				context.memo('button.microDataImg',function(){
					var button = ui.button({
						contents: options.microDataImg.icon,
						tooltip: 'microDataImg',
						click: function () {
							context.invoke('microDataImg.show');
						}
					});
					return button.render();
				});
				this.initialize = function(){
					var $container = options.dialogsInBody ? $(document.body) : $editor,
						body =
					'<div class="form-group">' +
					'<label>' + lang.link.url + '</label>' +
					'<input class="note-link-imgUrl form-control" type="text" /></div>';
					var footer = '<button href="#" class="btn btn-primary note-link-btn">' + lang.link.insert + '</button>';

					this.$dialog = ui.dialog({
						className: 'link-dialog',
						title: lang.link.insert,
						fade: options.dialogsFade,
						body: body,
						footer: footer
					}).render().appendTo($container);
				};
				this.destroy = function(){
					ui.hideDialog(this.$dialog);
					this.$dialog.remove();
				};
				this.show = function(){
					ui.showDialog(self.$dialog);
					var $imgUrl = self.$dialog.find('.note-link-imgUrl'),
						$editBtn= self.$dialog.find('.note-link-btn').click(function(e){
							e.preventDefault();
							self.buildImg($imgUrl.val());
							return false;
						});
					ui.onDialogHidden(self.$dialog,function(){
						$editBtn.off('click');
					});
				};
				this.buildImg = function(imgUrl){
					var imgDom = new Image();
						imgDom.src = imgUrl;
					$(imgDom).load(function(){
						idom = $('<div/>', {
							class: 'centertext',
							itemprop: 'image',
							itemscope: '',
							itemtype:'https://schema.org/ImageObject'
						}).append('<meta itemprop="url" content="'+ this.src +'"><meta itemprop="width" content="'+ this.width +'"><meta itemprop="height" content="'+ this.height +'">');
						$(this).appendTo(idom);

						$('#summernote').summernote('insertNode', idom.get(0));
					}).attr({
						class: 'img-responsive img-rounded center-block'
					});
					ui.hideDialog(self.$dialog);
				};
			}
		});

		$('#summernote').summernote({
			minHeight: 200,
			popover: {
				image: [
					['imagesize', ['imageSize100', 'imageSize50', 'imageSize25']],
					['float', ['floatLeft', 'floatRight', 'floatNone', 'imageAttributes', 'imageShape']],
					['remove', ['removeMedia']]
				],
			},
			lang: 'en-US',
			toolbar: [
				['para', ['style', 'ul', 'ol', 'paragraph']],
				['insert', ['microDataImg', 'picture', 'link', 'table', 'hr']],
				['height', ['height']],
				['misc', ['undo', 'redo', 'help']],
				['font', ['fontsize', 'color', 'bold', 'italic', 'underline', 'clear']],
			]
		});
	}
});

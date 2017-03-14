$(function() {

	// Tooltip
	$('[data-toggle="tooltip"]').tooltip();

	//  Dropdown
	$('.dropdown-toggle').dropdown();

	// Generic "you sure?  really you sure?" message
	$(document).on('click', '.you_sure', function()
	{
		return confirm($(this).attr('data-confirm'));
	});

	// Tags.
	if (typeof randomColor !== "undefined")
	{
		var tags = [];
		$('.topic').each(function( index, value ) {
			var _thisTopic = $(this);

			$.each(_thisTopic.data('tags'), function( index, value ) {
				_thisTopic.addClass('tag_' + value.replace(' ', ''));
			});
		});

		$('span.tag').each(function( index, value ) {
			var _this = $(this);
			_this.randomColor = randomColor();
			_this.uniqueTag = _this.find('a').html().replace(' ', '');
			_this.css('background-color', _this.randomColor);
			tags[_this.uniqueTag] = _this.randomColor;
		});

		$('span.tag').on('click', function() {
			var _this = $(this);
			_this.uniqueTag = _this.find('a').html().replace(' ', '');

			// Hide everything.
			$('.topic').hide('slow', function() {});

			// Show the topics that has this tag.
			$('.tag_'+ _this.uniqueTag).show('slow', function() {});
		});

		$('span.tag').on('mouseenter', function() {
			var _this = $(this);
			_this.uniqueTag = _this.find('a').html().replace(' ', '');

			// Show the topics that has this tag.
			$('.tag_'+ _this.uniqueTag).find('.media-body').css({
				'border-left': '2px solid ' + tags[_this.uniqueTag],
			});
		});

		$('span.tag').on('mouseleave', function() {
			var _this = $(this);
			_this.uniqueTag = _this.find('a').html().replace(' ', '');
			$('.tag_'+ _this.uniqueTag).find('.media-body').css('border-left', '#fff');
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

/*! loadCSS. [c]2017 Filament Group, Inc. MIT License */
(function(w){
	"use strict";
	/* exported loadCSS */
	var loadCSS = function( href, before, media ){
		// Arguments explained:
		// `href` [REQUIRED] is the URL for your CSS file.
		// `before` [OPTIONAL] is the element the script should use as a reference for injecting our stylesheet <link> before
			// By default, loadCSS attempts to inject the link after the last stylesheet or script in the DOM. However, you might desire a more specific location in your document.
		// `media` [OPTIONAL] is the media type or query of the stylesheet. By default it will be 'all'
		var doc = w.document;
		var ss = doc.createElement( "link" );
		var ref;
		if( before ){
			ref = before;
		}
		else {
			var refs = ( doc.body || doc.getElementsByTagName( "head" )[ 0 ] ).childNodes;
			ref = refs[ refs.length - 1];
		}

		var sheets = doc.styleSheets;
		ss.rel = "stylesheet";
		ss.href = href;
		// temporarily set media to something inapplicable to ensure it'll fetch without blocking render
		ss.media = "only x";

		// wait until body is defined before injecting link. This ensures a non-blocking load in IE11.
		function ready( cb ){
			if( doc.body ){
				return cb();
			}
			setTimeout(function(){
				ready( cb );
			});
		}
		// Inject link
			// Note: the ternary preserves the existing behavior of "before" argument, but we could choose to change the argument to "after" in a later release and standardize on ref.nextSibling for all refs
			// Note: `insertBefore` is used instead of `appendChild`, for safety re: http://www.paulirish.com/2011/surefire-dom-element-insertion/
		ready( function(){
			ref.parentNode.insertBefore( ss, ( before ? ref : ref.nextSibling ) );
		});
		// A method (exposed on return object for external use) that mimics onload by polling document.styleSheets until it includes the new sheet.
		var onloadcssdefined = function( cb ){
			var resolvedHref = ss.href;
			var i = sheets.length;
			while( i-- ){
				if( sheets[ i ].href === resolvedHref ){
					return cb();
				}
			}
			setTimeout(function() {
				onloadcssdefined( cb );
			});
		};

		function loadCB(){
			if( ss.addEventListener ){
				ss.removeEventListener( "load", loadCB );
			}
			ss.media = media || "all";
		}

		// once loaded, set link's media back to `all` so that the stylesheet applies once it loads
		if( ss.addEventListener ){
			ss.addEventListener( "load", loadCB);
		}
		ss.onloadcssdefined = onloadcssdefined;
		onloadcssdefined( loadCB );
		return ss;
	};
	// commonjs
	if( typeof exports !== "undefined" ){
		exports.loadCSS = loadCSS;
	}
	else {
		w.loadCSS = loadCSS;
	}
}( typeof global !== "undefined" ? global : this ));
/*! loadCSS rel=preload polyfill. [c]2017 Filament Group, Inc. MIT License */
(function( w ){
  // rel=preload support test
  if( !w.loadCSS ){
    return;
  }
  var rp = loadCSS.relpreload = {};
  rp.support = function(){
    try {
      return w.document.createElement( "link" ).relList.supports( "preload" );
    } catch (e) {
      return false;
    }
  };

  // loop preload links and fetch using loadCSS
  rp.poly = function(){
    var links = w.document.getElementsByTagName( "link" );
    for( var i = 0; i < links.length; i++ ){
      var link = links[ i ];
      if( link.rel === "preload" && link.getAttribute( "as" ) === "style" ){
        w.loadCSS( link.href, link, link.getAttribute( "media" ) );
        link.rel = null;
      }
    }
  };

  // if link[rel=preload] is not supported, we must fetch the CSS manually using loadCSS
  if( !rp.support() ){
    rp.poly();
    var run = w.setInterval( rp.poly, 300 );
    if( w.addEventListener ){
      w.addEventListener( "load", function(){
        rp.poly();
        w.clearInterval( run );
      } );
    }
    if( w.attachEvent ){
      w.attachEvent( "onload", function(){
        w.clearInterval( run );
      } )
    }
  }
}( this ));

$(function() {
	// highlight.js
	hljs.configure({
		tabReplace: '    ',
		languages: ["PHP", "CoffeeScript", "CSS", "Diff", "JavaScript", "JSON"]
	});
	hljs.initHighlighting();
	$("pre").each(function(i, block) {
		hljs.highlightBlock(block);
	});
});

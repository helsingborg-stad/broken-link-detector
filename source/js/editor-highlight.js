(function waitForTinyMCE() {
  if (typeof tinymce !== 'undefined') {
    function applyBrokenLinkHighlight(editor) {
      let styles = '';
      brokenLinkEditorHighlightData.links.forEach(function(item) {
        styles += `a[data-mce-href*="${item}"] {text-decoration: underline wavy #f00 !important; cursor: not-allowed; pointer-events: none;}`;
      });
      const styleElement = editor.dom.create('style', { id: 'broken-link-styles' }, styles);
      editor.getDoc().head.appendChild(styleElement);
    }

    window.onload = function() {
      tinymce.editors.forEach(function(editor) {
          applyBrokenLinkHighlight(editor);
      });
    };

  } else {
    setTimeout(waitForTinyMCE, 350); // Retry every 50 milliseconds
  }
})();
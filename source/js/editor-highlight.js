(function waitForTinyMCE(retries = 100) {
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
  } else if (retries > 0) {
    setTimeout(() => waitForTinyMCE(retries - 1), 350);
  } else {
    console.warn("TinyMCE failed to load within the specified retries.");
  }
})();
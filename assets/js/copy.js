// Utilidad para copiar (no requerido si usas el inline del PHP)
(function(w){
  w.copyToClipboard = function (text) {
    if (!navigator.clipboard) {
      const ta = document.createElement('textarea');
      ta.value = text || '';
      document.body.appendChild(ta);
      ta.select();
      try { document.execCommand('copy'); } catch(e) {}
      document.body.removeChild(ta);
      return;
    }
    navigator.clipboard.writeText(text || '');
  };
})(window);

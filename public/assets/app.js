// Minimal JS helpers
function copyToClipboard(text){
  navigator.clipboard?.writeText(text).then(()=>alert('Lien copié')); 
}

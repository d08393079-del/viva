document.addEventListener('click', function (e) {
  if (!e.target.closest('#open-chat')) return;
  e.preventDefault();
  function open(){ tidioChatApi.open(); }
  if (window.tidioChatApi && typeof tidioChatApi.open === 'function') open();
  else document.addEventListener('tidioChat-ready', open, { once:true });
});
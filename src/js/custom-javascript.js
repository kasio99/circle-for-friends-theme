// Add your custom JS here.

document.addEventListener('input', function(e) {

  if (!e.target.matches('input[id*="date-of-birth"]')) {
    return;
  }

  let value = e.target.value.replace(/\D/g, '');

  if (value.length > 2) {
    value = value.slice(0,2) + '/' + value.slice(2);
  }

  if (value.length > 5) {
    value = value.slice(0,5) + '/' + value.slice(5,9);
  }

  e.target.value = value;

});
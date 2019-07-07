/**
 * Inicia as máscara para os forms
 *
 * https://igorescobar.github.io/jQuery-Mask-Plugin/
 */

var initMaskInput = function () {
  $('.maskYear').mask('0000', {placeholder: '____'});
  $('.maskTime').mask('00:00', {placeholder: '__:__'});
  $('.maskDate').mask('00/00/0000', {placeholder: '__/__/____'});
  $('.maskDateTime').mask('00/00/0000 00:00', {placeholder: '__/__/____ __:__'});
  $('.maskMoney').mask('#.##0,00', {reverse: true});
  $('.maskFloat').mask('#.##0,00', {reverse: true});
  $('.maskNumber').mask('#00', {reverse: true});
  $('.maskCpf').mask('000.000.000-00', {reverse: true, placeholder: '___.___.___-__'});
  $('.maskRg').mask('00.000.000-0', {reverse: true, placeholder: '__.___.___-_'});
  $('.maskCnpj').mask('00.000.000/0000-00', {reverse: true, placeholder: '__.___.___/____-__'});
  
  $('.maskCep').mask('00000-000', {
    onKeyPress: function (cep, e, field, options) {
      var masks = ['00000-000', '0-00-00-00'];
      var mask = (cep.length > 7) ? masks[0] : masks[1];
      
      $('.maskCep').mask(mask, options);
    },
    
    placeholder: '_____-___',
  });
  
  /**
   * @return {string}
   */
  var SPMaskBehavior = function (val) {
    return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
  }, spOptions       = {
    onKeyPress: function (val, e, field, options) {
      field.mask(SPMaskBehavior.apply({}, arguments), options);
    },
    
    placeholder: "(__) 9____-____",
  };
  
  $('.maskPhone').mask(SPMaskBehavior, spOptions);
  $('.maskTelephone').mask('(00) 0000-0000', {placeholder: '(__) ____-____'});
};

/* Carrega o documento */
$(document).ready(function () {
  /* INIT :: Mask Input */
  if (typeof onLoadHtmlSuccess !== 'undefined' && typeof onLoadHtmlSuccess === 'function') {
    onLoadHtmlSuccess(function () {
      initMaskInput();
    });
  } else {
    initMaskInput();
  }
});

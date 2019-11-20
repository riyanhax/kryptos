/**
 * Star rating class - show 5 stars with the ability to select a rating
 */

// configure the class for runtime loading
if (!window.fbControls) window.fbControls = [];
window.fbControls.push(function(controlClass) {
  /**
   * Star rating class
   */
  class controlDatetime extends controlClass {

    /**
     * Class configuration - return the icons & label related to this control
     * @returndefinition object
     */
    static get definition() {
      return {
          icon: '<i class="fa fa-clock-o"></i>',
        i18n: {
          default: 'Data i czas'
        }
      };
    }

    /**
     * javascript & css to load
     */
    configure() {
      
    }

    /**
     * build a text DOM element, supporting other jquery text form-control's
     * @return {Object} DOM Element to be injected into the form.
     */
    build() {
      var className = (this.config.className)?this.config.className:'form-control';
      return this.markup('input', null, {id: this.config.name, name: this.config.type, className: className});
    }

    /**
     * onRender callback
     */
    onRender() {
      let value = this.config.value || '';
      $('#'+this.config.name).val(value);
    }
  }

  // register this control for the following types & text subtypes
  controlClass.register('datetime', controlDatetime);
  return controlDatetime;
});
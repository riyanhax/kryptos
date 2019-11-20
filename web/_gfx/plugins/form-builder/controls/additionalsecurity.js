/**
 * Star rating class - show 5 stars with the ability to select a rating
 */

// configure the class for runtime loading
if (!window.fbControls) window.fbControls = [];
window.fbControls.push(function(controlClass) {
  /**
   * Star rating class
   */
  class controlAdditionalsecurity extends controlClass {

    /**
     * Class configuration - return the icons & label related to this control
     * @returndefinition object
     */
    static get definition() {
      return {
        icon: '🌟',
        i18n: {
          default: 'Additionalsecurity'
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
      var dataNextElement = false;
      return this.markup('input', null, {dataNextElement, id: this.config.name, name: this.config.type, className: className});
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
  controlClass.register('additionalsecurity', controlAdditionalsecurity);
  return controlAdditionalsecurity;
});

/* INSERT INTO `entities` (`id`, `author_id`, `system_name`, `title`, `config`, `created_at`, `updated_at`) VALUES (NULL, '0', 'additionalsecurity', 'Additional security', '{\"type\":\"int\",\"baseModel\":\"RiskAssessmentSafeguards\",\"element\":{\"tag\":\"bs.typeahead\",\"url\":\"/riskAssessmentSafeguards/addmini/addmini/?useProcess=true\",\"model\":\"RiskAssessmentSafeguards\"}}', '0000-00-00 00:00:00', NULL); */
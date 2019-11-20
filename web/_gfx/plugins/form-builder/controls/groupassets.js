/**
 * Star rating class - show 5 stars with the ability to select a rating
 */

// configure the class for runtime loading
if (!window.fbControls) window.fbControls = [];
window.fbControls.push(function(controlClass) {
  /**
   * Star rating class
   */
  class controlGroupassets extends controlClass {

    /**
     * Class configuration - return the icons & label related to this control
     * @returndefinition object
     */
    static get definition() {
      return {
        icon: '🌟',
        i18n: {
          default: 'Groupassets'
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
  controlClass.register('groupassets', controlGroupassets);
  return controlGroupassets;
});

/* INSERT INTO `entities` (`id`, `author_id`, `system_name`, `title`, `config`, `created_at`, `updated_at`) VALUES (NULL, '0', 'groupassets', 'groupassets', '{\"type\":\"int\",\"baseModel\":\"RiskAssessmentAssetGroups\",\"element\":{\"tag\":\"bs.typeahead\",\"url\":\"/riskAssessmentAssetGroups/addmini/addmini/?useProcess=true\",\"model\":\"RiskAssessmentAssetGroups\"}}', '0000-00-00 00:00:00', NULL); */
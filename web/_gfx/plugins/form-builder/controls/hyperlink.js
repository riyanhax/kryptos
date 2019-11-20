/**
 * Star rating class - show 5 stars with the ability to select a rating
 */

// configure the class for runtime loading
if (!window.fbControls) window.fbControls = [];
window.fbControls.push(function(controlClass) {
    /**
     * Star rating class
     */
    class controlHyperLink extends controlClass {

        /**
         * Class configuration - return the icons & label related to this control
         * @returndefinition object
         */
        static get definition() {
            return {
                icon: '<i class="fa fa-link"></i>',
                inactive: [],
                i18n: {default: 'Hyperlink'}
            };
        }

        /**
         * build a text DOM element, supporting other jquery text form-control's
         * @return {Object} DOM Element to be injected into the form.
         */
        build() {
            return this.markup('a', this.label, {href: this.config.url, target: '_blank', className: 'btn-link', style: 'text-decoration: underline;'});
        }

        /**
         * onRender callback
         */
        onRender() {
            let container = jQuery(this.element).parents('.form-field:first');
            if (!container.data('controls-initialized')) {
                let urlField = container.find('.url-wrap :input');
                if (!urlField.val()) { urlField.val('#'); }
                container.find('.field-label:first').hide();
                container.data('controls-initialized', true);
            }
        }
    }

    controlClass.register('hyperlink', controlHyperLink);
    return controlHyperLink;
});

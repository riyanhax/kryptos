/**
 * Star rating class - show 5 stars with the ability to select a rating
 */

// configure the class for runtime loading
if (!window.fbControls) window.fbControls = [];
window.fbControls.push(function(controlClass) {
    /**
     * Smart Radio Group
     */
    class smartMultiSelect extends controlClass {

        /**
         * Class configuration - return the icons & label related to this control
         * @returndefinition object
         */
        static get definition() {
            return {
                icon: '<i class="fa fa-check-square"></i>',
                inactive: ['subtype'],
                i18n: {default: 'Smart Multi Select'}
            };
        }

        /**
         * build a text DOM element, supporting other jquery text form-control's
         * @return {Object} DOM Element to be injected into the form.
         */
        build() {
            return this.markup('div', [
                this.markup('button', 'Add relation', {className: 'btn-default btn'})
            ], {id: this.config.name, name: this.config.type});
        }

        /**
         * onRender callback
         */
        onRender() {
            let container = jQuery(this.element).parents('.form-field:first');
            if (!container.data('controls-initialized')) {
                container.find('.frm-holder .registry-wrap :input').fbMultiSelect({
                    hiddenInput: container.find('.frm-holder .registries-wrap :input')
                });
                container.data('controls-initialized', true);
            }
        }
    }

    controlClass.register('smartMultiSelect', smartMultiSelect);
    return smartMultiSelect;
});

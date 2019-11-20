/**
 * Star rating class - show 5 stars with the ability to select a rating
 */

// configure the class for runtime loading
if (!window.fbControls) window.fbControls = [];
window.fbControls.push(function(controlClass) {
    /**
     * Star rating class
     */
    class controlSignature extends controlClass {

        /**
         * Class configuration - return the icons & label related to this control
         * @returndefinition object
         */
        static get definition() {
            return {
                icon: '<i class="fa fa-pencil"></i>',
                inactive: ['subtype'],
                i18n: {default: 'Signature'}
            };
        }

        /**
         * build a text DOM element, supporting other jquery text form-control's
         * @return {Object} DOM Element to be injected into the form.
         */
        build() {
            return this.markup('img', '', {
                id: this.config.name,
                name: this.config.type,
                className: 'form-control',
                src: '/_gfx/libs/signature-pad/signature.png',
                style:'width:500px; height:300px;'
            });
        }

        /**
         * onRender callback
         */
        onRender() {
            let container = jQuery(this.element).parents('.form-field:first');
            if (!container.data('controls-initialized')) {
                container.find('.frm-holder .value-wrap').remove();
                container.data('controls-initialized', true);
            }
        }
    }

    controlClass.register('signature', controlSignature);
    return controlSignature;
});
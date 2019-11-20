/**
 * Star rating class - show 5 stars with the ability to select a rating
 */

// configure the class for runtime loading
if (!window.fbControls) window.fbControls = [];
window.fbControls.push(function(controlClass) {
    /**
     * Star rating class
     */
    class controlRating extends controlClass {

        /**
         * Class configuration - return the icons & label related to this control
         * @returndefinition object
         */
        static get definition() {
            return {
                icon: '<i class="fa fa-star"></i>',
                inactive: ['subtype'],
                i18n: {default: 'Rating'}
            };
        }

        /**
         * build a text DOM element, supporting other jquery text form-control's
         * @return {Object} DOM Element to be injected into the form.
         */
        build() {
            // this.markup('button', '1', {type: 'button', className: 'btn btn-default'}),
            return this.markup('div', [
                this.markup('span', 'Not satisfied '),
                this.markup('div', [1, 2, 3, 4, 5].map(function(number){
                    return this.markup('button', number+'', {type: 'button', className: 'btn btn-default'});
                }.bind(this)), {className: 'btn-group', role: 'group'}),
                this.markup('span', ' Completely satisfied')
            ], {id: this.config.name, name: this.config.type});
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

    controlClass.register('rating', controlRating);
    return controlRating;
});
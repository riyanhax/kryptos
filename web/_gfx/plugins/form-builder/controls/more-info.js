/**
 * Star rating class - show 5 stars with the ability to select a rating
 */

// configure the class for runtime loading
if (!window.fbControls) window.fbControls = [];
window.fbControls.push(function(controlClass) {

    /**
     * Star rating class
     */
    class controlMoreInfo extends controlClass {

        /**
         * Class configuration - return the icons & label related to this control
         * @returndefinition object
         */
        static get definition() {
            return {
                icon: '<i class="fa fa-info"></i>',
                inactive: [],
                i18n: {default: 'More Info'}
            };
        }

        /**
         * build a text DOM element, supporting other jquery text form-control's
         * @return {Object} DOM Element to be injected into the form.
         */
        build() {
            return [this.label + ' ', this.markup('button', this.label, {
                'type': 'button',
                'className': 'btn btn-info btn-xs js-more-info',
                'data-description':  this.description ? this.description : ''
            })];
        }

        onMoreClick(input, container) {
            let modal = $(container).find('.js-more-container');
            let message = $(input).data('description');
            if (!message) {
                message = 'Some Info...';
            }
            if (!modal.length) {
                modal = $('<div class="js-more-container" title="More Info"/>')
                    .appendTo(container);
                modal.dialog({
                    autoOpen: false
                });
            }
            modal.html('<p>'+message+'</p>');
            modal.dialog('open');
        }

        /**
         * onRender callback
         */
        onRender() {
            let container = jQuery(this.element).parents('.form-field:first');
            if (!container.data('controls-initialized')) {
                let descriptionField = container.find('.description-wrap :input');
                descriptionField.replaceWith($('<textarea>').attr({
                    'class': descriptionField.attr('class'),
                    'id': descriptionField.attr('id'),
                    'name': descriptionField.attr('name'),
                }).val($.trim(descriptionField.val())));
                container.find('.description-wrap label').text('Info Text');
                container.find('.field-label:first, .tooltip-element:first').hide();
                container.on('click', '.js-more-info', function (e) {
                    this.onMoreClick(e.target, container);
                    return false;
                }.bind(this));
                container.data('controls-initialized', true);
            }
        }
    }

    controlClass.register('moreInfo', controlMoreInfo);
    return controlMoreInfo;
});

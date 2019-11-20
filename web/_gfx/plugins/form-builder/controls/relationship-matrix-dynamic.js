/**
 * Multiple choice relationship matrix
 */

// configure the class for runtime loading
if (!window.fbControls) window.fbControls = [];
window.fbControls.push(function(controlClass, allControlClasses) {
    class controlRelationshipMatrixDynamic extends allControlClasses['relationshipMatrix']{
        /**
         * Control configuration
         * @inheritDoc
         */
        static get definition() {
            return {
                icon: '<i class="fa fa-bars"></i>',
                i18n: {
                    default: 'Matrix (dynamic rows)'
                }
            };
        }

        /**
         * Control constructor
         */
        constructor() {
            super();
            this.demoRowsCount = 1;
            this.tableClass = 'js-dynamic-relationship-matrix';
            this.rowClass = 'js-matrix-row';
            this.addButtonTargetClass = 'js-add-button';
            this.delButtonTargetClass = 'js-del-button';
            this.addButtonTemplate = '<button class="btn btn-default '+this.addButtonTargetClass+'"><i class="fa fa-plus-circle"></i></button>';
            this.delButtonTemplate = '<button class="btn btn-default '+this.delButtonTargetClass+'"><i class="fa fa-minus-circle"></i></button>';
        }

        /**
         * Render callback
         */
        onRender() {
            this.loadRegistryEntries()
                .done(this.renderPreview.bind(this));
        };

        /**
         * Render callback
         */
        onAddButtonClick(button) {
            let lastRow = $(button).parents('tr:first');
            let position = this.getNextPosition();
            lastRow.before(
                '<tr class="' + this.rowClass + '">' +
                '<td>Row #' + position + '</td>' +
                '<td align="right">' + this.renderFormElement(position, 'from') + '</td>' +
                '<td>-</td>' +
                '<td align="left">' + this.renderFormElement(position, 'to') + '</td>' +
                '<td align="left">' + this.delButtonTemplate + '</td>' +
                '</tr>');
        };

        /**
         * Render callback
         */
        onDelButtonClick(button) {
            $(button).parents('tr:first').remove();
        };

        /**
         * @inheritDoc
         */
        renderPreview(data) {
            this.assignFormEvents(data);
            this.getElementNode().html(
                this.markup('table', [
                    this.markup('tr', [
                        this.markup('td', '', {'colspan': 4}),
                        this.markup('td', this.addButtonTemplate)
                    ])
                ], {className: 'ui-widget-content table '+this.tableClass})
            );
        }

        /**
         * @inheritDoc
         */
        renderFormElement(index, suffix) {
            return '<select class="matrix-'+suffix+'-'+index+' form-control">'+
                '<option></option>' +
                this.getRegistryValues().map(function(register){
                    return '<optgroup label="'+register.title+'">' +
                        register.values.map(function(value){
                            return '<option value="'+value.id+'">'+value.title+'</option>';
                        }.bind(this)) + '</optgroup>';
                }.bind(this)).join('') + '</select>';
        }

        /**
         * Assign events
         */
        assignFormEvents(data) {
            let addButtonSelector = '.' + this.addButtonTargetClass;
            let delButtonSelector = '.' + this.delButtonTargetClass;
            jQuery(this.element)
                .on('click', addButtonSelector, function (event) {
                    this.onAddButtonClick(event.target);
                    return false;
                }.bind(this))
                .on('click', delButtonSelector, function (event) {
                    this.onDelButtonClick(event.target);
                    return false;
                }.bind(this))
                .one("DOMSubtreeModified", function() {
                    let i;
                    this.setRegistryValues(data);
                    let button = $(this.element).find(addButtonSelector).get(0);
                    for (i = 0; i < this.demoRowsCount; i++) {
                        this.onAddButtonClick(button);
                    }
                }.bind(this));
        };

        /**
         * @returns {array|*}
         */
        getRegistryValues()
        {
            return $(this.element).data('registry-values');
        }

        /**
         * @returns {number}
         */
        getNextPosition()
        {
            let position = $(this.element).data('position');
            isNaN(position) ? (position = 1) : position++;
            $(this.element).data('position', position);
            return position;
        }

        /**
         * @param {array|*} data
         */
        setRegistryValues(data)
        {
            $(this.element).data('registry-values', data);
        }
    }
    controlClass.register('relationshipMatrixDynamic', controlRelationshipMatrixDynamic);
    return controlRelationshipMatrixDynamic;
});

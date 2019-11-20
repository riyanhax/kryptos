/**
 * Extra choice relationship matrix
 */

// configure the class for runtime loading
if (!window.fbControls) window.fbControls = [];
window.fbControls.push(function(controlClass, allControlClasses) {
    class controlRelationshipMatrixExtra extends allControlClasses['relationshipMatrix']{
        /**
         * @inheritDoc
         */
        static get definition() {
            return {
                icon: '<i class="fa fa-th-list"></i>',
                i18n: {
                    default: 'Matrix (extra choice)'
                }
            };
        }

        /**
         * Render callback
         */
        onRender() {
            let registry1 = this.getSelectedRegistry('registry'),
                registry2 = this.getSelectedRegistry('registry2'),
                registry3 = this.getSelectedRegistry('registry3');
            this.renderHolderControls();
            if (!registry1 || !registry2 || !registry3) {
                this.renderAlert('Please specify registries in form settings');
                return;
            }
            let stringifyParams = {};
            stringifyParams[registry1] = this.config.registryStringify;
            stringifyParams[registry2] = this.config.registry2Stringify;
            stringifyParams[registry3] = this.config.registry3Stringify;
            this.loadRegistryEntries([registry1, registry2, registry3], stringifyParams)
                .done(this.renderPreview.bind(this));
        };

        /**
         * @inheritDoc
         */
        renderPreview(data) {
            if (!Object.keys(data).length) {
                this.renderAlert('Server error', 'danger');
                return;
            }

            let registry1 = this.getSelectedRegistry('registry'),
                registry2 = this.getSelectedRegistry('registry2'),
                registry3 = this.getSelectedRegistry('registry3');
            let tableRows = this.getFormElementRows(
                this.getDataValues(data, registry1),
                this.getDataValues(data, registry2),
                this.getDataValues(data, registry3),
            );
            tableRows.unshift(this.getFormElementHeaderRow(this.getDataValues(data, registry2)));
            this.getElementNode().html(
                this.markup('table', tableRows.map(function(row){
                    return this.markup('tr', row.map(function(cell){
                        return this.markup('td', ''+ cell);
                    }.bind(this)));
                }.bind(this)), {className: 'ui-widget-content table'})
            );
        };

        /**
         * Render form element holder controls
         */
        renderHolderControls() {
            let holderNode = this.getHolderNode();
            if (!holderNode.data('matrix-initialized')) {
                holderNode.find('.value-wrap').remove();
                holderNode.data('matrix-initialized', true);
                let registry = this.getInput(holderNode, 'registry');
                let registry2 = this.getInput(holderNode, 'registry2');
                let registry3 = this.getInput(holderNode, 'registry3');
                let registryStringify = this.getInput(holderNode, 'registryStringify');
                let registry2Stringify = this.getInput(holderNode, 'registry2Stringify');
                let registry3Stringify = this.getInput(holderNode, 'registry3Stringify');
                jQuery([registryStringify, registry2Stringify, registry3Stringify]).each(function (i, input) {
                    input.attr('type', 'hidden');
                    jQuery('<div class="fields-wrap"/>').appendTo(input.parent())
                        .on('change', ':checkbox', function () {
                            this.onFieldListChange(input);
                        }.bind(this))
                }.bind(this));
                registry.change(function(){
                    this.renderRegistryFields(registry, registryStringify);
                }.bind(this)).change();
                registry2.change(function(){
                    this.renderRegistryFields(registry2, registry2Stringify);
                }.bind(this)).change();
                registry3.change(function(){
                    this.renderRegistryFields(registry3, registry3Stringify);
                }.bind(this)).change();
            }
        };

        /**
         * @param row
         * @param col
         * @param items
         * @returns {string}
         */
        renderFormElement(row, col, items) {
            // value="'+row.id+'-'+col.id+'
            return '<select name="'+this.config.name+'[]">' +
                '<option value=""></option>' +
                jQuery(items).map(function(i, item){
                    return '<option value="'+row.id+'-'+col.id+'-'+item.id+'">'+item.title+'</option>';
                }).get().join('') +
                '</select>';
        };


        /**
         * Compose list of header cells by registry
         * @param {array} rowItems
         * @param {array} columnItems
         * @param {array} typeItems
         * @returns {array}
         */
        getFormElementRows(rowItems, columnItems, typeItems) {
            return rowItems.map(function(row) {
                let rows = columnItems.map(function(col) {
                    return this.renderFormElement(row, col, typeItems);
                }.bind(this));
                rows.unshift(row.title);
                return rows;
            }.bind(this));
        };
    }
    controlClass.register('relationshipMatrixExtra', controlRelationshipMatrixExtra);
    return controlRelationshipMatrixExtra;
});

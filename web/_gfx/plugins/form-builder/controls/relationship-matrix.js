/**
 * Single choice relationship matrix
 * @use jQuery
 */

// configure the class for runtime loading
if (!window.fbControls) window.fbControls = [];
window.fbControls.push(function(controlClass, allControlClasses) {
    /**
     * @extends {controlRelationshipSelect}
     * @property {{registryStringify, registry2Stringify}} config
     */
    class controlRelationshipMatrix extends allControlClasses['select'] {
        /**
         * Control configuration
         * @returns {{icon: string, inactive: string[], i18n: {default: string}}}
         */
        static get definition() {
            return {
                icon: '<i class="fa fa-list-ul"></i>',
                inactive: ['subtype'],
                i18n: {default: 'Matrix (single choice)'}
            };
        }

        /**
         * build a text DOM element
         * @returns {Object}
         */
        build() {
            return this.markup('div', [
                this.markup('span', null, {className: 'loading'})
            ], {className: 'relationship-matrix-content'});
        };

        /**
         * Render callback
         */
        onRender() {
            let registry1 = this.getSelectedRegistry('registry'),
                registry2 = this.getSelectedRegistry('registry2');
            this.renderHolderControls();
            if (!registry1 || !registry2) {
                this.renderAlert('Please specify registries in form settings');
                return;
            }
            let stringifyParams = {};
            stringifyParams[registry1] = this.config.registryStringify;
            stringifyParams[registry2] = this.config.registry2Stringify;
            this.loadRegistryEntries([registry1, registry2], stringifyParams)
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
                registry2 = this.getSelectedRegistry('registry2');
            let tableRows = this.getFormElementRows(
                this.getDataValues(data, registry1),
                this.getDataValues(data, registry2)
            );
            tableRows.unshift(this.getFormElementHeaderRow(this.getDataValues(data, registry2)));
            this.getElementNode().html(
                this.markup('table', tableRows.map(function(row){
                    return this.markup('tr', row.map(function(cell){
                        return this.markup('td', ''+ cell);
                    }.bind(this)));
                }.bind(this)), {className: 'ui-widget-content table'})
            );
        }

        /**
         * Render form element
         * @param data
         */
        renderFormElement(row, col) {
            return '<input type="radio" name="'+this.config.name+'" value="'+row.id+'-'+col.id+'"/>';
        }

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
                let registryStringify = this.getInput(holderNode, 'registryStringify');
                let registry2Stringify = this.getInput(holderNode, 'registry2Stringify');
                jQuery([registryStringify, registry2Stringify]).each(function (i, input) {
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
            }
        }

        /**
         * Compose list of header cells by registry
         * @param {array} items
         * @returns {array}
         */
        getFormElementHeaderRow(items) {
            let cells = items.map(function(col) {
                return col.title;
            });
            cells.unshift('');
            return cells;
        };

        /**
         * Compose list of header cells by registry
         * @param {array} rowItems
         * @param {array} columnItems
         * @returns {array}
         */
        getFormElementRows(rowItems, columnItems) {
            return rowItems.map(function(row) {
                let rows = columnItems.map(function(col) {
                    return this.renderFormElement(row, col);
                }.bind(this));
                rows.unshift(row.title);
                return rows;
            }.bind(this));
        };

        /**
         * Return registry values
         * @param registries
         * @param registryId
         * @returns {*}
         */
        getDataValues(registries, registryId) {
            let i;
            for (i = 0; i < registries.length; i++) {
                if (registries[i].id == registryId) {
                    return registries[i].values;
                }
            }
            return [];
        }
    }

    controlClass.register('relationshipMatrix', controlRelationshipMatrix);
    return controlRelationshipMatrix;
});

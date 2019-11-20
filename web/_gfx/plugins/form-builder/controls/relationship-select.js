/**
 * Single choice relationship matrix
 * @use jQuery
 */

// configure the class for runtime loading
if (!window.fbControls) window.fbControls = [];
window.fbControls.push(function(controlClass) {
    /**
     * @property {{registryStringify}} config
     */
    class controlRelationshipSelect extends controlClass {

        /**
         * Control configuration
         * @returns {{icon: string, inactive: string[], i18n: {default: string}}}
         */
        static get definition() {
            return {
                icon: '<i class="fa fa-caret-square-o-down"></i>',
                inactive: ['subtype'],
                i18n: {default: 'Select / Checkbox'}
            };
        }

        /**
         * build a text DOM element
         * @returns {Object}
         */
        build() {
            return this.markup('div', [
                this.markup('span', null, {className: 'loading'})
            ], {className: 'relationship-select-content'});
        };

        /**
         * Render callback
         */
        onRender() {
            let registry = this.getSelectedRegistry('registry');
            this.renderHolderControls();
            if (!registry) {
                this.renderAlert('Please specify registries in form settings');
                return;
            }
            let stringifyParams = {registry: this.config.registryStringify};
            this.loadRegistryEntries([registry], stringifyParams)
                .done(this.renderPreview.bind(this));
        };

        /**
         *
         * @param {jQuery} registryStringify
         */
        onFieldListChange(registryStringify) {
            let checkedFields = registryStringify.parent().find('.fields-wrap :checked').map(function () {
                return $(this).val();
            }).get();
            registryStringify.val( checkedFields.join(',') );
            this.onRender(); // trigger preview rerender
        }

        /**
         * Render element preview
         * @param data
         */
        renderPreview(data) {
            if (!Object.keys(data).length) {
                this.renderAlert('Selected register is empty', 'warning');
                return;
            }
            let registry = Number(this.getSelectedRegistry('registry'));
            let fieldName = this.config.name;
            let html = '';
            if (this.config.multiple) {
                for (let i = 0; i < data.length; i++) {
                    if (data[i].id !== registry) {
                        continue;
                    }
                    for (let v = 0; v < data[i].values.length; v++) {
                        let value = data[i].values[v];
                        html += '<div style="padding-left: 10px;">' +
                                '<input type="checkbox" name="'+fieldName+'[]" value="'+value.id+'"> ' +
                                '<span>' + value.title + '</span>' +
                            '</div>';
                    }
                }
            } else {
                html += '<select class="form-control" name="'+fieldName+'">';
                for (let i = 0; i < data.length; i++) {
                    if (data[i].id !== registry) {
                        continue;
                    }
                    for (let v = 0; v < data[i].values.length; v++) {
                        let value = data[i].values[v];
                        html += '<option value="'+value.id+'">'+value.title+'</option>';
                    }
                }
                html += '</select>';
            }
            this.getElementNode().html(html);
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
                let registryStringify = this.getInput(holderNode, 'registryStringify');
                jQuery([registryStringify]).each(function (i, input) {
                    input.attr('type', 'hidden');
                    jQuery('<div class="fields-wrap"/>').appendTo(input.parent())
                        .on('change', ':checkbox', function () {
                            this.onFieldListChange(input);
                        }.bind(this))
                }.bind(this));
                registry.change(function(){
                    this.renderRegistryFields(registry, registryStringify);
                }.bind(this)).change();
            }
        }

        /**
         *
         * @param {jQuery} registry
         * @param {jQuery} registryStringify
         */
        renderRegistryFields(registry, registryStringify) {
            let fields = registry.find('option:selected').data('fields');
            let listContainer = registryStringify.parent().find('.fields-wrap');
            let fieldIndex = registryStringify.attr('name');
            let fieldsHTML = '<ul class="frmb-control ui-sortable">';
            if (!fields) fields = [];
            let selectedIds = jQuery( (registryStringify.val()+'').split(/[\s,\,]/) )
                .filter(function(i, a){ return a.length; }).get();
            if (!selectedIds.length) {
                selectedIds = jQuery(fields).map(function (i, field) { return field.stringify ? field.id : false; })
                    .filter(function(i, value){ return value !== false; }).get();
            }
            for (let i=0; i<selectedIds.length; i++) {
                for (let f=0; f<fields.length; f++) {
                    if (selectedIds[i] !== fields[f].id) {
                        continue;
                    }
                    fieldsHTML += this.renderRegistryFieldFlag(fieldIndex, fields[f], true);
                }
            }
            for (let f=0; f<fields.length; f++) {
                if (jQuery.inArray(fields[f].id, selectedIds) !== -1) {
                    continue;
                }
                fieldsHTML += this.renderRegistryFieldFlag(fieldIndex, fields[f], false);
            }
            fieldsHTML += '</ul>';
            listContainer.html(fieldsHTML);
            listContainer.find('ul.ui-sortable').sortable({
                update: function () {
                    this.onFieldListChange(registryStringify);
                }.bind(this)
            });
            this.onFieldListChange(registryStringify);
        };

        /**
         * @param {string} fieldIndex
         * @param {object} field
         * @param {bool} checked
         * @returns {string}
         */
        renderRegistryFieldFlag(fieldIndex, field, checked) {
            return '<li>' +
                '<i class="fa fa-ellipsis-v" style="float: right;"></i>' +
                '<input type="checkbox" name="fields[' + fieldIndex + '][]" ' +
                'value="' + field.id + '"' + (checked ? ' checked="checked"' : '') + '/> ' +
                field.title +
                '</li>';
        }

        /**
         * Render alert message
         * @param {string} message
         * @param {string} [type]
         */
        renderAlert(message, type) {
            if (!type) type = 'info';
            this.getElementNode().html(
                this.markup('i', message, {className: 'alert ' + type})
            );
        };

        /**
         * Load relations matrix from backend
         * @param {number[]} [registryIds]
         * @param {Object} [stringifyParams]
         * @returns {Object} fetch promise
         */
        loadRegistryEntries(registryIds, stringifyParams) {
            if(!registryIds) {
                registryIds = [];
            }
            if(!stringifyParams) {
                stringifyParams = [];
            }
            return jQuery.get(
                '/registry-entries/ajax-get-values' +
                registryIds.map(function(id) { return '/registry_id/' + id; }).join(''),
                {stringify: stringifyParams}, null, 'json'
            ).fail(function(){
                this.renderAlert('Server error', 'danger');
            }.bind(this));
        };

        /**
         * Validate and return registry value
         * @returns {number|null}
         */
        getSelectedRegistry (valueName) {
            if (isNaN(this.config[valueName])) {
                return null;
            }
            return this.config[valueName];
        };

        /**
         * Return control holder node
         * @returns {jQuery}
         */
        getHolderNode() {
            return jQuery(this.element)
                .parents('.form-field:first')
                .find('.frm-holder:first');
        }

        /**
         * Return input by name and context
         * @param {jQuery|HTMLElement|string} context
         * @param {string} name
         * @returns {*}
         */
        getInput(context, name) {
            return jQuery(context)
                .find('.input-wrap :input[name='+name+']');
        }

        /**
         * Return element DOM node
         * @returns {object}
         */
        getElementNode() {
            return jQuery(this.element);
        };
    }

    controlClass.register('select', controlRelationshipSelect);
    return controlRelationshipSelect;
});

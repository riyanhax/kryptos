$.widget('formBuilder.fbExpression', {
    _modal: null,
    _field: null,
    _expressions: {
        'logical_true': {name: 'Logical: True', expression: 'true'},
        'logical_false': {name: 'Logical: False', expression: 'false'},
        'comparision': {name: 'String comparison', expression: 'a == b'},
    },
    options: {
        editButtonSelector: '.js-expression-edit',
        modalWidth: 500,
        modalHeight: 430,
        modalTemplate: '<div id="dialog-form" title="Edit expression"><form>' +
            '<div class="form-group">' +
            '<label>Expression Type</label>' +
            '<select class="form-control js-expression-type"></select>' +
            '</div>' +
            '<div class="js-form-expression-parameters"></div>' +
            '</form></div>',
        onApply: $.noop
    },
    _create: function () {
        $(this.element).on('click', this.options.editButtonSelector, function (event) {
            let clickTarget = event.currentTarget ? event.currentTarget : event.target,
                $expressionField = $(clickTarget).parents('.form-group:first').find(':input[type=hidden]:first'),
                modal = this._buildModal($expressionField);
            $(clickTarget).blur();
            modal.dialog( "open" );
            return false;
        }.bind(this));
    },
    _applyExpression: function () {
        let expressionTypeSelected = $(this._modal).find('.js-expression-type').val(),
            expressionSelected = this._expressions[expressionTypeSelected];
        if (typeof expressionSelected === 'undefined') {
            alert('Please select sexpression type');
            return false;
        }
        let result = {expression: expressionSelected.expression, bindings: {}};
        switch (expressionTypeSelected) {
            case 'comparision':
                result.bindings.a = $(this._modal).find('.js-value-a').val();
                result.bindings.b = $(this._modal).find('.js-value-b').val();
                if (!result.bindings.a) {
                    alert('Please select value "a"');
                    return false;
                }
                break;
            case 'logical_true':
            case 'logical_false':
                break;
        }
        $(this._field).val(JSON.stringify(result));
        this.options.onApply(this._field);
        return true;
    },
    _closeModal: function () {
        this._modal.dialog( "close" );
    },
    _buildModal: function (field) {
        let fieldValue = this._parseFieldValue(field);
        if (!this._modal) {
            this._modal = $(this.options.modalTemplate)
                .appendTo(this.element).dialog({
                    autoOpen: false,
                    modal: true,
                    height: this.options.modalHeight,
                    width: this.options.modalWidth,
                    buttons: {
                        Apply: function () {
                            if (!this._applyExpression()) {
                                return false;
                            }
                            this._closeModal();
                        }.bind(this),
                        Cancel: function() {
                            this._closeModal();
                        }.bind(this)
                    }
                });
        }
        this._modal.find('.js-form-expression-parameters')
            .html(this._buildExpressionParamersBlock(fieldValue));
        this._modal.find('.js-expression-type')
            .html(this._buildExpressionTypeOptions(fieldValue))
            .change(function(event){
                let selectedType = $(event.target).val();
                this._modal.find('.js-form-expression-parameters')
                    .html(this._buildExpressionParamersBlock(this._expressions[selectedType]));
            }.bind(this));
        this._field = field;
        return this._modal;
    },
    _buildExpressionTypeOptions: function (selectedValue) {
        let expressionTypeOptions = '', expressionType,
            seelectExpressionType = this._findFieldType(selectedValue);
        for (expressionType in this._expressions) {
            if ( !this._expressions.hasOwnProperty(expressionType)) {
                continue;
            }
            expressionTypeOptions += '<option value="'+expressionType+'" ' +
                (seelectExpressionType===expressionType?'selected="selected"':'')+'>' +
                this._expressions[expressionType].name + '</option>';
        }
        return expressionTypeOptions;
    },
    _buildExpressionParamersBlock: function (selectedValue) {
        let expressionSelected = this._findFieldType(selectedValue),
            bindings = selectedValue.bindings ? selectedValue.bindings : {};
            content = '<div class="form-group">' +
                '<label>Preview:</label>' +
                '<span class="form-control" readonly="readonly">'+selectedValue.expression+'</span>' +
                '</div>';
        switch (expressionSelected) {
            case 'comparision':
                content += '<div class="form-group">' +
                    '<label>Value "a" (form field):</label>' +
                    '<select class="form-control js-value-a">' + this._buildFieldListOptions(bindings.a)  + '</select>' +
                    '</div>' +
                    '<div class="form-group">' +
                    '<label>Value "b" (scalar):</label>' +
                    '<input type="text" class="form-control js-value-b" value="'+(bindings.b?bindings.b:'')+'"/>' +
                    '</div>';
                break;
            case 'logical_true':
            case 'logical_false':
                break;
        }
        return content;
    },
    _buildFieldListOptions: function (selectedValue) {
        let content = '<option></option>', fieldName, fieldTitle;
        $(this.element).find('.form-field').map( function(){
            fieldName = $(this).find('.fld-name').val();
            fieldTitle = $(this).find('.fld-label').text() + ' (' + fieldName + ')';
            fieldName = '{'+fieldName+'}';
            content += '<option value="' + fieldName + '" ' + (selectedValue === fieldName ? 'selected="selected"':'')+'>' + fieldTitle + '</option>';
        });
        return content;
    },
    _parseFieldValue: function (field) {
        let serializedData = $(field).val(), data;
        try {
            data = JSON.parse(serializedData);
        } catch (e) { }
        if($.isPlainObject(data) && data.expression) {
            return data;
        }
        return this._expressions.logical_true;
    },
    _findFieldType: function (selectedValue) {
        for (let expressionType in this._expressions) {
            if (
                this._expressions.hasOwnProperty(expressionType)
                && selectedValue.expression === this._expressions[expressionType].expression
            ) {
                return expressionType;
            }
        }
        return null;
    }
});

/**
 * Multiple choice relationship matrix
 */

// configure the class for runtime loading
if (!window.fbControls) window.fbControls = [];
window.fbControls.push(function(controlClass, allControlClasses) {
    class controlRelationshipMatrixMultiple extends allControlClasses['relationshipMatrix']{
        /**
         * @inheritDoc
         */
        static get definition() {
            return {
                icon: '<i class="fa fa-list"></i>',
                i18n: {
                    default: 'Matrix (multiple choice)'
                }
            };
        }

        /**
         * @inheritDoc
         */
        renderFormElement(row, col) {
            return '<input type="checkbox" name="'+this.config.name+'[]" value="'+row.id+'-'+col.id+'">';
        }
    }
    controlClass.register('relationshipMatrixMultiple', controlRelationshipMatrixMultiple);
    return controlRelationshipMatrixMultiple;
});

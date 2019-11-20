<?php

namespace migrations;

use Application_Model_Entities as EntityModel;

require_once __DIR__ .'/lib.inc.php';

$deleteSql = <<<SQL
DELETE FROM `entities`
SQL;


$addSql = <<<SQL
INSERT INTO `entities` (`id`, `author_id`, `system_name`, `title`, `config`, `created_at`)
VALUES (?, ?, ?, ?, ?, ?)
SQL;

$data = [
    [
        EntityModel::ID_VARCHAR,
        0,
        'varchar',
        'Pole tekstowe',
        '{"type":"string","element":{"tag":"bs.varchar"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_TEXT_AREA,
        0,
        'text-ckeditor',
        'Pole tekstowe z edytorem',
        '{"type":"text","baseModel":"RegistryEntriesEntitiesText","element":{"tag":"textarea","class":"ckeditor-default"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_DATE,
        0,
        'date',
        'Data',
        '{"type":"date","element":{"tag":"bs.varchar","class":"datepicker-input"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_DATETIME,
        0,
        'datetime',
        'Data i czas',
        '{"type":"datetime","element":{"tag":"bs.varchar","class":"datetimepicker-input"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_EMPLOYEE,
        0,
        'employees',
        'Wybór pracowników',
        '{"type":"int","element":{"tag":"bs.typeahead","url":"","model":"Osoby"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_DOCUMENT,
        0,
        'documents',
        'Wybór z dokumentów',
        '{"type":"int","baseModel":"Documents","element":{"tag":"bs.typeahead","url":"/documents/mini-add/?useProcess=true","model":"Documents"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_ROOM,
        0,
        'rooms',
        'Wybór pokoju',
        '{"type":"string","element":{"tag":"bs.varchar"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_ENTITY,
        0,
        'registry-entries',
        'Lista wartości',
        '{"type":"entry","baseModel":"RegistryEntries","element":{"tag":"bs.select"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_COLLECTION,
        0,
        'zbiory',
        'Wybór ze zbiorów',
        '{"type":"int","baseModel":"Zbiory","element":{"tag":"bs.typeahead","url":"/zbiory/addmini/?useProcess=true","model":"Zbiory"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_BUILDING,
        0,
        'buildings',
        'Wybór budynków',
        '{"type":"int","baseModel":"Budynki","widget":{"name":"typeahead-full","dialUrl":"/budynki/mini-add/?useProcess=true","options":{"type":"function","model":"Budynki","function":"getAllForTypeahead"}}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_FILE,
        0,
        'files',
        'Wybór plików',
        '{"type":"file","element":{"tag":"bs.dropzone"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_CONSENT,
        0,
        'consent',
        'Wyrażenie zgody',
        '{"type":"checkbox","baseModel":"RegistryEntriesEntitiesVarchar","element":{"tag":"bs.checkbox-line"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_SURVEY,
        0,
        'surveys',
        'Ankiety - zarządzanie',
        '{"type":"int","baseModel":"Surveys","element":{"tag":"bs.typeahead","url":"/surveys/addmini/addmini/?useProcess=true","model":"Surveys"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_DATAGRID,
        0,
        'datagrid',
        'Siatka danych',
        '{"type":"datagrid","element":{"tag":"bs.datagrid"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_CLASSIFICATION,
        0,
        'classification',
        'Wybierz klasyfikację',
        '{"type":"int","baseModel":"RiskAssessmentClassifications","element":{"tag":"bs.typeahead","url":"","model":"RiskAssessmentClassifications"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_GROUP_ASSET,
        0,
        'groupassets',
        'Groupassets',
        '{"type":"int","baseModel":"RiskAssessmentAssetGroups","element":{"tag":"bs.typeahead","url":"","model":"RiskAssessmentAssetGroups"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_ADDITIONAL_SECURITY,
        0,
        'additionalsecurity',
        'Additional security',
        '{"type":"int","baseModel":"RiskAssessmentSafeguards","element":{"tag":"bs.typeahead","url":"","model":"RiskAssessmentSafeguards"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_RELATION_MATRIX,
        0,
        'relationshipMatrix',
        'Matrix (single choice)',
        '{"type":"relationshipMatrix","element":{"tag":"bs.relationshipMatrix"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_RELATION_MATRIX_MULTIPLE,
        0,
        'relationshipMatrixMultiple',
        'Matrix (multiple choice)',
        '{"type":"relationshipMatrixMultiple","element":{"tag":"bs.relationshipMatrixMultiple"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_RELATION_MATRIX_DYNAMIC,
        0,
        'relationshipMatrixDynamic',
        'Matrix (dynamic rows)',
        '{"type":"relationshipMatrixDynamic","element":{"tag":"bs.relationshipMatrixDynamic"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_NUMBER,
        0,
        'number',
        'Number',
        '{"type":"number","element":{"tag":"bs.number", "min":"0", "max":"","step":"1"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_BUTTON,
        0,
        'button',
        'Button',
        '{"type":"button","element":{"tag":"bs.button"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_AUTO_COMPLETE,
        0,
        'autocomplete',
        'Autocomplete',
        '{"type":"autocomplete","element":{"tag":"bs.autocomplete"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_CHECKBOX_GROUP,
        0,
        'checkboxGroup',
        'Checkbox Group',
        '{"type":"checkboxGroup","element":{"tag":"bs.checkboxGroup"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_HIDDEN,
        0,
        'hidden',
        'Hidden',
        '{"type":"hidden","element":{"tag":"bs.hidden"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_HEADER,
        0,
        'header',
        'Header',
        '{"type":"header","element":{"tag":"bs.header"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_PARAGRAPH,
        0,
        'paragraph',
        'Paragraph',
        '{"type":"paragraph","element":{"tag":"bs.paragraph"}}',
        '0000-00-00 00:00:00',
    ],
    [
        EntityModel::ID_RADIO_GROUP,
        0,
        'radioGroup',
        'Radio Group',
        '{"type":"radioGroup","element":{"tag":"bs.radioGroup"}}',
        '0000-00-00 00:00:00',
    ],
];

$pdo = db_pdo();
$pdo->beginTransaction();
$pdo->exec($deleteSql);
foreach ($data as $row) {
    $pdo->prepare($addSql)
        ->execute($row);
}
$pdo->commit();

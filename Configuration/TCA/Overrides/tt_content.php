<?php
defined ('TYPO3_MODE') or die ('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup'] = unserialize($_EXTCONF);

$TCA['tt_content']['columns']['colPos']['config']['items'][] = array(
    'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tx_flux_container',
    \FluidTYPO3\Flux\Service\ContentService::COLPOS_FLUXCONTENT
);

$GLOBALS['TCA']['tt_content']['ctrl']['shadowColumnsForNewPlaceholders'] .= ',tx_flux_column,tx_flux_parent';
$GLOBALS['TCA']['tt_content']['ctrl']['useColumnsForDefaultValues'] .= ',tx_flux_column,tx_flux_parent';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', array(
        'tx_flux_column' => array (
            'exclude' => 0,
            'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tx_flux_column',
            'displayCond' => 'FIELD:tx_flux_parent:>:0',
            'config' => array (
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array(
                    array('', '')
                ),
                'default' => '',
                'itemsProcFunc' => 'FluidTYPO3\Flux\Backend\AreaListItemsProcessor->itemsProcFunc'
            )
        ),
        'tx_flux_parent' => array (
            'exclude' => 0,
            'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tx_flux_parent',
            'displayCond' => 'FIELD:tx_flux_parent:>:0',
            'config' => array (
                'type' => 'input',
                'readOnly' => TRUE,
                'foreign_table' => 'tt_content',
                'foreign_table_where' => "AND tt_content.pid = '###CURRENT_PID###'",
                'default' => 0,
                'size' => 1,
                'maxitems' => 1,
            )
        ),
        'tx_flux_children' => array (
            'exclude' => 0,
            'config' => array (
                'type' => 'inline',
                'foreign_table' => 'tt_content',
                'foreign_field' => 'tx_flux_parent',
                'foreign_sortby' => 'sorting',
                'appearance' => array(
                    'collapseAll' => TRUE,
                    'showPossibleLocalizationRecords' => TRUE,
                    'showAllLocalizationLink' => TRUE,
                    'showSynchronizationLink' => TRUE,
                    'enabledControls' => array(
                        'new' => FALSE,
                        'localize' => TRUE,
                        'sort' => FALSE,
                        'hide' => TRUE
                    )
                ),
                'behaviour' => array(
                    'localizationMode' => 'select',
                    'localizeChildrenAtParentLocalization' => TRUE,
                ),
            )
        ),
    )
);

// Selectively adding the IRRE related content displays, since updating an IRRE which contains one or more placeholders
// for other records, causes TYPO3 to error with a "versioning placeholders is not allowed" when saving a record which
// has nested content elements that have been moved in a workspace.
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('workspaces')) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', ',--div--;LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tabs.relation,tx_flux_parent,tx_flux_column,');
} else {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', ',--div--;LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tabs.relation,tx_flux_parent,tx_flux_column,tx_flux_children;LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tx_flux_children,');
}

// Initialize the TCA needed by "template as CType" integrations
\FluidTYPO3\Flux\Backend\TableConfigurationPostProcessor::spoolQueuedContentTypeTableConfigurations(
    \FluidTYPO3\Flux\Core::getQueuedContentTypeRegistrations()
);

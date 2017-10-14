/**
 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.
    config.toolbarGroups = [
        '/',
        '/',
        { name: 'clipboard', groups: [ 'clipboard', 'undo'] },
        { name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
        { name: 'tools', groups: [ 'tools' ] },
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
        { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
        { name: 'styles', groups: [ 'styles' ] },
        { name: 'colors', groups: [ 'colors' ] }
    ];

    config.removeButtons = 'Underline,Subscript,Superscript,Format,Scayt,Maximize,Styles,Blockquote';
    //config.extraPlugins=['pastebase64'];
    // Set the most common block elements.
    config.format_tags = 'p;h1;h2;h3;pre';
    config.defaultLanguage = 'zh-cn';
    // Simplify the dialog windows.
    config.removeDialogTabs = 'image:advanced;link:advanced';
};

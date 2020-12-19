<webpdf>
    <toolbar name="toolbar" class="fv__ui-toolbar-scrollable">
        <div class="fv__ui-tab-nav" name="toolbar-tabs">
            <gtab name="home-tab" group="toolbar-tab" body="fv--home-tab-paddle" text="toolbar.tabs.home.title" active></gtab>
            <gtab name="comment-tab" group="toolbar-tab" body="fv--comment-tab-paddle" text="toolbar.tabs.comment.title"></gtab>
            <gtab name="edit-tab" group="toolbar-tab" body="fv--edit-tab-paddle" text="toolbar.tabs.edit.title" @hide-on-sr @device="desktop" @collab-unsupported></gtab>
            <gtab name="form-tab" group="toolbar-tab" body="fv--form-tab-paddle" text="toolbar.tabs.form.title" @hide-on-sr @device="desktop" @collab-unsupported></gtab>
            <gtab name="protect-tab" group="toolbar-tab" body="fv--protect-tab-paddle" text="toolbar.tabs.protect.title" @hide-on-sr @collab-unsupported></gtab>
        </div>
        <div class="fv__ui-toolbar-tab-bodies" name="toolbar-tab-bodies">
            <paddle exclude-devices="tablet" name="fv--home-tab-paddle">
                <group-list name="home-toolbar-group-list">
                    <group name="home-tab-group-hand" retain-count="3">
                        <hand-button></hand-button>
                        <selection-button></selection-button>
                        <snapshot-button @hide-on-sr></snapshot-button>
                    </group>
                    <group name="home-tab-group-change-color" @hide-on-sr>
                        <change-color-dropdown></change-color-dropdown>
                    </group>
                    <group name="home-tab-group-io" retain-count="1" shrink-title="toolbar.more.document.title">
                        <open-file-dropdown></open-file-dropdown>
                        <download-file-button></download-file-button>
                        <print:print-button></print:print-button>
                    </group>
                    <group name="home-tab-group-nav" retain-count="3">
                        <goto-prev-page-button></goto-prev-page-button>
                        <goto-next-page-button></goto-next-page-button>
                        <goto-page-input></goto-page-input>
                    </group>
                    <group name="home-tab-group-zoom">
                        <zoom-out-button></zoom-out-button>
                        <zoom-in-button></zoom-in-button>
                        <editable-zoom-dropdown></editable-zoom-dropdown>
                    </group>
                    <group name="home-tab-group-page" retain-count="1">
                        <single-page-button></single-page-button>
                        <continuous-page-button></continuous-page-button>
                        <facing-page-button></facing-page-button>
                        <continuous-facing-page-button></continuous-facing-page-button>
                        <h-continuous:h-continuous-button></h-continuous:h-continuous-button>
                        <!--<h-single:h-single-button></h-single:h-single-button>-->
                        <!--<h-facing:h-facing-button></h-facing:h-facing-button>-->
                    </group>
                    <group name="home-tab-group-magnifier" @hide-on-sr>
                        <loupe-tool-button></loupe-tool-button>
                    </group>
                    <group name="home-tab-group-marquee" @hide-on-sr>
                        <marquee-tool-button></marquee-tool-button>
                    </group>
                    <group name="file-property" @require-modules="fpmodule">
                        <fpmodule:file-property-button></fpmodule:file-property-button>
                    </group>
                </group-list>
            </paddle>
            <paddle exclude-devices="tablet" name="fv--comment-tab-paddle">
                <group-list name="comment-toolbar-group-list">
                    <group name="comment-tab-group-hand" retain-count="3">
                        <hand-button></hand-button>
                        <selection-button></selection-button>
                        <zoom-dropdown></zoom-dropdown>
                    </group>
                    <group name="comment-tab-group-note" retain-count="3">
                        <create-note-button></create-note-button>                        
                    </group>
                    <group name="comment-tab-group-mark">
                        <create-text-highlight-button></create-text-highlight-button>
                        <create-strikeout-button></create-strikeout-button>
                        <create-underline-button></create-underline-button>
                        <create-squiggly-button></create-squiggly-button>
                        <create-replace-button></create-replace-button>
                        <create-caret-button></create-caret-button>
                    </group>
                    <group name="comment-tab-group-text">                      
                        <create-typewriter-button></create-typewriter-button>
                        <create-callout-button></create-callout-button>
                        <create-textbox-button></create-textbox-button>
                    </group>
                    <group name="comment-tab-group-drawing" retain-count="2">
                        <create-drawings-dropdown></create-drawings-dropdown>                       
                        <create-area-highlight-button></create-area-highlight-button>
                    </group>
                    <group name="comment-tab-group-pencil" retain-count="2">
                        <create-pencil-button></create-pencil-button>
                        <eraser-button></eraser-button>
                    </group>
                    <group name="comment-tab-group-stamp">
                        <stamp-dropdown></stamp-dropdown>
                    </group>
                    <group name="comment-tab-group-measurement">
                        <create-measure-dropdown></create-measure-dropdown>
                    </group>
                    <group name="comment-tab-group-media" @grp-more-hide-on-sr>
                        <create-attachment-button></create-attachment-button>
                        <create-image-button @hide-on-sr  @collab-unsupported></create-image-button>
                        <create-link-button @hide-on-sr  @collab-unsupported></create-link-button>
                        <multi-media:multi-media-button @hide-on-sr  @collab-unsupported></multi-media:multi-media-button>
                    </group>
                    <group name="comment-tab-group-inksign" visible='false'></group>                    
                    <group name="comment-tab-group-other" visible='false'></group>
                </group-list>
            </paddle>
            <paddle exclude-devices="tablet" name="fv--edit-tab-paddle" @device="desktop">
                <group-list name="edit-toolbar-group-list">
                    <group name="edit-tab-group-hand" retain-count="3">
                        <hand-button></hand-button>
                        <selection-button></selection-button>
                        <zoom-dropdown></zoom-dropdown>
                    </group>
                    <group name="edit-tab-group-mode" retain-count="3">
                        <edit-pageobjects:edit-all-objects-button></edit-pageobjects:edit-all-objects-button>
                        <add-image-button></add-image-button>
                        <edit-pageobjects:path-objects-dropdown></edit-pageobjects:path-objects-dropdown>
                        <!--<edit-image-button></edit-image-button>-->
                        <edit-text-object:add-text-button></edit-text-object:add-text-button>
                    </group>
                    <group name="edit-tab-group-font" retain-count="5" @require-modules="edit-text-object">
                        <edit-text-object:text-bold-style-button></edit-text-object:text-bold-style-button>
                        <edit-text-object:text-italic-style-button></edit-text-object:text-italic-style-button>
                        <edit-text-object:font-color-picker></edit-text-object:font-color-picker>
                        <edit-text-object:font-style-dropdown></edit-text-object:font-style-dropdown>
                    </group>
                    <group name="edit-tab-group-layer" visible="false"></group>
                    <group name="edit-tab-group-redact" visible="false"></group>
                </group-list>
            </paddle>
            <paddle exclude-devices="tablet" name="fv--form-tab-paddle" @device="desktop">
                <group-list name="form-toolbar-group-list">
                    <group name="form-tab-group-hand" retain-count="3">
                        <hand-button></hand-button>
                        <selection-button></selection-button>
                        <zoom-dropdown></zoom-dropdown>
                    </group>
                    <group name="form-tab-group-import-export" retain-count="2" @require-modules="import-form-module,export-form-module">
                        <import-form-module:import-form-button></import-form-module:import-form-button>
                        <export-form-module:export-form-dropdown></export-form-module:export-form-dropdown>
                    </group>
                    <group name="form-tab-group-fields" retain-count="2" @require-modules="form-designer">
                        <form-designer:create-push-button></form-designer:create-push-button>
                        <form-designer:create-check-box></form-designer:create-check-box>
                        <form-designer:create-radio-button></form-designer:create-radio-button>
                        <form-designer:create-combo-box></form-designer:create-combo-box>
                        <form-designer:create-list-box></form-designer:create-list-box>            
                        <form-designer:create-text></form-designer:create-text>
                        <form-designer:create-sign></form-designer:create-sign>
                    </group>
                </group-list>
            </paddle>
            <paddle exclude-devices="tablet" name="fv--protect-tab-paddle">
                <group-list name="protect-toolbar-group-list">
                    <group name="protect-tab-group-hand" retain-count="4">
                        <hand-button></hand-button>
                        <selection-button></selection-button>
                        <zoom-dropdown></zoom-dropdown>
                    </group>
                    <group name="protect-tab-group-sign" retain-count="4">
                        <ink-sign-dropdown></ink-sign-dropdown>
                    </group>
                    <group name="password-protect-group" retain-count="2" @require-modules="password-protect">
                        <password-protect:password-protect-button></password-protect:password-protect-button>
                        <password-protect:remove-protect-button></password-protect:remove-protect-button>
                    </group>
                    <group name="redaction" functional-module="redaction" @license-validation @require-modules="redaction">
                        <redaction:create-redactions-dropdown></redaction:create-redactions-dropdown>
                        <redaction:apply-redactions-button></redaction:apply-redactions-button>
                        <redaction:redaction-search-button></redaction:redaction-search-button>
                    </group>
                </group-list>
            </paddle>
        </div>
    </toolbar>
    <div class="fv__ui-body">
        <sidebar name="sidebar" @controller="sidebar:SidebarController">
            <bookmark-sidebar-panel></bookmark-sidebar-panel>
            <commentlist-sidebar-panel>
                <slot for="header">
                    <comment-list:toggle-commentlist-group-button></comment-list:toggle-commentlist-group-button>
                    <dropdown separate="false" class="comment-list-dropdown" icon-class="fv__icon-toolbar-more">
                        <!-- <comment-list:expand-pages-button></comment-list:expand-pages-button>
                        <comment-list:collapse-pages-button></comment-list:collapse-pages-button> -->
                        <comment-list:show-comment-button></comment-list:show-comment-button>
                        <comment-list:hide-comment-button></comment-list:hide-comment-button>
                        <comment-list:import-comment-button></comment-list:import-comment-button>
                        <dropdown-item class="fv__ui-dropdown-container-item">
                            <comment-list:export-comment-dropdown></comment-list:export-comment-dropdown>
                        </dropdown-item>
                        <dropdown-item class="fv__ui-dropdown-container-item">
                            <comment-list:sort-comments-dropdown></comment-list:sort-comments-dropdown>
                        </dropdown-item>
                    </dropdown>
                </slot>
            </commentlist-sidebar-panel>
            <thumbnail-sidebar-panel></thumbnail-sidebar-panel>
            <layer-sidebar-panel @hide-on-sr></layer-sidebar-panel>
            <search-sidebar-panel></search-sidebar-panel>
            <field-sidebar-panel @device="desktop" @collab-unsupported>
                <slot for="header">
                    <dropdown class="field-order-dropdown" icon-class="fv__icon-order" separate="false" @controller="field:FieldSidebarMenuController as fsmc">
                        <option-group>
                            <option-group-item @on.click="fsmc.setOrder('row')">sidebar.field.order.row</option-group-item>
                            <option-group-item @on.click="fsmc.setOrder('column')">sidebar.field.order.column</option-group-item>
                            <option-group-item @on.click="fsmc.setOrder('structure')">sidebar.field.order.structure</option-group-item>
                        </option-group>
                    </dropdown>
                    <dropdown class="field-orderaz-dropdown" icon-class="fv__icon-orderaz" separate="false" @controller="field:FieldSidebarMenuController as fsmc">
                        <option-group>
                            <option-group-item @on.click="fsmc.setOrder('alphabetic')">sidebar.field.order.alphabetic</option-group-item>
                            <option-group-item selected @on.click="fsmc.setOrder('tab')">sidebar.field.order.tab</option-group-item>
                        </option-group>
                    </dropdown>
                </slot>
            </field-sidebar-panel>
            <attachment-sidebar-panel></attachment-sidebar-panel>
        </sidebar>
        <distance:ruler-container name="pdf-viewer-container-with-ruler">
            <slot>
                <viewer @zoom-on-pinch @zoom-on-doubletap @zoom-on-wheel @touch-to-scroll></viewer>
            </slot>
        </distance:ruler-container>
    </div>
    <template name="template-container">
        <create-stamp-dialog></create-stamp-dialog>
        <print:print-dialog></print:print-dialog>
        <loupe-tool-dialog></loupe-tool-dialog>
        <create-ink-sign-dialog></create-ink-sign-dialog>
        <distance:measurement-popup></distance:measurement-popup>
        <fpmodule:file-property-dialog></fpmodule:file-property-dialog>
        <redaction:redaction-page-dialog @hide-on-sr></redaction:redaction-page-dialog>
        <!-- contextmenus -->
        <page-contextmenu></page-contextmenu>
        <default-annot-contextmenu></default-annot-contextmenu>
        <markup-contextmenu></markup-contextmenu>
        <markup-contextmenu name="fv--line-contextmenu"></markup-contextmenu>
        <markup-contextmenu name="fv--linearrow-contextmenu"></markup-contextmenu>
        <markup-contextmenu name="fv--linedimension-contextmenu"></markup-contextmenu>
        <markup-contextmenu name="fv--polylinedimention-contextmenu"></markup-contextmenu>
        <markup-contextmenu name="fv--polygondimension-contextmenu"></markup-contextmenu>
        <markup-contextmenu name="fv--circle-contextmenu"></markup-contextmenu>
        <markup-contextmenu name="fv--square-contextmenu"></markup-contextmenu>
        <markup-contextmenu name="fv--polyline-contextmenu"></markup-contextmenu>
        <markup-contextmenu name="fv--polygon-contextmenu"></markup-contextmenu>
        <markup-contextmenu name="fv--polygoncloud-contextmenu"></markup-contextmenu>
        <markup-contextmenu name="fv--ink-contextmenu"></markup-contextmenu>
        <markup-contextmenu name="fv--stamp-contextmenu"></markup-contextmenu>
        <markup-contextmenu name="fv--text-contextmenu"></markup-contextmenu>
        <caret-contextmenu name="fv--areahighlight-contextmenu"></caret-contextmenu>
        <caret-contextmenu name="fv--replace-contextmenu"></caret-contextmenu>
        <measurement-contextmenu></measurement-contextmenu>
        <caret-contextmenu name="fv--caret-contextmenu"></caret-contextmenu>
        <textmarkup-contextmenu name="fv--highlight-contextmenu"></textmarkup-contextmenu>
        <textmarkup-contextmenu name="fv--strikeout-contextmenu"></textmarkup-contextmenu>
        <textmarkup-contextmenu name="fv--underline-contextmenu"></textmarkup-contextmenu>
        <textmarkup-contextmenu name="fv--squiggly-contextmenu"></textmarkup-contextmenu>
        <freetext-contextmenu name="fv--typewriter-contextmenu"></freetext-contextmenu>
        <freetext-contextmenu name="fv--callout-contextmenu"></freetext-contextmenu>
        <freetext-contextmenu name="fv--textbox-contextmenu"></freetext-contextmenu>
        <action-annot-contextmenu name="fv--image-contextmenu"></action-annot-contextmenu>
        <action-annot-contextmenu name="fv--link-contextmenu"></action-annot-contextmenu>
        <comment-card-contextmenu></comment-card-contextmenu>
        <fileattachment-contextmenu></fileattachment-contextmenu>
        <media-contextmenu></media-contextmenu>
        <sound-contextmenu></sound-contextmenu>
        <redact-contextmenu></redact-contextmenu>
        <edit-graphics:image-contextmenu></edit-graphics:image-contextmenu>
        <edit-pageobjects:path-contextmenu></edit-pageobjects:path-contextmenu>
        <field-signature-contextmenu name="fv--field-signature-contextmenu"></field-signature-contextmenu>
        <text-sel:text-selection-tooltip></text-sel:text-selection-tooltip>
        <freetext:freetext-tooltip></freetext:freetext-tooltip>
        <annottext name="fv--annottext-tooltip"></annottext>
        <!-- Field panel contentmenu -->
        <contextmenu class="field-panel-contentmenu" name="fv--field-contextmenu" @controller="field:FieldSidebarMenuController">
            <contextmenu-item name="fv--contextmenu-item-properties">contextmenu.fieldPanel.properties</contextmenu-item>
            <contextmenu-item name="fv--contextmenu-item-rename">contextmenu.fieldPanel.rename</contextmenu-item>
            <dropdown name="dropdown-align" separate="false" align="out-right" valign="top" text="contextmenu.fieldPanel.addNewField.title">
                <contextmenu-item name="fv--contextmenu-item-create-push-button" icon-class="fv__push-button">contextmenu.fieldPanel.addNewField.pushButtton</contextmenu-item>
                <contextmenu-item name="fv--contextmenu-item-create-check-box" icon-class="fv__check-box">contextmenu.fieldPanel.addNewField.checkBox</contextmenu-item>
                <contextmenu-item name="fv--contextmenu-item-create-radio-button" icon-class="fv__radio-button">contextmenu.fieldPanel.addNewField.radioButtton</contextmenu-item>
                <contextmenu-item name="fv--contextmenu-item-create-text" icon-class="fv__text">contextmenu.fieldPanel.addNewField.textField</contextmenu-item>
                <contextmenu-item name="fv--contextmenu-item-create-sign" icon-class="fv__sign">contextmenu.fieldPanel.addNewField.signatureField</contextmenu-item>
            </dropdown>
            <contextmenu-item name="fv--contextmenu-item-delete">contextmenu.fieldPanel.delete</contextmenu-item>
            <contextmenu-item name="fv--contextmenu-item-multiple">contextmenu.fieldPanel.multiple</contextmenu-item>
            <!-- <contextmenu-item name="fv--contextmenu-item-numbers">sidebar.field.order.numbers</contextmenu-item> -->
            <contextmenu-item name="fv--contextmenu-item-duplicate">contextmenu.fieldPanel.duplicate</contextmenu-item>
        </contextmenu>
        <comment-list:filter-dialog name="fv--commentlist-filter-comment-dialog"></comment-list:filter-dialog>
    </template>
</webpdf>
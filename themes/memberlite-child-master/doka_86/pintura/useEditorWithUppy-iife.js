/*!
 * Pintura Image Editor 8.6.0
 * (c) 2018-2021 PQINA Inc. - All Rights Reserved
 * License: https://pqina.nl/pintura/license/
 */
/* eslint-disable */

var useEditorWithUppy = (function () {
    'use strict';

    function useEditorWithUppy (openEditor, editorOptions = {}) {
        const queue = [];

        const canEditFile = (file) => {
            if (file.isRemote) return false;
            if (!(file.data instanceof Blob)) return false;
            return /^image/.test(file.type) && !/svg/.test(file.type);
        };

        const editNextFile = () => {
            const next = queue[0];
            if (next) next();
        };

        const queueFile = (file) => {
            queue.push(function () {


                const editor = openEditor({
                    ...editorOptions,
                    src: file.data
                });

                editor.on('hide', () => {
                    // Remove this item from the queue
                    queue.shift();

                    // Edit next item in queue
                    editNextFile();
                });

                editor.on('process', ({ dest }) => {
                    // Don't add file if cancelled
                    if (!dest) return;

                    uppyTopicIcon.addFile({
                       name: file.name,
                       type: file.blob,
                       data: dest,
                       meta: {
                         __handledByEditor: true
                       },
                       source: 'Pintura',
                       isRemote: false
                    });
                });
            });

            // If this is first item, let's open the editor immmidiately
            if (queue.length === 1) editNextFile();
        };

        return (file) => {

            if (file.meta.__handledByEditor  || !canEditFile(file)) return true;
            // edit first, then add manually
            queueFile(file);

            // can't add now, we have to wait for editing to finish
            return false;
        };
    }

    return useEditorWithUppy;

}());
